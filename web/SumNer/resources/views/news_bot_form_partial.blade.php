@if(session('status'))
    <div class="bg-[#d1e7dd] text-[#0f5132] px-5 py-3 rounded-2xl mb-6 flex items-center font-medium border border-[#badbcc]">
        <i class="fa-solid fa-check-circle mr-3"></i>
        {{ session('status') }}
    </div>
@endif

@if($errors->any())
    <div class="bg-[#f8d7da] text-[#842029] px-5 py-3 rounded-2xl mb-6 font-medium border border-[#f5c2c7]">
        <ul class="list-none m-0 p-0">
            @foreach ($errors->all() as $error)
                <li class="mb-1 flex items-center">
                    <i class="fa-solid fa-circle-exclamation mr-2"></i> {{ $error }}
                </li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('news.process') }}" method="POST" enctype="multipart/form-data" class="flex flex-col gap-6">
    @csrf
    
    <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
        
        <label for="news_pdf" class="cursor-pointer group flex items-center gap-3 px-5 py-2.5 bg-gray-50 hover:bg-gray-100 border border-[rgba(0,0,0,0.15)] rounded-2xl transition-all w-full sm:w-auto justify-center">
            <i class="fa-solid fa-cloud-arrow-up text-[#4a6fa5] group-hover:scale-110 transition-transform"></i>
            <span id="file_name" class="text-sm font-semibold text-gray-600 truncate max-w-[200px]">Upload PDF</span>
            <input type="file" name="news_pdf" id="news_pdf" accept="application/pdf" class="hidden" 
                   onchange="document.getElementById('file_name').textContent = this.files[0] ? this.files[0].name : 'Upload PDF'; this.parentElement.classList.add('bg-blue-50', 'border-blue-200');">
        </label>

        <div class="flex items-center gap-3 px-4 py-2.5 border border-[rgba(0,0,0,0.15)] rounded-2xl bg-white w-full sm:w-auto">
            <label for="summary_type" class="text-xs font-bold text-gray-400 uppercase tracking-wider cursor-pointer">Type</label>
            <div class="h-4 w-px bg-gray-200"></div>
            <div class="relative flex-1">
                <select name="summary_type" id="summary_type" class="w-full bg-transparent border-none text-sm font-semibold text-gray-700 focus:ring-0 cursor-pointer outline-none appearance-none pr-6">
                    <option value="abstractive" {{ (old('summary_type') == 'abstractive') ? 'selected' : '' }}>Abstractive</option>
                    <option value="extractive" {{ (old('summary_type') == 'extractive') ? 'selected' : '' }}>Extractive</option>
                </select>
                <i class="fa-solid fa-chevron-down text-xs text-gray-400 absolute right-0 top-1/2 -translate-y-1/2 pointer-events-none"></i>
            </div>
        </div>
    </div>

    <div class="relative w-full h-[320px] bg-gray-50 rounded-[24px] border border-[rgba(0,0,0,0.15)] transition-colors focus-within:bg-white focus-within:border-[#4a6fa5] focus-within:ring-4 focus-within:ring-[#4a6fa5]/10">
        <textarea name="news_text" id="news_text" 
            placeholder="Paste your news article here..."
            class="w-full h-full bg-transparent border-none resize-none p-6 pb-20 font-sans text-base text-gray-700 placeholder-gray-400 outline-none rounded-[24px]"
        >{{ old('news_text', $initialText ?? '') }}</textarea>
        
        <div class="absolute bottom-4 left-4 right-4 flex justify-between items-center pointer-events-none">
            
            <button type="button" onclick="navigator.clipboard.readText().then(text => document.getElementById('news_text').value = text)" 
                class="pointer-events-auto flex items-center gap-2 px-4 py-2 bg-white hover:bg-gray-100 border border-[rgba(0,0,0,0.15)] rounded-xl text-gray-500 hover:text-[#4a6fa5] font-medium text-xs transition-all shadow-none">
                 <i class="fa-solid fa-paste"></i> Paste
            </button>

            <button type="submit" class="pointer-events-auto relative group w-auto rounded-full p-[2px] overflow-hidden shadow-none transform-none cursor-pointer">
                    <div class="absolute inset-[-100%] bg-[conic-gradient(from_0deg,#ff4545,#ffa534,#ffe234,#57ff34,#34e1ff,#3456ff,#b834ff,#ff4545)] opacity-0 group-hover:opacity-100 transition-opacity duration-500 animate-[spin_3s_linear_infinite]"></div>
                    
                    <div class="relative z-10 flex items-center justify-center gap-2 px-8 py-3 bg-gradient-to-r from-[#282828] via-[#2f2f2f] to-[#5f5f5f] text-white rounded-full font-semibold text-sm h-full w-full leading-none">
                        Analyze Content
                        <i class="fa-solid fa-wand-magic-sparkles ml-1"></i>
                    </div>
                </button>
        </div>
    </div>
