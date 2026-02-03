<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Model Settings') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row gap-6">
                <!-- Sidebar (Same as Dashboard for consistency or back button?) -->
                <!-- Let's make it a single focused card for settings -->
                
                <div class="w-full max-w-3xl mx-auto">
                    
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

                    <div class="bg-white rounded-[24px] border border-[rgba(0,0,0,0.15)] overflow-hidden">
                        <div class="p-8">
                            <h3 class="font-bold text-xl text-gray-800 mb-2 flex items-center gap-2">
                                <i class="fa-solid fa-sliders text-[#4a6fa5]"></i> Configure Models
                            </h3>
                            <p class="text-gray-500 text-sm mb-8">Fine-tune how the AI generates summaries and detects entities.</p>

                            <form action="{{ route('settings.update') }}" method="POST" class="space-y-8">
                                @csrf

                                <!-- Abstractive Settings -->
                                <div class="bg-gray-50 p-6 rounded-2xl border border-gray-100">
                                    <h4 class="font-bold text-gray-700 mb-4 flex items-center gap-2 text-sm uppercase tracking-wide">
                                        <i class="fa-solid fa-pen-nib text-gray-400"></i> Abstractive Model
                                    </h4>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Min Length (Words)</label>
                                            <input type="number" name="abstractive_min_length" value="{{ old('abstractive_min_length', $preferences->abstractive_min_length) }}" 
                                                class="w-full rounded-xl border-gray-200 focus:border-[#4a6fa5] focus:ring focus:ring-[#4a6fa5]/20 text-sm py-2.5">
                                            <p class="text-xs text-gray-400 mt-1">Minimum words in summary.</p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Max Length (Words)</label>
                                            <input type="number" name="abstractive_max_length" value="{{ old('abstractive_max_length', $preferences->abstractive_max_length) }}" 
                                                class="w-full rounded-xl border-gray-200 focus:border-[#4a6fa5] focus:ring focus:ring-[#4a6fa5]/20 text-sm py-2.5">
                                            <p class="text-xs text-gray-400 mt-1">Cutoff point for generation.</p>
                                        </div>
                                        <div class="col-span-full">
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Beam Search (Creativity vs Focus)</label>
                                            <div class="flex items-center gap-4">
                                                <input type="range" name="abstractive_num_beams" min="1" max="8" step="1" value="{{ old('abstractive_num_beams', $preferences->abstractive_num_beams) }}" 
                                                    class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-[#4a6fa5]"
                                                    oninput="document.getElementById('beams_val').innerText = this.value">
                                                <span id="beams_val" class="text-lg font-bold text-[#4a6fa5] w-6">{{ old('abstractive_num_beams', $preferences->abstractive_num_beams) }}</span>
                                            </div>
                                            <p class="text-xs text-gray-400 mt-1">Higher values (4-8) = Better quality but slower. Lower (1) = Faster.</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Extractive Settings -->
                                <div class="bg-gray-50 p-6 rounded-2xl border border-gray-100">
                                    <h4 class="font-bold text-gray-700 mb-4 flex items-center gap-2 text-sm uppercase tracking-wide">
                                        <i class="fa-solid fa-highlighter text-gray-400"></i> Extractive Model
                                    </h4>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Retention Ratio</label>
                                        <div class="flex items-center gap-4">
                                            <input type="range" name="extractive_retention_ratio" min="0.1" max="1.0" step="0.1" value="{{ old('extractive_retention_ratio', $preferences->extractive_retention_ratio) }}" 
                                                class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-[#4a6fa5]"
                                                 oninput="document.getElementById('ratio_val').innerText = Math.round(this.value * 100) + '%'">
                                            <span id="ratio_val" class="text-lg font-bold text-[#4a6fa5] w-12 text-right">{{ round(old('extractive_retention_ratio', $preferences->extractive_retention_ratio) * 100) }}%</span>
                                        </div>
                                        <p class="text-xs text-gray-400 mt-1">Percentage of original sentences to keep.</p>
                                    </div>
                                </div>

                                <!-- NER Settings -->
                                <div class="bg-gray-50 p-6 rounded-2xl border border-gray-100">
                                    <h4 class="font-bold text-gray-700 mb-4 flex items-center gap-2 text-sm uppercase tracking-wide">
                                        <i class="fa-solid fa-tags text-gray-400"></i> NER Model
                                    </h4>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Confidence Threshold</label>
                                        <div class="flex items-center gap-4">
                                            <input type="range" name="ner_threshold" min="0.1" max="0.99" step="0.05" value="{{ old('ner_threshold', $preferences->ner_threshold) }}" 
                                                class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-[#4a6fa5]"
                                                 oninput="document.getElementById('ner_val').innerText = Math.round(this.value * 100) + '%'">
                                            <span id="ner_val" class="text-lg font-bold text-[#4a6fa5] w-12 text-right">{{ round(old('ner_threshold', $preferences->ner_threshold) * 100) }}%</span>
                                        </div>
                                        <p class="text-xs text-gray-400 mt-1">Only show entities with confidence score above this level.</p>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="flex justify-between items-center pt-4 border-t border-gray-100 mt-6">
                                    
                                    <!-- Reset Form (Outside the main form? No, we need to handle this carefully. 
                                         Nested forms are invalid HTML. We'll use a separate form and position it with flexbox order or just put it outside.) -->
                                    <button form="reset-form" type="submit" class="text-gray-400 hover:text-red-500 font-medium text-sm transition flex items-center gap-2" onclick="return confirm('Are you sure you want to reset all settings to default?')">
                                        <i class="fa-solid fa-rotate-left"></i> Reset Defaults
                                    </button>

                                    <button type="submit" class="bg-[#0b3064] text-white px-8 py-3 rounded-full font-semibold hover:bg-[#092247] transition shadow-lg flex items-center gap-2">
                                        <i class="fa-solid fa-save"></i> Save Settings
                                    </button>
                                </div>

                            </form>
                            
                            <!-- Separate Reset Form -->
                            <form id="reset-form" action="{{ route('settings.destroy') }}" method="POST" class="hidden">
                                @csrf
                                @method('DELETE')
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
