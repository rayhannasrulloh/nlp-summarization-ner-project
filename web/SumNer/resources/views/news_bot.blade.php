<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>News Summarization & NER Bot</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen p-8">
    <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-lg p-6">
        <div class="flex justify-between items-center mb-6 border-b pb-4">
            <h1 class="text-3xl font-bold text-gray-800">News Bot: Summarization & NER</h1>
            <div class="space-x-4">
                <span class="text-gray-500 text-sm italic">Guest Mode</span>
                <a href="{{ route('login') }}" class="text-indigo-600 hover:text-indigo-800 font-medium">Login</a>
            </div>
        </div>

        @include('news_bot_form_partial')
        
    </div>
</body>
</html>
