import torch
from transformers import pipeline, AutoTokenizer, AutoModelForSeq2SeqLM, AutoModelForSequenceClassification, AutoModelForTokenClassification
import os
import json

class ModelManager:
    _instance = None
    
    def __new__(cls):
        if cls._instance is None:
            cls._instance = super(ModelManager, cls).__new__(cls)
            cls._instance._initialized = False
        return cls._instance
    
    def __init__(self):
        if self._initialized:
            return
            
        self.device = torch.device("cuda" if torch.cuda.is_available() else "cpu")
        print(f"Loading models on: {self.device}")

        # Base Paths
        base_dir = os.path.dirname(os.path.abspath(__file__))
        model_nlp_dir = os.path.join(base_dir, "Model_NLP")
        
        # 1. Abstractive Summarization Model (Fine-tuned DistilBART)
        abs_path = os.path.join(model_nlp_dir, "Distilbert_abstractive", "Distilbert_abstractive", "distilbart_5k_e3")
        print(f"Loading Abstractive Model from: {abs_path}")
        self.abs_tokenizer = AutoTokenizer.from_pretrained(abs_path, use_fast=False)
        self.abs_model = AutoModelForSeq2SeqLM.from_pretrained(abs_path).to(self.device)
        self.abs_model.eval()

        # 2. Extractive Summarization Model (Fine-tuned DistilBERT Classifier)
        ext_path = os.path.join(model_nlp_dir, "Distilbert_extractive", "Distilbert_extractive", "saved_extractive_model")
        print(f"Loading Extractive Model from: {ext_path}")
        self.ext_tokenizer = AutoTokenizer.from_pretrained(ext_path)
        self.ext_model = AutoModelForSequenceClassification.from_pretrained(ext_path).to(self.device)
        self.ext_model.eval()

        # 3. NER Model (Fine-tuned BERT)
        ner_path = os.path.join(model_nlp_dir, "Distilbert_abstractive", "Distilbert_abstractive", "NER_Model")
        print(f"Loading NER Model from: {ner_path}")
        # Model is RoBERTa-based (checked config.json), so we MUST use RoBERTa tokenizer
        self.ner_tokenizer = AutoTokenizer.from_pretrained("roberta-base", add_prefix_space=True)
        self.ner_model = AutoModelForTokenClassification.from_pretrained(ner_path).to(self.device)
        self.ner_model.eval()
        
        # Load NER Labels
        ner_config = json.load(open(os.path.join(ner_path, "config.json")))
        self.ner_labels = ner_config.get("id2label", {})
        # Ensure keys are integers
        self.ner_labels = {int(k): v for k, v in self.ner_labels.items()}

        # 4. Sentiment Model
        # ... (sentiment loading remains same) ...
        print("Loading Sentiment Model...")
        self.sentiment_pipeline = pipeline(
            "sentiment-analysis",
            model="cardiffnlp/twitter-roberta-base-sentiment",
            device=0 if torch.cuda.is_available() else -1
        )

        self._initialized = True

    @staticmethod
    def cleanup_entities(entities: list) -> list:
        # Sort by start position
        entities = sorted(entities, key=lambda x: x['start'])
        
        unique_entities = []
        seen_words = set()
        
        for ent in entities:
            word = ent['word']
            # Remove artifacts
            word = word.replace('##', '') # BERT
            word = word.replace('Ġ', '')  # RoBERTa
            word = word.strip()
            
            if not word:
                continue
                
            # Create a unique key based on word (case-insensitive)
            key = word.lower()
            
            if key not in seen_words:
                ent['word'] = word # Update with cleaned word
                unique_entities.append(ent)
                seen_words.add(key)
        
        return unique_entities

