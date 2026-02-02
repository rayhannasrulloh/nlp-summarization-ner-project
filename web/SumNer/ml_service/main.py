from fastapi import FastAPI, HTTPException
from pydantic import BaseModel
from typing import List, Optional, Dict
import uvicorn
from models import ModelManager
import abstractive, extractive

app = FastAPI(title="News Summarization & NER API")

# Request Model
class NewsRequest(BaseModel):
    text: str
    summary_type: str = "abstractive" # "abstractive" or "extractive"

# Response Models
class Entity(BaseModel):
    word: str
    entity: str
    start: int
    end: int
    score: float

class Sentiment(BaseModel):
    label: str
    score: float

class AnalysisResponse(BaseModel):
    summary: str
    entities: List[Entity]
    sentiment: Sentiment

@app.on_event("startup")
async def startup_event():
    # Initialize singleton models
    # This triggers __init__ exactly once due to __new__ logic
    ModelManager()

@app.post("/analyze", response_model=AnalysisResponse)
async def analyze_news(request: NewsRequest):
    if not request.text:
        raise HTTPException(status_code=400, detail="Text is required")
    
    try:
        # Get the singleton instance
        model_manager = ModelManager()
        
        if request.summary_type == "extractive":
            result = extractive.process(request.text, model_manager)
        else:
            result = abstractive.process(request.text, model_manager)
            
        return AnalysisResponse(
            summary=result['summary'],
            entities=result['entities'],
            sentiment=result['sentiment']
        )
        
    except Exception as e:
        print(f"Analysis Error: {e}")
        import traceback
        traceback.print_exc()
        raise HTTPException(status_code=500, detail=str(e))

if __name__ == "__main__":
    uvicorn.run("main:app", host="127.0.0.1", port=8001, reload=False)
