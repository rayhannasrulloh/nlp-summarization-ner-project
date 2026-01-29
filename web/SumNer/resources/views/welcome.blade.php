<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SumNer - AI News Summarization</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .gradient-text {
            background: linear-gradient(to right, #4f46e5, #9333ea);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 font-sans antialiased">
    <div class="min-h-screen flex flex-col justify-center items-center relative overflow-hidden">
        
        <!-- Background Elements -->
        <div class="absolute top-0 left-0 w-full h-full overflow-hidden -z-10">
            <div class="absolute top-[-10%] left-[-10%] w-96 h-96 bg-purple-300 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob"></div>
            <div class="absolute top-[-10%] right-[-10%] w-96 h-96 bg-indigo-300 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob animation-delay-2000"></div>
            <div class="absolute bottom-[-20%] left-[20%] w-96 h-96 bg-pink-300 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob animation-delay-4000"></div>
        </div>

        <div class="text-center space-y-8 p-8 bg-white/80 backdrop-blur-sm rounded-2xl shadow-2xl border border-white/20 max-w-2xl">
            <h1 class="text-6xl font-extrabold tracking-tight">
                Welcome to <span class="gradient-text">SumNer</span>
            </h1>
            <p class="text-xl text-gray-600 leading-relaxed">
                Experience the power of AI-driven news summarization and entity recognition. 
                Transform long articles and PDF documents into concise insights instantly.
            </p>
            
            <div class="pt-4">
                <a href="{{ route('login') }}" 
                   class="inline-flex items-center justify-center px-8 py-4 text-lg font-bold text-white transition-all duration-200 bg-indigo-600 rounded-full hover:bg-indigo-700 hover:shadow-lg hover:-translate-y-1 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Try Now
                    <svg class="w-5 h-5 ml-2 -mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
            
            <p class="text-sm text-gray-400 pt-8">
                Powered by FastAPI & Transformers
            </p>
        </div>
    </div>
</body>
</html>
