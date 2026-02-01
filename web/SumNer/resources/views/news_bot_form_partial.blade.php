@if(session('status'))
    <div style="background-color: #d1e7dd; color: #0f5132; padding: 12px 20px; border-radius: 12px; margin-bottom: 20px; font-weight: 500; display: flex; align-items: center;">
        <i class="fa-solid fa-check-circle" style="margin-right: 10px;"></i>
        {{ session('status') }}
    </div>
@endif

@if($errors->any())
    <div style="background-color: #f8d7da; color: #842029; padding: 12px 20px; border-radius: 12px; margin-bottom: 20px; font-weight: 500;">
        <ul style="list-style: none;">
            @foreach ($errors->all() as $error)
                <li style="margin-bottom: 4px;"><i class="fa-solid fa-circle-exclamation" style="margin-right: 8px;"></i> {{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

    <div class="mb-6 grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Summary Type Selector -->
        <div>
            <label for="summary_type" class="block text-gray-700 font-semibold mb-2">Summary Type:</label>
            <select name="summary_type" id="summary_type" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-3 border">
                <option value="abstractive" {{ (old('summary_type') == 'abstractive') ? 'selected' : '' }}>Abstractive (AI Generated)</option>
                <option value="extractive" {{ (old('summary_type') == 'extractive') ? 'selected' : '' }}>Extractive (Key Sentences)</option>
            </select>
            <p class="text-xs text-gray-400 mt-1">Abstractive writes new text. Extractive picks important sentences.</p>
        </div>

        <!-- File Upload -->
        <div>
             <label for="news_pdf" class="block text-gray-700 font-semibold mb-2">Upload PDF (Optional):</label>
             <input type="file" name="news_pdf" id="news_pdf" accept="application/pdf" class="w-full border border-gray-300 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
    </div>

    <!-- Text Input -->
    <div class="mb-6">
        <label for="news_text" class="block text-gray-700 font-semibold mb-2">Paste News Text:</label>
        <textarea name="news_text" id="news_text" rows="6" 
            class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-3 border"
            placeholder="Paste or type news article here...">{{ old('news_text', $initialText ?? '') }}</textarea>
    </div>

    <div class="flex justify-between items-center mt-4">
        <!-- Paste Button (JS Helper) -->
        <button type="button" onclick="navigator.clipboard.readText().then(text => document.getElementById('news_text').value = text)" class="text-gray-500 hover:text-gray-700 text-sm flex items-center gap-1">
            <i class="fa-solid fa-paste"></i> Paste from Clipboard
        </button>

        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-8 rounded-lg shadow-lg hover:shadow-xl transition transform hover:-translate-y-0.5">
            Analyze Content
        </button>
    </div>
</form>

@php
    $finalResults = session('results') ?? $results ?? null;
@endphp

@if($finalResults)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mt-8 overflow-hidden">
        <div class="bg-indigo-50 px-6 py-4 border-b border-indigo-100">
            <h2 class="text-xl font-bold text-indigo-900">Analysis Results</h2>
        </div>
        
        <div class="p-6">
            <!-- Summary Section -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-800 mb-3 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Summary
                </h3>
                <div class="bg-gray-50 rounded-lg p-5 text-gray-700 leading-relaxed border border-gray-100">
                    {{ $finalResults['summary'] }}
                </div>
            </div>

            <!-- Sentiment Section -->
            <div class="mb-8 p-4 rounded-lg border {{ ($finalResults['sentiment']['label'] ?? '') == 'POSITIVE' ? 'bg-green-50 border-green-200' : ( ($finalResults['sentiment']['label'] ?? '') == 'NEGATIVE' ? 'bg-red-50 border-red-200' : 'bg-gray-50 border-gray-200') }}">
                <h3 class="text-lg font-semibold text-gray-800 mb-2 flex items-center">
                    Sentiment Analysis
                </h3>
                <div class="flex items-center gap-4">
                    <span class="font-bold text-2xl 
                        {{ ($finalResults['sentiment']['label'] ?? '') == 'POSITIVE' ? 'text-green-600' : ( ($finalResults['sentiment']['label'] ?? '') == 'NEGATIVE' ? 'text-red-600' : 'text-gray-600') }}">
                        {{ $finalResults['sentiment']['label'] ?? 'N/A' }}
                    </span>
                    <span class="text-sm text-gray-500 bg-white px-2 py-1 rounded border">
                        Confidence: {{ number_format(($finalResults['sentiment']['score'] ?? 0) * 100, 1) }}%
                    </span>
                </div>
            </div>

            <!-- Named Entities Section -->
            <div>
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                    Named Entities
                </h3>
            @if(empty($finalResults['entities']))
                <p style="color: #888; font-style: italic;">No entities found.</p>
            @else
                <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                    @foreach($finalResults['entities'] as $entity)
                        @php 
                            $entity = (array)$entity; 
                            $type = $entity['entity'] ?? 'MISC';
                            
                            // Map Entity Types to Summer AI Colors
                            $bgColor = '#f0f2f5'; // Default
                            $textColor = '#333';
                            $borderColor = '#ddd';
                            
                            if(str_contains($type, 'PER')) {
                                $bgColor = '#ffcccb'; $textColor = '#8b0000'; $borderColor = '#ffb3b3';
                            } elseif(str_contains($type, 'ORG')) {
                                $bgColor = '#d1e7dd'; $textColor = '#0f5132'; $borderColor = '#badbcc';
                            } elseif(str_contains($type, 'LOC')) {
                                $bgColor = '#cff4fc'; $textColor = '#055160'; $borderColor = '#b6effb';
                            }
                        @endphp

                        <span style="background-color: {{ $bgColor }}; color: {{ $textColor }}; border: 1px solid {{ $borderColor }}; padding: 4px 10px; border-radius: 12px; font-size: 0.9rem; font-weight: 500; display: inline-flex; align-items: center;" title="Score: {{ number_format($entity['score'], 2) }}">
                            {{ $entity['word'] }}
                            <span style="font-size: 0.7rem; opacity: 0.7; margin-left: 6px; text-transform: uppercase;">{{ $type }}</span>
                        </span>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endif