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
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="font-bold text-lg text-indigo-600">Your History</h3>
                                <a href="{{ route('dashboard') }}" class="text-xs bg-indigo-50 text-indigo-600 px-3 py-1 rounded-full border border-indigo-200 hover:bg-indigo-100 transition">
                                    + New Session
                                </a>
                            </div>
                            
                            @if($histories->isEmpty())
                                <p class="text-gray-500 text-sm">No history yet.</p>
                            @else
                                <div class="space-y-4 max-h-[600px] overflow-y-auto pr-2">
                                    @foreach($histories as $history)
                                        <div class="relative group">
                                            <!-- Link to dashboard with history ID -->
                                            <a href="{{ route('dashboard', ['history' => $history->id]) }}" class="block p-4 border rounded-lg bg-gray-50 hover:bg-gray-100 transition no-underline text-inherit">
                                                <p class="text-xs text-gray-400 mb-1">{{ $history->created_at->diffForHumans() }}</p>
                                                <p class="font-semibold text-sm line-clamp-2 text-gray-700">
                                                    {{ $history->input_text ? substr($history->input_text, 0, 80) . '...' : 'PDF Upload: ' . basename($history->input_pdf_path) }}
                                                </p>
                                            </a>

                                            <!-- Simple Delete Form -->
                                            <div class="absolute top-2 right-2 z-10">
                                                <form action="{{ route('history.destroy', $history->id) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-gray-400 hover:text-red-600 p-1.5 bg-white rounded-full shadow-sm border border-gray-200 hover:shadow-md transition" onclick="return confirm('Delete this item?')" title="Delete">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                            
                                        <!-- View Details (Just showing snippet for now, or re-populate form?) -->
                                        <!-- Snippet logic moved inside the link above -->
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Main Bot Interface -->
                <div class="w-full md:w-2/3">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <!-- Helper to display current analysis results from session -->
                            @include('news_bot_form_partial')
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
