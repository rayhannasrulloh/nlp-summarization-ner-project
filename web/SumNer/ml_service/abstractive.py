from models import ModelManager

def process(text: str):
    models = ModelManager.get_instance()
    
    # 1. Abstractive Summarization
    try:
        # max_length and min_length can be adjusted
        summary_result = models.summarizer(text, max_length=150, min_length=30, do_sample=False, truncation=True)
        summary_text = summary_result[0]['summary_text']
    except Exception as e:
        print(f"Abstractive Summarization Error: {e}")
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
        # Truncate for sentiment model
        sentiment_result = models.sentiment(text, truncation=True, max_length=512)
        
        # Map LABEL_0/1/2 to text if necessary, though some pipelines return labels directly
        # cardiffnlp/twitter-roberta-base-sentiment returns LABEL_0 (Neg), LABEL_1 (Neu), LABEL_2 (Pos) by default
        # But pipeline might map it if config is present. Let's handle both.
        raw_label = sentiment_result[0]['label']
        label_map = {
            "LABEL_0": "NEGATIVE",
            "LABEL_1": "NEUTRAL",
            "LABEL_2": "POSITIVE"
        }
        
        final_label = label_map.get(raw_label, raw_label) # Fallback to raw if not in map
        
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
