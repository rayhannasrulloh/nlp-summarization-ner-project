from models import ModelManager
import torch

def process(text, model_manager, params={}):
    """
    Perform Abstractive Summarization using Fine-tuned DistilBART.
    """
    summary = ""
    
    # --- 1. Abstractive Summarization ---
    try:
        inputs = model_manager.abs_tokenizer(
            text, 
            max_length=1024, 
            truncation=True, 
            return_tensors="pt"
        ).to(model_manager.device)

        # Get params with defaults
        max_len = int(params.get('abstractive_max_length', 150))
        min_len = int(params.get('abstractive_min_length', 40))
        beams = int(params.get('abstractive_num_beams', 4))
        
        print(f"Abstractive Config -> Max: {max_len}, Min: {min_len}, Beams: {beams}")

        summary_ids = model_manager.abs_model.generate(
            inputs["input_ids"], 
            max_length=max_len, 
            min_length=min_len, 
            num_beams=beams, 
            early_stopping=True
        )
        
        summary = model_manager.abs_tokenizer.decode(summary_ids[0], skip_special_tokens=True)
    except Exception as e:
        print(f"Abstractive Summarization Error: {e}")
        summary = "Error generating summary."

    # --- 2. NER (Copied Logic from Extractive to match Model usage) ---
    entities = []
    try:
        inputs = model_manager.ner_tokenizer(
            text, 
            return_tensors="pt", 
            truncation=True, 
            max_length=512,
            return_offsets_mapping=True
        ).to(model_manager.device)
        
        offset_mapping = inputs.pop("offset_mapping")[0].cpu().numpy()
        
        with torch.no_grad():
            outputs = model_manager.ner_model(**inputs)
            predictions = torch.argmax(outputs.logits, dim=2)
            # Get probabilities to filter by confidence
            probs = torch.softmax(outputs.logits, dim=2)
            confidences = torch.max(probs, dim=2).values
        
        tokens = model_manager.ner_tokenizer.convert_ids_to_tokens(inputs["input_ids"][0])
        pred_labels = [model_manager.ner_labels.get(idx.item(), "O") for idx in predictions[0]]
        pred_scores = confidences[0].cpu().numpy() # Get scores
        
        current_entity_tokens = []
        current_entity = {"word": "", "type": "", "score": 0.0, "start": 0, "end": 0}
        active_entity = False
        
        # Filter special tokens for both BERT and RoBERTa
        special_tokens = ["[CLS]", "[SEP]", "[PAD]", "<s>", "</s>", "<pad>"]
        
        # NER Threshold
        ner_threshold = float(params.get('ner_threshold', 0.50))
        
        for i, (token, label, offset) in enumerate(zip(tokens, pred_labels, offset_mapping)):
            if token in special_tokens:
                continue
            
            # Skip low confidence token if starting new entity
            # (Simplification: we check average score at the end, or per token here)
            score = float(pred_scores[i])
            
            if label.startswith("B-"):
                if active_entity:
                    # decoding previous entity
                    decoded_word = model_manager.ner_tokenizer.convert_tokens_to_string(current_entity_tokens).strip()
                    current_entity["word"] = decoded_word
                    # Only add if score is high enough (using last token score as proxy or need avg)
                    if current_entity["score"] >= ner_threshold:
                         entities.append(current_entity)
                
                active_entity = True
                entity_type = label[2:]
                current_entity_tokens = [token]
                current_entity = {
                    "word": "", 
                    "type": entity_type, 
                    "score": score, # Start with this token's score
                    "group": entity_type,
                    "entity": entity_type,
                    "start": int(offset[0]),
                    "end": int(offset[1])
                }
            elif label.startswith("I-") and active_entity:
                current_entity_tokens.append(token)
                current_entity["end"] = int(offset[1])
                # Average score? Or min? Let's take min to optionally be strict
                current_entity["score"] = min(current_entity["score"], score)
            else:
                if active_entity:
                    decoded_word = model_manager.ner_tokenizer.convert_tokens_to_string(current_entity_tokens).strip()
                    current_entity["word"] = decoded_word
                    if current_entity["score"] >= ner_threshold:
                        entities.append(current_entity)
                    active_entity = False
                    current_entity_tokens = []
        
        if active_entity:
            decoded_word = model_manager.ner_tokenizer.convert_tokens_to_string(current_entity_tokens).strip()
            current_entity["word"] = decoded_word
            if current_entity["score"] >= ner_threshold:
                entities.append(current_entity)

        entities = ModelManager.cleanup_entities(entities)

    except Exception as e:
        print(f"NER Error: {e}")
        pass

    # 3. Sentiment Analysis
    sentiment_data = {"label": "UNKNOWN", "score": 0.0}
    try:
        # Use sentiment_pipeline
        sentiment_result = model_manager.sentiment_pipeline(text[:512])[0]
        
        raw_label = sentiment_result['label']
        label_map = {
            "LABEL_0": "NEGATIVE",
            "LABEL_1": "NEUTRAL",
            "LABEL_2": "POSITIVE"
        }
        
        final_label = label_map.get(raw_label, raw_label)
        
        sentiment_data = {
            "label": final_label,
            "score": float(sentiment_result['score'])
        }
    except Exception as e:
        print(f"Sentiment Error: {e}")

    return {
        "summary": summary,
        "entities": entities,
        "sentiment": sentiment_data
    }
