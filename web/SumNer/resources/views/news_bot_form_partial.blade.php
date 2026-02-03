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
    
    <!-- Input Tabs & Type Selector Header -->
    <div x-data="{ tab: 'text' }" class="mb-6">
        <input type="hidden" name="input_source" :value="tab">
        <div class="flex flex-wrap justify-between items-center border-b border-gray-200 mb-6 pb-2">
            <!-- Tabs -->
            <div class="flex space-x-2 sm:space-x-4 ">
                <button type="button" @click="tab = 'text'" :class="{ 'text-[#0b3064] font-bold bg-blue-50/50': tab === 'text', 'text-gray-500 hover:text-gray-700 hover:bg-gray-50': tab !== 'text' }" class="py-2 px-3 rounded-lg border border-[rgba(0,0,0,0.15)] transition-all flex items-center gap-2 text-sm">
                    <i class="fa-solid fa-align-left"></i> <span class="hidden sm:inline">Paste Text</span><span class="sm:hidden">Text</span>
                </button>
                <button type="button" @click="tab = 'url'" :class="{ 'text-[#0b3064] font-bold bg-blue-50/50': tab === 'url', 'text-gray-500 hover:text-gray-700 hover:bg-gray-50': tab !== 'url' }" class="py-2 px-3 rounded-lg border border-[rgba(0,0,0,0.15)] transition-all flex items-center gap-2 text-sm">
                    <i class="fa-solid fa-link"></i> <span class="hidden sm:inline">Paste URL</span><span class="sm:hidden">URL</span>
                </button>
                <button type="button" @click="tab = 'pdf'" :class="{ 'text-[#0b3064] font-bold bg-blue-50/50': tab === 'pdf', 'text-gray-500 hover:text-gray-700 hover:bg-gray-50': tab !== 'pdf' }" class="py-2 px-3 rounded-lg border border-[rgba(0,0,0,0.15)] transition-all flex items-center gap-2 text-sm">
                    <i class="fa-solid fa-file-pdf"></i> <span class="hidden sm:inline">Upload PDF</span><span class="sm:hidden">PDF</span>
                </button>
            </div>

            <!-- Type Selector (Top Right) -->
            <div class="flex items-center gap-2">
                <label for="summary_type" class="text-xs font-bold text-gray-400 uppercase tracking-wider cursor-pointer">Type</label>
                <div class="relative">
                    <select name="summary_type" id="summary_type" 
                        class="bg-gray-50 border border-gray-200 text-sm font-semibold text-gray-700 rounded-lg focus:ring-[#4a6fa5] focus:border-[#4a6fa5] py-1.5 pl-3 pr-8 cursor-pointer outline-none appearance-none" 
                        style="-webkit-appearance: none; -moz-appearance: none; appearance: none;">
                        <option value="abstractive" {{ (old('summary_type') == 'abstractive') ? 'selected' : '' }}>Abstractive</option>
                        <option value="extractive" {{ (old('summary_type') == 'extractive') ? 'selected' : '' }}>Extractive</option>
                    </select>
                    <i class="fa-solid fa-chevron-down text-xs text-gray-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                </div>
            </div>
        </div>

        <!-- 1. Text Input -->
        <div x-show="tab === 'text'" class="space-y-4">
            <div class="relative w-full h-[320px] bg-gray-50 rounded-[24px] border border-[rgba(0,0,0,0.15)] transition-colors focus-within:bg-white focus-within:border-[#4a6fa5] focus-within:ring-4 focus-within:ring-[#4a6fa5]/10">
                <textarea name="news_text" id="news_text" 
                    placeholder="Paste your news article here..."
                    class="w-full h-full bg-transparent border-none resize-none p-6 pb-20 font-sans text-base text-gray-700 placeholder-gray-400 outline-none rounded-[24px]"
                >{{ old('news_text', $initialText ?? '') }}</textarea>
                
                <div class="absolute bottom-4 left-4 flex pointer-events-none">
                    <button type="button" onclick="navigator.clipboard.readText().then(text => document.getElementById('news_text').value = text)" 
                        class="pointer-events-auto flex items-center gap-2 px-4 py-2 bg-white hover:bg-gray-100 border border-[rgba(0,0,0,0.15)] rounded-xl text-gray-500 hover:text-[#4a6fa5] font-medium text-xs transition-all shadow-none">
                         <i class="fa-solid fa-paste"></i> Paste
                    </button>
                </div>
            </div>
            @error('news_text') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
        
        <!-- 2. URL Input -->
        <div x-show="tab === 'url'" class="space-y-4 py-8 px-4 bg-gray-50 rounded-[24px] border border-dashed border-gray-300" style="display: none;">
            <div class="relative max-w-2xl mx-auto">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <i class="fa-solid fa-globe text-gray-400"></i>
                </div>
                <input type="url" name="news_url" 
                    class="w-full pl-11 rounded-xl border-gray-200 focus:border-[#4a6fa5] focus:ring focus:ring-[#4a6fa5]/20 text-[15px] py-3 placeholder:text-gray-400" 
                    placeholder="https://cnn.com/article/..." value="{{ old('news_url') }}">
            </div>
             <p class="text-xs text-center text-gray-500">The AI will extract the main content, title, and image from the link.</p>
            @error('news_url') <p class="text-red-500 text-xs mt-1 text-center">{{ $message }}</p> @enderror
        </div>

        <!-- 3. PDF Input -->
        <div x-show="tab === 'pdf'" class="space-y-2 py-8" style="display: none;">
             <div class="relative border-2 border-dashed border-gray-300 rounded-[24px] p-12 text-center hover:border-[#4a6fa5] hover:bg-blue-50/30 transition-colors cursor-pointer" onclick="document.getElementById('news_pdf').click()">
                <input type="file" name="news_pdf" id="news_pdf" class="hidden" accept=".pdf" onchange="document.getElementById('file-name-display').innerText = this.files[0].name">
                <div class="space-y-3">
                    <div class="w-16 h-16 bg-[#eaf1f8] rounded-full flex items-center justify-center mx-auto text-[#4a6fa5]">
                        <i class="fa-solid fa-cloud-arrow-up text-3xl"></i>
                    </div>
                    <p class="text-base font-semibold text-gray-700">Click to upload PDF</p>
                    <p id="file-name-display" class="text-sm text-gray-400">Max size: 10MB</p>
                </div>
            </div>
            @error('news_pdf') <p class="text-red-500 text-xs mt-1 text-center">{{ $message }}</p> @enderror
        </div>

        <!-- Submit Button Only (Footer) -->
        <div class="flex justify-end items-center pt-4 border-t border-gray-100">
             <button type="submit" class="relative group w-full sm:w-auto rounded-full p-[2px] overflow-hidden shadow-none transform-none cursor-pointer">
                <div class="absolute inset-[-100%] bg-[conic-gradient(from_0deg,#ff4545,#ffa534,#ffe234,#57ff34,#34e1ff,#3456ff,#b834ff,#ff4545)] opacity-0 group-hover:opacity-100 transition-opacity duration-500 animate-[spin_3s_linear_infinite]"></div>
                <div class="relative z-10 flex items-center justify-center gap-2 px-10 py-3 bg-gradient-to-r from-[#282828] via-[#2f2f2f] to-[#5f5f5f] text-white rounded-full font-semibold text-sm h-full w-full leading-none">
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
        <span class="flex-shrink-0 mx-4 text-gray-400 text-xs font-bold tracking-wider uppercase flex items-center gap-2">
            Analysis Results
            @if(isset($finalResults['processing_time']))
                <span class="bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full font-normal normal-case border border-gray-200">
                    <i class="fa-solid fa-stopwatch mr-1"></i> {{ $finalResults['processing_time'] }}s
                </span>
            @endif
        </span>
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

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <!-- Category Card -->
            <div class="p-5 rounded-2xl border border-blue-100 bg-blue-50/50 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-white flex items-center justify-center text-blue-600 border border-blue-100 shadow-sm">
                        <i class="fa-solid fa-layer-group text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wide">Category</h3>
                        <span class="text-lg font-bold text-gray-800 tracking-tight">
                            {{ $finalResults['category'] ?? 'General' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Sentiment Card -->
            <div class="p-5 rounded-2xl border {{ $cardBorder }} {{ $cardBg }} flex items-center justify-between shadow-none">
                <div class="flex items-center gap-4">
                     <div class="w-12 h-12 rounded-full bg-white/60 flex items-center justify-center {{ $iconColor }} border border-white/50">
                         <i class="fa-solid fa-heart-pulse text-lg"></i>
                     </div>
                     <div>
                         <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wide">Sentiment</h3>
                         <span class="text-lg font-bold {{ $textColor }} tracking-tight">
                            {{ $label }}
                        </span>
                     </div>
                </div>
                <div class="bg-white/60 px-3 py-1 rounded-lg border border-white/50 text-sm font-semibold text-gray-600">
                    {{ number_format($score, 1) }}% <span class="text-xs font-normal text-gray-500">Conf.</span>
                </div>
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

        @if(!empty($finalResults['related_news_query']))
            <div x-data="{ 
                loading: false, 
                loaded: false, 
                query: '{{ $finalResults['related_news_query'] }}',
                category: '{{ $finalResults['category'] ?? '' }}',
                title: '{{ addslashes($finalResults['title'] ?? '') }}'
            }" class="mt-8 border-t border-[rgba(0,0,0,0.1)] pt-8">
                
                <h3 class="text-sm font-bold text-gray-700 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-newspaper text-[#4a6fa5]"></i> Related Coverage
                </h3>
                
                <!-- Initial State: Button -->
                <div x-show="!loaded && !loading" class="text-center py-6 bg-blue-50/50 rounded-xl border border-blue-100">
                    <p class="text-gray-500 mb-4 text-sm">Discover what other sources are saying about this topic.</p>
                    <button type="button" 
                        @click="
                            loading = true;
                            fetch('{{ route('news.related') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({ query: query, category: category, title: title })
                            })
                            .then(response => response.text())
                            .then(html => {
                                $refs.resultsContainer.innerHTML = html;
                                loaded = true;
                                loading = false;
                            })
                            .catch(() => {
                                loading = false;
                                alert('Failed to load related news.');
                            })
                        "
                        class="px-6 py-2.5 bg-white border border-gray-200 text-gray-700 font-semibold rounded-full shadow-sm hover:border-[#4a6fa5] hover:text-[#4a6fa5] transition-all text-sm group">
                        <i class="fa-solid fa-search mr-2 text-gray-400 group-hover:text-[#4a6fa5]"></i> Find Related News
                    </button>
                </div>

                <!-- Loading State -->
                <div x-show="loading" class="text-center py-10">
                    <i class="fa-solid fa-circle-notch fa-spin text-2xl text-[#4a6fa5] mb-3"></i>
                    <p class="text-xs text-gray-400 font-medium uppercase tracking-wider">Searching Global News...</p>
                </div>

                <!-- Results Container -->
                <div x-show="loaded" x-ref="resultsContainer" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Cards injected here -->
                </div>

            </div>
        @endif
    </div>
@endif