from models import ModelManager
import torch

def process(text, model_manager):
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

        summary_ids = model_manager.abs_model.generate(
            inputs["input_ids"], 
            max_length=150, 
            min_length=40, 
            num_beams=4, 
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
        
        tokens = model_manager.ner_tokenizer.convert_ids_to_tokens(inputs["input_ids"][0])
        pred_labels = [model_manager.ner_labels.get(idx.item(), "O") for idx in predictions[0]]
        
        current_entity = {"word": "", "type": "", "score": 1.0, "start": 0, "end": 0}
        active_entity = False
        
        for i, (token, label, offset) in enumerate(zip(tokens, pred_labels, offset_mapping)):
            if token in ["[CLS]", "[SEP]", "[PAD]"]:
                continue
                
            if label.startswith("B-"):
                if active_entity:
                    entities.append(current_entity)
                
                active_entity = True
                entity_type = label[2:]
                current_entity = {
                    "word": token, 
                    "type": entity_type, 
                    "score": 0.99,
                    "group": entity_type,
                    "entity": entity_type,
                    "start": int(offset[0]),
                    "end": int(offset[1])
                }
            elif label.startswith("I-") and active_entity:
                current_entity["word"] += " " + token
                current_entity["end"] = int(offset[1])
            else:
                if active_entity:
                    entities.append(current_entity)
                    active_entity = False
        
        if active_entity:
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