</form>

@php
    $finalResults = session('results') ?? $results ?? null;
@endphp

@if($finalResults)
    <div class="flex items-center my-10">
        <div class="flex-grow border-t border-[rgba(0,0,0,0.1)]"></div>
        <span class="flex-shrink-0 mx-4 text-gray-400 text-xs font-bold tracking-wider uppercase">Analysis Results</span>
        <div class="flex-grow border-t border-[rgba(0,0,0,0.1)]"></div>
    </div>

    <div class="animate-[fadeIn_0.5s_ease-out]">
        
        @php
            $label = $finalResults['sentiment']['label'] ?? 'NEUTRAL';
            $score = ($finalResults['sentiment']['score'] ?? 0) * 100;
            
            // Colors based on sentiment
            $cardBg = $label == 'POSITIVE' ? 'bg-[#d1e7dd]' : ($label == 'NEGATIVE' ? 'bg-[#f8d7da]' : 'bg-gray-50');
            $cardBorder = $label == 'POSITIVE' ? 'border-[#badbcc]' : ($label == 'NEGATIVE' ? 'border-[#f5c2c7]' : 'border-[rgba(0,0,0,0.15)]');
            $iconColor = $label == 'POSITIVE' ? 'text-[#0f5132]' : ($label == 'NEGATIVE' ? 'text-[#842029]' : 'text-gray-600');
            $textColor = $label == 'POSITIVE' ? 'text-[#0f5132]' : ($label == 'NEGATIVE' ? 'text-[#842029]' : 'text-gray-800');
        @endphp

        <div class="p-5 rounded-2xl border {{ $cardBorder }} {{ $cardBg }} flex items-center justify-between mb-6 shadow-none">
            <div class="flex items-center gap-4">
                 <div class="w-12 h-12 rounded-full bg-white/60 flex items-center justify-center {{ $iconColor }} border border-white/50">
                     <i class="fa-solid fa-heart-pulse text-lg"></i>
                 </div>
                 <div>
                     <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wide">Sentiment</h3>
                     <span class="text-xl font-extrabold {{ $textColor }} tracking-tight">
                        {{ $label }}
                    </span>
                 </div>
            </div>
            <div class="bg-white/60 px-3 py-1 rounded-lg border border-white/50 text-sm font-semibold text-gray-600">
                {{ number_format($score, 1) }}% <span class="text-xs font-normal text-gray-500">Conf.</span>
            </div>
        </div>

        <div class="mb-8">
            <h3 class="text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
                <i class="fa-solid fa-align-left text-[#4a6fa5]"></i> Generated Summary
            </h3>
            <div class="bg-gray-50 p-6 rounded-[24px] border border-[rgba(0,0,0,0.15)] text-gray-700 leading-relaxed text-[1.05rem]">
                {{ $finalResults['summary'] }}
            </div>
        </div>

        <div>
            <h3 class="text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
                <i class="fa-solid fa-tags text-[#4a6fa5]"></i> Named Entities
            </h3>
            
            @if(empty($finalResults['entities']))
                <p class="text-gray-400 italic text-sm">No specific entities detected.</p>
            @else
                <div class="flex flex-wrap gap-2">
                    @foreach($finalResults['entities'] as $entity)
                        @php 
                            $entity = (array)$entity; 
                            $type = $entity['entity'] ?? 'MISC';
                            
                            // Tailwind classes for Entities
                            // Using arbitrary values for specific pastel colors requested
                            $classes = 'bg-gray-100 text-gray-700 border-gray-200'; // Default
                            
                            if(str_contains($type, 'PER')) {
                                $classes = 'bg-[#ffe5e5] text-[#8b0000] border-[#ffb3b3]';
                            } elseif(str_contains($type, 'ORG')) {
                                $classes = 'bg-[#e2f0e9] text-[#0f5132] border-[#badbcc]';
                            } elseif(str_contains($type, 'LOC')) {
                                $classes = 'bg-[#e0f7fa] text-[#055160] border-[#b6effb]';
                            }
                        @endphp

                        <span class="px-3 py-1.5 rounded-full border text-sm font-medium flex items-center gap-2 {{ $classes }}">
                            {{ $entity['word'] }}
                            <span class="text-[0.65rem] opacity-70 uppercase tracking-wider font-bold border-l border-current pl-1.5 ml-0.5">{{ $type }}</span>
                        </span>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endif