@if(session('status'))
    <div style="background-color: #d1e7dd; color: #0f5132; padding: 12px 20px; border-radius: 12px; margin-bottom: 20px; font-weight: 500; display: flex; align-items: center;">
        <i class="fa-solid fa-check-circle" style="margin-right: 10px;"></i>
        {{ session('status') }}
    </div>
@endif

@if($errors->any())
    <div style="background-color: #f8d7da; color: #842029; padding: 12px 20px; border-radius: 12px; margin-bottom: 20px; font-weight: 500;">
        <ul style="list-style: none; margin: 0; padding: 0;">
            @foreach ($errors->all() as $error)
                <li style="margin-bottom: 4px; display: flex; align-items: center;">
                    <i class="fa-solid fa-circle-exclamation" style="margin-right: 8px;"></i> {{ $error }}
                </li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('news.process') }}" method="POST" enctype="multipart/form-data" class="flex flex-col">
    @csrf
    
    <div class="card-top-bar" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; flex-wrap: wrap; gap: 10px;">
        
        <!-- Left Side: File Upload (Custom Styled) -->
        <div class="input-type-toggle" style="background-color: #f0f2f5; padding: 4px; border-radius: 16px; display: inline-flex; align-items: center;">
            <label for="news_pdf" id="file_label" class="toggle-btn" style="cursor: pointer; margin-bottom: 0; font-size: 0.9rem; font-weight: 500; padding: 6px 16px; color: #4a6fa5; display: flex; align-items: center; transition: all 0.2s;">
                <i class="fa-solid fa-cloud-arrow-up" style="margin-right: 8px;"></i> 
                <span id="file_name">Upload PDF</span>
            </label>
            <input type="file" name="news_pdf" id="news_pdf" accept="application/pdf" style="display: none;" onchange="document.getElementById('file_name').textContent = this.files[0] ? this.files[0].name : 'Upload PDF'; document.getElementById('file_label').style.color = '#333';">
        </div>

        <!-- Right Side: Summary Type & Analysis Options -->
        <div class="options-dropdowns" style="display: flex; align-items: center; gap: 10px;">
            <div style="display: flex; align-items: center; background-color: #fff; border: 1px solid #e1e4e8; border-radius: 12px; padding: 4px 12px;">
                 <label for="summary_type" style="font-size: 0.85rem; color: #888; margin-right: 8px; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px;">Type</label>
                 <select name="summary_type" id="summary_type" style="border: none; background: transparent; font-size: 0.9rem; color: #333; font-weight: 600; cursor: pointer; outline: none; padding-right: 0;">
                    <option value="abstractive" {{ (old('summary_type') == 'abstractive') ? 'selected' : '' }}>Abstractive</option>
                    <option value="extractive" {{ (old('summary_type') == 'extractive') ? 'selected' : '' }}>Extractive</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Main Text Area (Fixed Height) -->
    <div style="position: relative; height: 280px; border: 1px solid #eef2f6; border-radius: 16px; padding: 5px; transition: border-color 0.3s; background-color: #fbfbfc; margin-bottom: 20px;">
        <textarea name="news_text" id="news_text" 
            placeholder="Paste your news article here..."
            style="width: 100%; height: 100%; border: none; resize: none; padding: 15px; padding-bottom: 50px; font-family: 'Inter', sans-serif; font-size: 1.05rem; outline: none; color: #333; background-color: transparent; line-height: 1.6;"
        >{{ old('news_text', $initialText ?? '') }}</textarea>
        
        <!-- Bottom Right Paste Button inside textarea area -->
        <button type="button" onclick="navigator.clipboard.readText().then(text => document.getElementById('news_text').value = text)" style="position: absolute; bottom: 15px; left: 15px; border: none; background: transparent; color: #999; cursor: pointer; font-size: 0.85rem; display: flex; align-items: center; font-weight: 500;">
             <i class="fa-solid fa-paste" style="margin-right: 6px;"></i> Paste from Clipboard
        </button>

        <!-- Analyze Button Inside the Card Flow, Bottom Right of Textarea -->
        <button type="submit" class="summarize-action-btn" style="position: absolute; bottom: 10px; right: 10px; border: none; background: linear-gradient(135deg, #4a6fa5 0%, #3b5c8d 100%); color: #fff; padding: 10px 24px; border-radius: 12px; cursor: pointer; font-family: 'Inter', sans-serif; font-weight: 600; font-size: 0.95rem; transition: transform 0.2s, box-shadow 0.2s; box-shadow: 0 4px 12px rgba(74, 111, 165, 0.2);">
            Analyze Content
        </button>
    </div>
