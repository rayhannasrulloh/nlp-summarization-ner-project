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

<form action="{{ route('news.process') }}" method="POST" enctype="multipart/form-data" style="height: 100%; display: flex; flex-direction: column; flex: 1;">
    @csrf
    
    <div class="card-top-bar" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; flex-wrap: wrap; gap: 10px;">
        
        <div class="input-type-toggle" style="background-color: #f0f2f5; padding: 4px; border-radius: 16px; display: inline-flex; align-items: center;">
            <label for="news_pdf" class="toggle-btn" style="cursor: pointer; margin-bottom: 0;">
                <i class="fa-solid fa-file-pdf"></i> Upload PDF
            </label>
            <input type="file" name="news_pdf" id="news_pdf" accept="application/pdf" style="font-size: 0.9rem; color: #666; background: transparent; border: none; padding: 4px 10px; width: 200px;">
        </div>

        <div class="options-dropdowns">
            <span style="font-size: 0.9rem; color: #888; font-weight: 500;">AI Analysis</span>
        </div>
    </div>

    <textarea name="news_text" id="news_text" 
        placeholder="Enter or paste your text here to summarize and extract entities..."
        style="flex: 1; border: none; resize: none; padding: 15px; font-family: 'Inter', sans-serif; font-size: 1.1rem; outline: none; color: #333; background-color: transparent; width: 100%; min-height: 200px;"
    >{{ old('news_text', $initialText ?? '') }}</textarea>

    <div class="card-bottom-bar" style="display: flex; justify-content: space-between; align-items: center; margin-top: 10px; padding-top: 20px; border-top: 1px solid #eee;">
        <button type="button" class="paste-btn" onclick="navigator.clipboard.readText().then(text => document.getElementById('news_text').value = text)" style="border: none; background-color: #f0f2f5; padding: 10px 20px; border-radius: 16px; cursor: pointer; font-family: 'Inter', sans-serif; font-weight: 500; color: #666; display: flex; align-items: center; font-size: 0.9rem;">
            <i class="fa-solid fa-paste" style="margin-right: 8px;"></i> Paste
        </button>

        <button type="submit" class="summarize-action-btn" style="border: none; background-color: #4a6fa5; color: #fff; padding: 12px 32px; border-radius: 16px; cursor: pointer; font-family: 'Inter', sans-serif; font-weight: 600; font-size: 1rem; transition: background-color 0.3s ease;">
            Analyze Content
        </button>
    </div>
</form>

@php
    $finalResults = session('results') ?? $results ?? null;
@endphp

@if($finalResults)
    <div style="margin-top: 40px; padding-top: 30px; border-top: 2px dashed #eee;">
        <h2 style="font-size: 1.5rem; font-weight: 700; color: #333; margin-bottom: 20px; text-align: center;">Analysis Results</h2>
        
        <div style="background-color: #f9fafb; padding: 25px; border-radius: 20px; margin-bottom: 25px; border: 1px solid #eef2f6;">
            <h3 style="font-size: 1.1rem; font-weight: 600; color: #4a6fa5; margin-bottom: 12px; display: flex; align-items: center;">
                <i class="fa-solid fa-align-left" style="margin-right: 10px;"></i> Summary
            </h3>
            <p style="font-size: 1.05rem; line-height: 1.6; color: #444;">
                {{ $finalResults['summary'] }}
            </p>
        </div>

        <div style="background-color: #fff; padding: 25px; border-radius: 20px; border: 1px solid #eef2f6;">
            <h3 style="font-size: 1.1rem; font-weight: 600; color: #4a6fa5; margin-bottom: 15px; display: flex; align-items: center;">
                <i class="fa-solid fa-tags" style="margin-right: 10px;"></i> Named Entities
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