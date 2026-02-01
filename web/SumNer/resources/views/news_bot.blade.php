<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>News Bot: Summarization & NER</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        /* --- SUMMER AI DESIGN SYSTEM --- */
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(to bottom, #e6eff8 0%, #eef2f9 100%);
            color: #333;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            padding: 20px;
        }

        .app-container {
            width: 100%;
            max-width: 1100px;
            display: flex;
            flex-direction: column;
        }

        /* Header */
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 0;
            margin-bottom: 40px;
        }

        .logo {
            display: flex;
            align-items: center;
            font-weight: 700;
            font-size: 1.2rem;
            letter-spacing: 1px;
            text-decoration: none;
            color: #333;
        }
        .logo svg { margin-right: 10px; }

        nav {
            background-color: #fff;
            padding: 5px;
            border-radius: 30px;
            display: flex;
            align-items: center;
            /* box-shadow: 0 2px 5px rgba(0,0,0,0.05); */
        }

        .status-badge {
            padding: 10px 16px;
            font-size: 0.85rem;
            color: #888;
            font-style: italic;
            border-right: 1px solid #eee;
            margin-right: 5px;
        }

        .nav-btn {
            text-decoration: none;
            padding: 10px 24px;
            border-radius: 25px;
            cursor: pointer;
            font-family: 'Inter', sans-serif;
            font-weight: 500;
            color: #666;
            transition: all 0.3s ease;
            font-size: 0.95rem;
            display: inline-block;
        }

        .nav-btn.active {
            background-color: #4a6fa5; /* Menggunakan warna brand biru */
            color: #fff;
            font-weight: 600;
        }
        
        .nav-btn:hover:not(.active) {
            background-color: #f7f9fc;
            color: #333;
        }

        /* Main Layout */
        main {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .hero {
            text-align: center;
            margin-bottom: 30px;
            max-width: 800px;
        }

        .hero h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 15px;
            color: #222;
        }

        /* Card Container Styles (Matches previous UI) */
        .summarize-card {
            background-color: #fff;
            padding: 30px;
            border-radius: 24px;
            width: 100%;
            max-width: 900px;
            /* box-shadow: 0 4px 12px rgba(0,0,0,0.03); */
            display: flex;
            flex-direction: column;
            min-height: 400px; /* Allow height to grow with content */
        }

        /* Footer */
        footer {
            text-align: center;
            margin-top: 60px;
            padding: 20px 0;
            color: #666;
        }
        footer p.brand { font-weight: 600; margin-bottom: 8px; color: #333; }
        .copyright { font-size: 0.9rem; color: #888; }
        
        /* Utility for Blade Content adaptability */
        .blade-content-wrapper {
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="app-container">
        <header>
            <a href="/" class="logo">
                <svg width="40" height="24" viewBox="0 0 40 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="0" y="2" width="22" height="3" rx="1.5" fill="#333"/>
                    <rect x="0" y="9" width="22" height="3" rx="1.5" fill="#333"/>
                    <rect x="0" y="16" width="16" height="3" rx="1.5" fill="#333"/>
                    <rect x="26" y="2" width="14" height="3" rx="1.5" fill="#333"/>
                    <rect x="26" y="9" width="8" height="3" rx="1.5" fill="#333"/>
                    <rect x="20" y="16" width="14" height="3" rx="1.5" fill="#333"/>
                </svg>
                <span>SUMMER</span>
            </a>
            <nav>
                <span class="status-badge">Guest Mode</span>
                <a href="{{ route('login') }}" class="nav-btn active">
                    <i class="fa-solid fa-right-to-bracket" style="margin-right: 8px;"></i> Login
                </a>
            </nav>
        </header>

        <main>
            <div class="hero">
                <h1>News Bot: Summarization & NER</h1>
            </div>

            <div class="summarize-card">
                <div class="blade-content-wrapper">
                    @include('news_bot_form_partial')
                </div>
            </div>
        </main>

        <footer>
            <p class="brand">Summer AI</p>
            <p class="copyright">@2026 Summer AI. Built with ❤️.</p>
        </footer>
    </div>
</body>
</html>