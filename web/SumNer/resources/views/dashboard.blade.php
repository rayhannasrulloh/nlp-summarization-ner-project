<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row gap-6">
                
                <!-- History Sidebar -->
                <div class="w-full md:w-1/3">
                    <div class="bg-white rounded-[24px] border border-[rgba(0,0,0,0.15)] h-full">
                        <div class="p-6 h-full flex flex-col">
                            <div class="flex justify-between items-center mb-6">
                                <h3 class="font-bold text-lg text-gray-700 flex items-center gap-2">
                                    <i class="fa-solid fa-clock-rotate-left text-[#4a6fa5]"></i> Your History
                                </h3>
                                <a href="{{ route('dashboard') }}" class="text-xs bg-[#e6eff8] text-[#0b3064] px-4 py-2 rounded-full font-semibold hover:bg-[#d0e1f5] transition-colors">
                                    + New Session
                                </a>
                            </div>
                            
                            @if($histories->isEmpty())
                                <div class="flex-1 flex flex-col items-center justify-center text-center p-6 text-gray-400">
                                    <i class="fa-regular fa-folder-open text-4xl mb-3 opacity-50"></i>
                                    <p class="text-sm">No history yet.</p>
                                </div>
                            @else
                                <div class="space-y-3 flex-1 overflow-y-auto pr-2 custom-scrollbar">
                                    @foreach($histories as $history)
                                        <div class="relative group">
                                            <!-- Link to dashboard with history ID -->
                                            <a href="{{ route('dashboard', ['history' => $history->id]) }}" class="block p-4 border rounded-2xl bg-gray-50 hover:bg-white hover:border-[#4a6fa5] hover:shadow-sm transition-all duration-200 group-hover:pl-5">
                                                <div class="flex justify-between items-start mb-1">
                                                     <div class="flex items-center gap-2">
                                                        @if($history->input_url)
                                                            <span class="text-xs text-[#4a6fa5]" title="Link Input"><i class="fa-solid fa-link"></i></span>
                                                        @elseif($history->input_pdf_path)
                                                            <span class="text-xs text-red-500" title="PDF Input"><i class="fa-solid fa-file-pdf"></i></span>
                                                        @else
                                                            <span class="text-xs text-gray-400" title="Text Input"><i class="fa-solid fa-align-left"></i></span>
                                                        @endif
                                                        <p class="text-[0.7rem] font-bold tracking-wide text-gray-400 uppercase">
                                                            {{ $history->created_at->diffForHumans() }}
                                                        </p>
                                                     </div>
                                                     <div class="flex gap-1">
                                                        @if($history->category)
                                                            <span class="text-[0.65rem] px-2 py-0.5 rounded bg-blue-50 border border-blue-100 text-blue-600 font-semibold">
                                                                {{ $history->category }}
                                                            </span>
                                                        @endif
                                                         <span class="text-[0.65rem] px-2 py-0.5 rounded bg-white border text-gray-500">
                                                             {{ ucfirst($history->summary_type) }}
                                                         </span>
                                                     </div>
                                                </div>
                                                
                                                <p class="font-medium text-sm text-gray-700 line-clamp-2 leading-relaxed">
                                                    @if($history->input_url)
                                                        <span class="text-[#4a6fa5]">{{ $history->input_url }}</span>
                                                    @elseif($history->input_pdf_path)
                                                        PDF: {{ basename($history->input_pdf_path) }}
                                                    @else
                                                        {{ substr($history->input_text, 0, 80) . '...' }}
                                                    @endif
                                                </p>
                                            </a>

                                            <!-- Delete Button -->
                                            <div class="absolute top-1/2 -translate-y-1/2 right-4 opacity-0 group-hover:opacity-100 transition-opacity z-10">
                                                <form action="{{ route('history.destroy', $history->id) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-gray-400 hover:text-[#dc3545] p-2 bg-white rounded-full shadow border border-gray-100 hover:border-[#dc3545] transition-all transform hover:scale-110" onclick="return confirm('Delete this item?')" title="Delete">
                                                        <i class="fa-solid fa-trash-can"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Main Bot Interface -->
                <div class="w-full md:w-2/3">
                    <div class="bg-white rounded-[24px] border border-[rgba(0,0,0,0.15)] shadow-none">
                        <div class="p-8">
                            <div class="mb-6">
                                <h1 class="text-2xl font-bold text-gray-800 mb-2">News Bot Analysis</h1>
                                <p class="text-gray-500 text-sm">Summarize articles and extract entities with AI.</p>
                            </div>
                            <!-- Helper to display current analysis results from session -->
                            @include('news_bot_form_partial')
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
