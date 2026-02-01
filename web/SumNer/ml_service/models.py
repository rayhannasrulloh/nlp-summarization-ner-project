import torch
from transformers import pipeline
from summarizer import Summarizer

class ModelManager:
    _instance = None

    def __init__(self):
        self.device = 0 if torch.cuda.is_available() else -1
        print(f"ModelManager: Using device {'GPU' if self.device == 0 else 'CPU'}")
        
        self.summarizer = None
        self.ner = None
        self.sentiment = None
        self.extractive_model = None

    @classmethod
    def get_instance(cls):
        if cls._instance is None:
            cls._instance = cls()
        return cls._instance

    def load_models(self):
        print("Loading Abstractive Summarizer (DistilBART)...")
        self.summarizer = pipeline(
            "summarization", 
            model="sshleifer/distilbart-cnn-12-6", 
            device=self.device
        )

        print("Loading NER Model (BERT)...")
        self.ner = pipeline(
            "ner", 
            model="dslim/bert-base-NER", 
            aggregation_strategy="simple",
            device=self.device
        )

        print("Loading Sentiment Model (Roberta)...")
        self.sentiment = pipeline(
            "sentiment-analysis",
            model="cardiffnlp/twitter-roberta-base-sentiment",
            device=self.device
        )

        print("Loading Extractive Summarizer (BERT)...")
        # bert-extractive-summarizer handles device internally or via pytorch, 
        # usually doesn't accept device arg in constructor the same way as pipeline
        # It uses the default torch device.
        self.extractive_model = Summarizer() 

        print("All models loaded successfully!")

    @staticmethod
    def cleanup_entities(ner_results):
        """
        Cleans up NER results:
        1. Sorts by start position.
        2. Merges adjacent entities of the same group.
        3. Removes '##' artifacts.
        4. Deduplicates based on word text.
        """
        if not ner_results:
            return []
            
        # 1. Sort by start position
        sorted_entities = sorted(ner_results, key=lambda x: x['start'])
        
        merged_entities = []
        if not sorted_entities:
            return []
            
        # Start with the first entity
        current_entity = sorted_entities[0].copy()
        current_entity['word'] = str(current_entity['word']).replace('##', '') # Initial cleanup
        
        for i in range(1, len(sorted_entities)):
            next_entity = sorted_entities[i]
            next_word_clean = str(next_entity['word']).replace('##', '')
            
            # 2. Merge logic:
            # If same entity group AND (adjacent OR separated by space/token limit which is roughly < 2 chars)
            # We assume adjacent if end == start (subword) or end + 1 == start (space)
            gap = next_entity['start'] - current_entity['end']
            
            if (next_entity['entity_group'] == current_entity['entity_group'] and gap <= 1):
                # Merge
                current_entity['word'] += next_word_clean
                current_entity['end'] = next_entity['end']
                # Keep max score or average? Let's take average for now
                current_entity['score'] = (current_entity['score'] + next_entity['score']) / 2
            else:
                # Push current and start new
                merged_entities.append(current_entity)
                current_entity = next_entity.copy()
                current_entity['word'] = next_word_clean
        
        # Append the last one
        merged_entities.append(current_entity)
        
        # 4. Deduplicate logic (keep highest score)
        unique_entities = {}
        for entity in merged_entities:
            word = entity['word'].strip()
            if not word: continue
            
            group = entity['entity_group']
            key = (word, group)
            
            if key not in unique_entities or entity['score'] > unique_entities[key]['score']:
                unique_entities[key] = {
                    "word": word,
                    "entity": group,
                    "start": int(entity['start']),
                    "end": int(entity['end']),
                    "score": float(entity['score'])
                }
                
        return list(unique_entities.values())
