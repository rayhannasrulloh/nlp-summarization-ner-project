from fastapi import FastAPI, HTTPException
from pydantic import BaseModel
from typing import List, Optional
from transformers import pipeline
import uvicorn
import torch

app = FastAPI(title="News Summarization & NER API")

# Request Model
class NewsRequest(BaseModel):
    text: str

# Response Models
class Entity(BaseModel):
    word: str
    entity: str
    start: int
    end: int
    score: float

class AnalysisResponse(BaseModel):
    summary: str
    entities: List[Entity]

# Global variables for models
summarizer_pipeline = None
ner_pipeline = None

def load_pipelines():
    global summarizer_pipeline, ner_pipeline
    print("Loading Summarization Model...")
    # Use a smaller/faster model for CPU/GPU balance
    device = 0 if torch.cuda.is_available() else -1
    print(f"Using device: {'GPU' if device == 0 else 'CPU'}")
    
    summarizer_pipeline = pipeline(
        "summarization", 
        model="sshleifer/distilbart-cnn-12-6", 
        device=device
    )
    
    print("Loading NER Model...")
    ner_pipeline = pipeline(
        "ner", 
        model="dslim/bert-base-NER", 
        aggregation_strategy="simple", # Merges subwords (e.g. New + York -> New York)
        device=device
    )
    print("Models loaded successfully!")

@app.on_event("startup")
async def startup_event():
    load_pipelines()

@app.post("/analyze", response_model=AnalysisResponse)
async def analyze_news(request: NewsRequest):
    if not request.text:
        raise HTTPException(status_code=400, detail="Text is required")
    
    text = request.text
    
    # 1. Summarize
    # Truncate text if too long for the model usually handled by truncation=True
    try:
        # Bart model limit is usually 1024 tokens. We'll let the pipeline handle truncation.
        summary_result = summarizer_pipeline(text, max_length=150, min_length=30, do_sample=False, truncation=True)
        summary_text = summary_result[0]['summary_text']
    except Exception as e:
        print(f"Summarization Error: {e}")
        summary_text = "Error generating summary."

    # 2. NER
    entities_list = []
    try:
        ner_results = ner_pipeline(text)
        # Convert numpy types to native types if necessary
        for entity in ner_results:
            entities_list.append(Entity(
                word=str(entity['word']),
                entity=str(entity['entity_group']),
                start=int(entity['start']),
                end=int(entity['end']),
                score=float(entity['score'])
            ))
    except Exception as e:
        print(f"NER Error: {e}")
        
    return AnalysisResponse(
        summary=summary_text,
        entities=entities_list
    )

if __name__ == "__main__":
    uvicorn.run("main:app", host="127.0.0.1", port=8001, reload=False)
