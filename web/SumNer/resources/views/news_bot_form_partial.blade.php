@if(session('status'))
    <div class="mb-4 text-green-600 font-semibold">{{ session('status') }}</div>
@endif

<form action="{{ route('news.process') }}" method="POST" enctype="multipart/form-data" class="mb-8">
    @csrf
    
    <div class="mb-6 grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label for="summary_type" class="block text-gray-700 font-semibold mb-2">Summary Type:</label>
            <select name="summary_type" id="summary_type" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-3 border">
                <option value="abstractive" {{ (old('summary_type') == 'abstractive') ? 'selected' : '' }}>Abstractive (AI Generated)</option>
                <option value="extractive" {{ (old('summary_type') == 'extractive') ? 'selected' : '' }}>Extractive (Key Sentences)</option>
            </select>
            <p class="text-xs text-gray-400 mt-1">Abstractive writes new text. Extractive picks important sentences.</p>
        </div>
    </div>

    <div class="mb-6">
        <label for="news_text" class="block text-gray-700 font-semibold mb-2">Paste News Text:</label>
        <textarea name="news_text" id="news_text" rows="6" 
            class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-3 border"
            placeholder="Paste or type news article here...">{{ old('news_text', $initialText ?? '') }}</textarea>
    </div>

    <div class="mb-6">
        <label class="block text-gray-700 font-semibold mb-2">OR Upload PDF:</label>
        <input type="file" name="news_pdf" accept="application/pdf"
            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
    </div>

    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-6 rounded-lg transition duration-200">
        Analyze Content
    </button>
</form>

<!-- Results Section -->
@php
    $finalResults = session('results') ?? $results ?? null;
@endphp

@if($finalResults)
    <div class="bg-green-50 rounded-lg p-6 border border-green-200 mt-6">
        <h2 class="text-2xl font-bold mb-4 text-green-800">Results</h2>
        
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-2">Summary</h3>
            <p class="text-gray-700 leading-relaxed bg-white p-4 rounded border">
                {{ $finalResults['summary'] }}
            </p>
        </div>

        <div class="mb-6 p-4 rounded-lg border {{ ($finalResults['sentiment']['label'] ?? '') == 'POSITIVE' ? 'bg-green-100 border-green-200' : ( ($finalResults['sentiment']['label'] ?? '') == 'NEGATIVE' ? 'bg-red-100 border-red-200' : 'bg-gray-100 border-gray-200') }}">
            <h3 class="text-lg font-semibold text-gray-800 mb-2 flex items-center">
                Sentiment Analysis
                <span class="ml-2 text-xs font-normal border px-2 py-0.5 rounded bg-white">Experimental</span>
            </h3>
            <div class="flex items-center gap-4">
                <span class="font-bold text-xl 
                    {{ ($finalResults['sentiment']['label'] ?? '') == 'POSITIVE' ? 'text-green-700' : ( ($finalResults['sentiment']['label'] ?? '') == 'NEGATIVE' ? 'text-red-700' : 'text-gray-700') }}">
                    {{ $finalResults['sentiment']['label'] ?? 'N/A' }}
                </span>
                <span class="text-sm text-gray-600">
                    Confidence: {{ number_format(($finalResults['sentiment']['score'] ?? 0) * 100, 1) }}%
                </span>
            </div>
        </div>

        <div>
            <h3 class="text-lg font-semibold text-gray-800 mb-2">Named Entities</h3>
            @if(empty($finalResults['entities']))
                <p class="text-gray-500 italic">No entities found.</p>
            @else
                <div class="flex flex-wrap gap-2">
                    @foreach($finalResults['entities'] as $entity)
                        <!-- Handle array or object from JSON decode -->
                        @php $entity = (array)$entity; @endphp 
                        <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-medium border border-blue-200" title="Score: {{ number_format($entity['score'], 2) }}">
                            {{ $entity['word'] }} 
                            <span class="text-xs uppercase ml-1 opacity-75">({{ $entity['entity'] }})</span>
                        </span>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endif

@if($errors->any())
    <div class="bg-red-50 text-red-700 p-4 rounded-lg mt-4 border border-red-200">
        <ul>
            @foreach ($errors->all() as $error)
                <li>• {{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
