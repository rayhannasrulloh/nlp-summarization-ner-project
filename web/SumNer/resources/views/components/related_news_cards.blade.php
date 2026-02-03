@if(empty($articles))
    <div class="col-span-full text-center py-8 bg-gray-50 rounded-xl border border-dashed border-gray-200">
        <p class="text-gray-400 text-sm">No related articles found for this topic.</p>
    </div>
@else
    @foreach($articles as $article)
        <a href="{{ $article['url'] }}" target="_blank" class="group block bg-white rounded-xl border border-gray-200 overflow-hidden hover:shadow-md hover:border-[#4a6fa5] transition-all animate-[fadeIn_0.5s_ease-out]">
            <div class="h-32 bg-gray-100 overflow-hidden relative">
                @if(!empty($article['urlToImage']))
                    <img src="{{ $article['urlToImage'] }}" alt="News" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                @else
                    <div class="w-full h-full flex items-center justify-center text-gray-300">
                        <i class="fa-regular fa-image text-3xl"></i>
                    </div>
                @endif
                <div class="absolute bottom-0 left-0 w-full bg-gradient-to-t from-black/60 to-transparent p-3">
                    <span class="text-[0.6rem] font-bold text-white uppercase tracking-wider bg-black/30 px-2 py-0.5 rounded backdrop-blur-sm">
                        {{ $article['source']['name'] ?? 'News' }}
                    </span>
                </div>
            </div>
            <div class="p-4">
                <h4 class="text-sm font-bold text-gray-800 leading-tight mb-2 line-clamp-3 group-hover:text-[#4a6fa5] transition-colors">
                    {{ $article['title'] }}
                </h4>
                <p class="text-[0.7rem] text-gray-400">
                    @if(isset($article['publishedAt']))
                    {{ \Carbon\Carbon::parse($article['publishedAt'])->diffForHumans() }}
                    @endif
                </p>
            </div>
        </a>
    @endforeach
@endif
