from models import ModelManager

def process(text: str):
    models = ModelManager.get_instance()
    
    # 1. Extractive Summarization
    try:
        # ratio=0.2 means keep top 20% of sentences
        # min_length sets a floor for sentence length to consider
        summary_text = models.extractive_model(text, ratio=0.2, min_length=30)
        # If text is short, it might return empty string options, handle fallback
        if not summary_text:
            summary_text = text[:500] + "..." # Fallback for very short text
    except Exception as e:
        print(f"Extractive Summarization Error: {e}")
        summary_text = "Error generating summary."

    # 2. NER
    entities_list = []
    try:
        ner_results = models.ner(text)
        # Use centralized cleanup logic
        entities_list = ModelManager.cleanup_entities(ner_results)

    except Exception as e:
        print(f"NER Error: {e}")

    # 3. Sentiment Analysis
    sentiment_data = {"label": "UNKNOWN", "score": 0.0}
    try:
        sentiment_result = models.sentiment(text, truncation=True, max_length=512)
        
        raw_label = sentiment_result[0]['label']
        label_map = {
            "LABEL_0": "NEGATIVE",
            "LABEL_1": "NEUTRAL",
            "LABEL_2": "POSITIVE"
        }
        
        final_label = label_map.get(raw_label, raw_label)
        
        sentiment_data = {
            "label": final_label,
            "score": float(sentiment_result[0]['score'])
        }
    except Exception as e:
        print(f"Sentiment Error: {e}")

    return {
        "summary": summary_text,
        "entities": entities_list,
        "sentiment": sentiment_data
    }
