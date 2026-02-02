from models import ModelManager
import torch
import nltk

# Ensure nltk resources
try:
    nltk.data.find('tokenizers/punkt')
    nltk.data.find('tokenizers/punkt_tab')
except LookupError:
    nltk.download('punkt')
    nltk.download('punkt_tab')

def process(text, model_manager):
    models = model_manager
    
    # 1. Extractive Summarization
    try:
        # Split text into sentences
        sentences = nltk.sent_tokenize(text)
        
        summary_text = ""
        if sentences:
            inputs = models.ext_tokenizer(
                sentences, 
                padding=True, 
                truncation=True, 
                max_length=512, 
                return_tensors="pt"
            ).to(models.device)

            with torch.no_grad():
                outputs = models.ext_model(**inputs)
                logits = outputs.logits
                # Assuming high value for class 1 = "summary worthy"
                # If num_labels=2, probs[:, 1]
                probs = torch.softmax(logits, dim=1)
                
                # Check shape to decide index (assuming binary: 0=reject, 1=keep)
                if probs.shape[1] > 1:
                    scores = probs[:, 1]
                else:
                    scores = probs[:, 0]

            # Select Top K sentences (e.g., top 30% or top 3 sentences)
            num_sentences = len(sentences)
            k = max(1, int(num_sentences * 0.3)) # Keep top 30%
            if k < 2 and num_sentences >= 2: k = 2
            
            # Get indices of top scores
            top_indices = torch.topk(scores, k).indices.tolist()
            top_indices.sort() # Sort to keep original order
            
            summary_text = " ".join([sentences[i] for i in top_indices])
        else:
            summary_text = text # Fallback

    except Exception as e:
        print(f"Extractive Summarization Error: {e}")
        summary_text = text[:500] + "..." # Fallback

    # 2. NER
    entities_list = []
    try:
        # We need to process text in chunks if too long for BERT
        inputs = models.ner_tokenizer(
            text, 
            return_tensors="pt", 
            truncation=True, 
            max_length=512,
            return_offsets_mapping=True
        ).to(models.device)
        
        offset_mapping = inputs.pop("offset_mapping")[0].cpu().numpy()
        
        with torch.no_grad():
            outputs = models.ner_model(**inputs)
            predictions = torch.argmax(outputs.logits, dim=2)
        
        tokens = models.ner_tokenizer.convert_ids_to_tokens(inputs["input_ids"][0])
        pred_labels = [models.ner_labels.get(idx.item(), "O") for idx in predictions[0]]
        
        # Raw Entity Extraction Logic
        current_entity_tokens = []
        current_entity = {"word": "", "type": "", "score": 0.0, "start": 0, "end": 0}
        active_entity = False
        
        # Filter special tokens for both BERT and RoBERTa
        special_tokens = ["[CLS]", "[SEP]", "[PAD]", "<s>", "</s>", "<pad>"]
        
        for i, (token, label, offset) in enumerate(zip(tokens, pred_labels, offset_mapping)):
            if token in special_tokens:
                continue
                
            if label.startswith("B-"):
                if active_entity:
                    # decoding previous entity
                    decoded_word = models.ner_tokenizer.convert_tokens_to_string(current_entity_tokens).strip()
                    current_entity["word"] = decoded_word
                    entities_list.append(current_entity)
                
                active_entity = True
                entity_type = label[2:]
                current_entity_tokens = [token]
                current_entity = {
                    "word": "", # Placeholder, will decode later
                    "type": entity_type, 
                    "score": 0.99, 
                    "group": entity_type,
                    "entity": entity_type,
                    "start": int(offset[0]),
                    "end": int(offset[1])
                }
            elif label.startswith("I-") and active_entity:
                current_entity_tokens.append(token)
                current_entity["end"] = int(offset[1])
            else:
                if active_entity:
                    decoded_word = models.ner_tokenizer.convert_tokens_to_string(current_entity_tokens).strip()
                    current_entity["word"] = decoded_word
                    entities_list.append(current_entity)
                    active_entity = False
                    current_entity_tokens = []
        
        if active_entity:
            decoded_word = models.ner_tokenizer.convert_tokens_to_string(current_entity_tokens).strip()
            current_entity["word"] = decoded_word
            entities_list.append(current_entity)

        # Cleanup
        entities_list = ModelManager.cleanup_entities(entities_list)

    except Exception as e:
        print(f"NER Error: {e}")
        pass

    # 3. Sentiment Analysis
    sentiment_data = {"label": "UNKNOWN", "score": 0.0}
    try:
        # Use sentiment_pipeline
        sentiment_result = models.sentiment_pipeline(text[:512])[0]
        
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
        "summary": summary_text,
        "entities": entities_list,
        "sentiment": sentiment_data
    }
