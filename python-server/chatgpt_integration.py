from fastapi import FastAPI, HTTPException, Security
from fastapi.security import APIKeyHeader
from pydantic import BaseModel
import openai
import os
from typing import Optional

app = FastAPI()

# Security
api_key_header = APIKeyHeader(name="X-API-KEY")

# Load OpenAI API key from environment variables
openai.api_key = os.getenv("OPENAI_API_KEY")
API_KEYS = os.getenv("API_KEYS", "").split(",")  # Comma-separated keys for WordPress auth

class ChatRequest(BaseModel):
    prompt: str
    max_tokens: Optional[int] = 150
    temperature: Optional[float] = 0.7
    model: Optional[str] = "gpt-3.5-turbo"

def validate_api_key(api_key: str = Security(api_key_header)):
    if api_key not in API_KEYS:
        raise HTTPException(status_code=401, detail="Invalid API Key")
    return api_key

@app.post("/chat")
async def chat_completion(
    request: ChatRequest,
    api_key: str = Security(validate_api_key)
):
    try:
        response = openai.ChatCompletion.create(
            model=request.model,
            messages=[{"role": "user", "content": request.prompt}],
            max_tokens=request.max_tokens,
            temperature=request.temperature
        )
        return {
            "response": response.choices[0].message.content.strip(),
            "tokens_used": response.usage.total_tokens
        }
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))

if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="0.0.0.0", port=8000)