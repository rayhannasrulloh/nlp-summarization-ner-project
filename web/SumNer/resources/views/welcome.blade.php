<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SUMMER AI - Welcome</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* Reset and Global Styles */
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
            /* box-shadow: 0 2px 5px rgba(0,0,0,0.05); */
        }

        .nav-item {
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

        .nav-item:hover {
            color: #333;
            background-color: #f7f9fc;
        }

        .nav-item.active {
            background-color: #f0f2f5;
            color: #333;
            font-weight: 600;
        }

        .nav-item.cta {
            color: #4a6fa5;
            font-weight: 600;
        }

        /* Main Content */
        main {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* Hero Section */
        .hero {
            text-align: center;
            margin-bottom: 50px;
            max-width: 800px;
        }

        .hero h1 {
            font-size: 4rem;
            font-weight: 700;
            margin-bottom: 20px;
            line-height: 1.1;
            color: #222;
        }

        .hero p {
            font-size: 1.15rem;
            color: #666;
            line-height: 1.5;
            max-width: 700px;
            margin: 0 auto;
        }

        /* Features Grid */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 25px;
            width: 100%;
            max-width: 950px;
            margin-bottom: 50px;
        }

        .feature-card {
            background-color: #fff;
            padding: 35px;
            border-radius: 24px;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            /* box-shadow: 0 4px 12px rgba(0,0,0,0.03); */
            transition: transform 0.2s ease;
        }

        .feature-card:hover {
            transform: translateY(-5px);
        }

        .feature-card i {
            font-size: 1.4rem;
            margin-bottom: 20px;
            color: #444;
            background-color: #f0f2f5;
            padding: 12px;
            border-radius: 14px;
        }

        .feature-card h3 {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 12px;
            color: #222;
        }

        .feature-card p {
            font-size: 1rem;
            color: #666;
            line-height: 1.5;
        }

        /* Process Section */
        .process-section {
            background-color: #fff;
            padding: 50px;
            border-radius: 24px;
            text-align: center;
            width: 100%;
            max-width: 950px;
            /* box-shadow: 0 4px 12px rgba(0,0,0,0.03); */
        }

        .process-section h2 {
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 15px;
            color: #222;
        }

        .process-section p {
            color: #666;
            margin-bottom: 40px;
            font-size: 1.05rem;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
        }

        .process-steps {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 15px;
        }

        .step {
            display: flex;
            align-items: center;
            background-color: #f0f2f5;
            padding: 12px 24px;
            border-radius: 30px;
        }

        .step-number {
            background-color: #fff;
            color: #333;
            font-weight: 600;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-right: 12px;
            /* box-shadow: 0 2px 4px rgba(0,0,0,0.05); */
        }

        .step-text {
            font-weight: 500;
            font-size: 1rem;
        }

        .separator {
            color: #ccc;
            font-size: 0.8rem;
        }

        /* Footer */
        footer {
            text-align: center;
            margin-top: 60px;
            padding: 20px 0;
            color: #666;
        }

        footer p.brand {
            font-weight: 600;
            margin-bottom: 8px;
            color: #333;
            font-size: 1.1rem;
        }

        .copyright {
            font-size: 0.9rem;
            color: #888;
        }

        /* Responsive */
        @media (max-width: 768px) {
            header { flex-direction: column; gap: 20px; }
            .hero h1 { font-size: 3rem; }
            .features-grid { grid-template-columns: 1fr; }
            .process-steps { flex-direction: column; gap: 10px; }
            .separator { transform: rotate(90deg); }
        }
    </style>
</head>
<body>
    <div class="app-container">
        <header>
            <a href="#" class="logo">
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
                <a href="#" class="nav-item active">Home</a>
                <a href="{{ route('login') }}" class="nav-item cta">Try Now</a>
            </nav>
        </header>

        <main>
            <section id="landing-page">
                <div class="hero">
                    <h1>Summer ‘eAy</h1>
                    <p>Our platform leverages the latest in Generative AI to provide more than just text shortening.</p>
                </div>

                <div class="features-grid">
                    <div class="feature-card">
                        <i class="fa-solid fa-bolt"></i>
                        <h3>Instant Summaries</h3>
                        <p>Get to the point fast. Our AI condenses complex articles into concise executive summaries.</p>
                    </div>
                    <div class="feature-card">
                        <i class="fa-solid fa-tags"></i>
                        <h3>Entity Recognition</h3>
                        <p>Automatically extract and classify key entities like Names, Organizations, and Locations.</p>
                    </div>
                    <div class="feature-card">
                        <i class="fa-solid fa-file-lines"></i>
                        <h3>Document Analysis</h3>
                        <p>Upload document reports or text files. We handle text extraction and formatting for you.</p>
                    </div>
                    <div class="feature-card">
                        <i class="fa-solid fa-chart-simple"></i>
                        <h3>Visual Data</h3>
                        <p>Identify key themes instantly with generated word clouds and frequency analysis visualizations.</p>
                    </div>
                </div>

                <div class="process-section">
                    <h2>Streamline your information intake</h2>
                    <p>Whether you are a researcher, student, or professional, Summer AI helps you digest information 10x faster.</p>
                    <div class="process-steps">
                        <div class="step">
                            <span class="step-number">1</span>
                            <span class="step-text">Input Source</span>
                        </div>
                        <i class="fa-solid fa-chevron-right separator"></i>
                        <div class="step">
                            <span class="step-number">2</span>
                            <span class="step-text">AI Processing</span>
                        </div>
                        <i class="fa-solid fa-chevron-right separator"></i>
                        <div class="step">
                            <span class="step-number">3</span>
                            <span class="step-text">Export & Share</span>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <footer>
            <p class="brand">Summer AI</p>
            <p class="copyright" style="margin-top: 5px; font-size: 0.8rem;">Powered by FastAPI & Transformers</p>
            <p class="copyright">@2026 Summer AI. Built with ❤️.</p>
        </footer>
    </div>
</body>
</html>