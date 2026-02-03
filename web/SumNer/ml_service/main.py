from fastapi import FastAPI, HTTPException
from pydantic import BaseModel
from typing import List, Optional, Dict
import uvicorn
from models import ModelManager
import abstractive, extractive
from newspaper import Article, Config
import tracemalloc

# Prevent memory leak warnings
tracemalloc.start()

app = FastAPI(title="News Summarization & NER API")

# Request Model
class NewsRequest(BaseModel):
    text: Optional[str] = None
    url: Optional[str] = None
    summary_type: str = "abstractive"
    parameters: dict = {}

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
    category: Optional[str] = "Uncategorized"
    title: Optional[str] = None
    image_url: Optional[str] = None

@app.on_event("startup")
async def startup_event():
    ModelManager()

@app.post("/analyze", response_model=AnalysisResponse)
async def analyze_news(request: NewsRequest):
    print(f"Received Request. Params: {request.parameters}")
    
    extracted_title = None
    extracted_image = None
    final_text = request.text
    
    try:
        # 1. URL Extraction
        if request.url:
            print(f"Extracting Request URL: {request.url}")
            try:
                user_agent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
                config = Config()
                config.browser_user_agent = user_agent
                
                article = Article(request.url, config=config)
                article.download()
                article.parse()
                
                final_text = article.text
                extracted_title = article.title
                extracted_image = article.top_image
                print("URL Extraction Successful")
            except Exception as e:
                print(f"URL Extraction Failed: {e}")
                # Fallback to text if provided, else fail
                if not final_text:
                    raise HTTPException(status_code=400, detail=f"Failed to extract content from URL: {str(e)}")

        if not final_text:
            raise HTTPException(status_code=400, detail="Text or valid URL is required")

        # Get singleton
        model_manager = ModelManager()
        
        # 2. Summarization & NER
        if request.summary_type == "extractive":
            result = extractive.process(final_text, model_manager, request.parameters)
        else:
            result = abstractive.process(final_text, model_manager, request.parameters)
            
        # Refine NER: If entities are empty (sometimes abstractive models lose them), run pure NER on original text
        if not result['entities']:
            print("Entities empty after summarization. Running fallback NER on original text...")
            ner_results = model_manager.ner_pipeline(final_text[:2000]) # Run on first 2k chars for speed
            entities = []
            for ent in ner_results:
                if ent['score'] > float(request.parameters.get('ner_threshold', 0.50)):
                    entities.append(Entity(
                        word=ent['word'],
                        entity=ent['entity_group'],
                        start=ent['start'],
                        end=ent['end'],
                        score=float(ent['score'])
                    ))
            result['entities'] = entities
            
        # 3. Auto-Categorization (Zero-Shot)
        category = "Uncategorized"
        try:
            # Classify based on the summary (concise) or full text (slower)
            # Using summary is usually efficient enough
            text_to_classify = result['summary'] if result['summary'] else final_text[:1024]
            
            candidate_labels = ["Politics", "Technology", "Business", "Sports", "Health", "Entertainment", "Science"]
            
            classification = model_manager.classifier(
                text_to_classify, 
                candidate_labels, 
                multi_label=False
            )
            
            # Get top label
            category = classification['labels'][0]
            print(f"Categorized as: {category}")
            
        except Exception as e:
            print(f"Categorization Error: {e}")
            
        return AnalysisResponse(
            summary=result['summary'],
            entities=result['entities'],
            sentiment=result['sentiment'],
            category=category,
            title=extracted_title,
            image_url=extracted_image
        )
        
    except HTTPException as he:
        raise he
    except Exception as e:
        print(f"Analysis Error: {e}")
        import traceback
        traceback.print_exc()
        raise HTTPException(status_code=500, detail=str(e))

if __name__ == "__main__":
    uvicorn.run("main:app", host="127.0.0.1", port=8001, reload=False)