</form>

@php
    $finalResults = session('results') ?? $results ?? null;
@endphp

@if($finalResults)
    <!-- Divider -->
    <div style="border-top: 2px dashed #eee; margin: 30px 0; position: relative; text-align: center;">
        <span style="background: #fff; padding: 0 15px; font-weight: 600; color: #aaa; position: relative; top: -10px; font-size: 0.9rem;">RESULTS</span>
    </div>

    <div style="animation: fadeIn 0.5s ease-out;">
        <!-- Sentiment Card (Full Width Highlight) -->
        <div style="padding: 15px 20px; border-radius: 16px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; border: 1px solid 
            {{ ($finalResults['sentiment']['label'] ?? '') == 'POSITIVE' ? '#badbcc; background-color: #d1e7dd;' : 
               ( ($finalResults['sentiment']['label'] ?? '') == 'NEGATIVE' ? '#f5c2c7; background-color: #f8d7da;' : 
               '#e2e3e5; background-color: #fcfcfd;') }}">
            
            <div style="display: flex; align-items: center;">
                 <div style="width: 40px; height: 40px; border-radius: 50%; background: rgba(255,255,255,0.5); display: flex; align-items: center; justify-content: center; margin-right: 15px;">
                     <i class="fa-solid fa-heart-pulse" style="color: {{ ($finalResults['sentiment']['label'] ?? '') == 'POSITIVE' ? '#0f5132' : ( ($finalResults['sentiment']['label'] ?? '') == 'NEGATIVE' ? '#842029' : '#41464b') }}"></i>
                 </div>
                 <div>
                     <h3 style="font-size: 0.9rem; font-weight: 600; color: #555; margin: 0; text-transform: uppercase; letter-spacing: 0.5px;">Sentiment</h3>
                     <span style="font-weight: 800; font-size: 1.1rem; 
                        color: {{ ($finalResults['sentiment']['label'] ?? '') == 'POSITIVE' ? '#0f5132' : 
                                 ( ($finalResults['sentiment']['label'] ?? '') == 'NEGATIVE' ? '#842029' : '#41464b') }}">
                        {{ $finalResults['sentiment']['label'] ?? 'N/A' }}
                    </span>
                 </div>
            </div>

            <span style="font-size: 0.85rem; background: rgba(255,255,255,0.6); padding: 4px 8px; border-radius: 6px; color: #555;">
                {{ number_format(($finalResults['sentiment']['score'] ?? 0) * 100, 1) }}% Conf.
            </span>
        </div>

        <!-- Summary Section -->
        <div style="margin-bottom: 25px;">
            <h3 style="font-size: 1rem; font-weight: 600; color: #4a6fa5; margin-bottom: 10px; display: flex; align-items: center;">
                <i class="fa-solid fa-align-left" style="margin-right: 10px;"></i> Summary
            </h3>
            <div style="background-color: #f9fafb; padding: 20px; border-radius: 16px; border: 1px solid #eef2f6; font-size: 1.05rem; line-height: 1.7; color: #444;">
                {{ $finalResults['summary'] }}
            </div>
        </div>

        <!-- Entities Section -->
        <div>
            <h3 style="font-size: 1rem; font-weight: 600; color: #4a6fa5; margin-bottom: 10px; display: flex; align-items: center;">
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
<style>
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>