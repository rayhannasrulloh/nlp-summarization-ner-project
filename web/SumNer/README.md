# News Summarization & NER Bot

A Laravel 12 application integrated with a Python FastAPI service to perform Abstractive Summarization and Named Entity Recognition (NER) on news articles (Text or PDF).

## Features
- **Abstractive Summarization**: Uses `sshleifer/distilbart-cnn-12-6`.
- **Named Entity Recognition**: Uses `dslim/bert-base-NER`.
- **Dual Input**: Supports both raw text paste and PDF document upload.
- **Microservice Architecture**: Laravel (Frontend/Controller) + FastAPI (AI Inference).

## Prerequisites

- **PHP 8.2+** & **Composer**
- **Python 3.9+**
- **CUDA 12.1+** (Protected Nvidia GPU recommended for performance)

## Installation & Setup

### 1. Clone & Laravel Setup

```bash
git clone <repository_url>
cd SumNer

# Install PHP dependencies
composer install

# Copy environment file
cp .env.example .env
# Configure DB in .env if needed (sqlite by default usually fine)

# Generate Key
php artisan key:generate
```

### 2. Python ML Service Setup

The AI logic lives in `ml_service/`. You must set up a virtual environment and install dependencies.

```powershell
cd ml_service

# Create Virtual Environment
python -m venv venv

# Activate (Windows)
.\venv\Scripts\activate

# 1. Install PyTorch with CUDA 12.1 support
pip install torch torchvision torchaudio --index-url https://download.pytorch.org/whl/cu121

# 2. Install remaining dependencies
pip install -r requirements.txt
```

> **Note**: This project specifically uses `transformers==4.40.0` for stability.

## Running the Project

You need two terminals.

**Terminal 1: ML Service**
```powershell
cd ml_service
.\venv\Scripts\activate
uvicorn main:app --host 127.0.0.1 --port 8001
```
*Wait until you see "Models loaded successfully!". The first run may take a few minutes to download models (~1.5GB).*

**Terminal 2: Laravel App**
```powershell
# (In project root)
php artisan serve
```

## Usage
1. Open [http://127.0.0.1:8000/news-bot](http://127.0.0.1:8000/news-bot).
2. Paste text or upload a News PDF.
3. Click **Analyze**.

## Troubleshooting
- **Port 8001 In Use**: If the ML service fails to bind, check if port 8001 is free.
- **Model Download Failures**: Ensure you have a stable internet connection for the initial run.
- **CUDA Not Found**: If PyTorch cannot find your GPU, the service will fall back to CPU (slower but functional).
