<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MLService;
use App\Models\SummarizationHistory;
use Smalot\PdfParser\Parser;
use Illuminate\Support\Facades\Auth;
use Exception;

use App\Services\NewsApiService;

class NewsBotController extends Controller
{
    protected $mlService;
    protected $newsApiService;

    public function __construct(MLService $mlService, NewsApiService $newsApiService)
    {
        $this->mlService = $mlService;
        $this->newsApiService = $newsApiService;
    }

    public function index()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('news_bot');
    }

    public function process(Request $request)
    {
        $request->validate([
            'news_text' => 'nullable|string',
            'news_url' => 'nullable|url',
            'news_pdf' => 'nullable|file|mimes:pdf|max:10240', // Increased to 10MB
            'summary_type' => 'nullable|in:abstractive,extractive',
        ]);

        $inputSource = $request->input('input_source', 'text');
        $summaryType = $request->input('summary_type') ?? 'abstractive';
        $text = '';
        $url = null;
        $pdfPath = null;
        
        // 1. Process based on Input Source
        if ($inputSource === 'pdf' && $request->hasFile('news_pdf')) {
            try {
                $pdf = $request->file('news_pdf');
                $parser = new Parser();
                $pdfObject = $parser->parseFile($pdf->getPathname());
                $text = $this->cleanText($pdfObject->getText());
                
                if (Auth::check()) {
                    $pdfPath = $pdf->store('pdfs', 'public');
                }
                
                if (trim($text) === '') {
                     return back()->withErrors(['news_pdf' => 'The uploaded PDF contains no extractable text.'])->withInput();
                }
            } catch (Exception $e) {
                return back()->withErrors(['news_pdf' => 'Error parsing PDF: ' . $e->getMessage()])->withInput();
            }
        } elseif ($inputSource === 'url') {
            $url = $request->input('news_url');
            if (empty($url)) {
                 return back()->withErrors(['news_url' => 'Please provide a valid URL.'])->withInput();
            }
        } else {
            // Default: Text
            $text = $request->input('news_text');
            if (empty(trim($text))) {
                 return back()->withErrors(['news_text' => 'Please provide text content.'])->withInput();
            }
        }

        try {
            $startTime = microtime(true);
            
            // Fetch User Preferences (if logged in)
            $params = [];
            if (Auth::check()) {
                $user = Auth::user();
                if ($user->preferences) {
                    $params = $user->preferences->toArray();
                }
            }
            
            $results = $this->mlService->analyze($text, $summaryType, $params, $url);
            
            $duration = round((microtime(true) - $startTime), 2);
            
            // Add summary type and meta data to results
            $results['summary_type'] = $summaryType;
            $results['processing_time'] = $duration;

            // --- RELATED NEWS QUERY GENERATION ---
            $relatedNewsQuery = $this->generateSearchQuery(
                $results['entities'] ?? [], 
                $results['category'] ?? null, 
                $url ? ($results['title'] ?? null) : null
            );
            $results['related_news_query'] = $relatedNewsQuery;
            // -------------------------------

            // Save History if Logged In
            if (Auth::check()) {
                SummarizationHistory::create([
                    'user_id' => Auth::id(),
                    'input_text' => $url ? null : $text,
                    'input_url' => $url,
                    'input_pdf_path' => $pdfPath,
                    'summary' => $results['summary'],
                    'entities' => $results['entities'],
                    'summary_type' => $summaryType,
                    'sentiment_label' => $results['sentiment']['label'] ?? null,
                    'sentiment_score' => $results['sentiment']['score'] ?? null,
                    'category' => $results['category'] ?? null,
                    'image_url' => $results['image_url'] ?? null,
                ]);
            }
            
            return back()->with('results', $results)->withInput();

        } catch (Exception $e) {
            return back()->withErrors(['api_error' => 'Analysis failed: ' . $e->getMessage()])->withInput();
        }
    }

    public function dashboard(SummarizationHistory $history = null)
    {
        $histories = Auth::user()->histories()->latest()->get();
        
        if ($history) {
            // Ensure ownership
            if ($history->user_id !== Auth::id()) {
                abort(403);
            }
            
            // Pass results directly to view
            return view('dashboard', [
                'histories' => $histories,
                'initialText' => $history->input_text,
                'results' => [
                    'summary' => $history->summary,
                    'entities' => $history->entities ?? [],
                    'summary_type' => $history->summary_type ?? 'abstractive',
                    'sentiment' => [
                        'label' => $history->sentiment_label,
                        'score' => $history->sentiment_score
                    ],
                    'category' => $history->category,
                    'related_news_query' => $this->generateSearchQuery(
                        $history->entities ?? [], 
                        $history->category, 
                        null 
                    )
                ]
            ]);
        }


        return view('dashboard', compact('histories'));
    }

    public function destroy(SummarizationHistory $history)
    {
        // Ensure user owns this history
        if ($history->user_id !== Auth::id()) {
            abort(403);
        }

        $history->delete();

        return redirect()->route('dashboard')->with('status', 'History deleted successfully.');
    }

    /**
     * Clean extracted text to remove artifacts and normalize whitespace.
     */
    private function cleanText(string $text): string
    {
        // 1. Replace various whitespace characters (tabs, newlines, etc.) with a single space
        $text = preg_replace('/\s+/u', ' ', $text);
        
        // 2. Remove common PDF artifacts (e.g. hygiene check)
        // (Optional: can add hyphen removal if needed: "exam- ple" -> "example")
        
        return trim($text);
    }

    /**
     * Generate a search query for related news based on analysis results.
     * STRICT MODE: Only searches for Organizations (ORG).
     */
    private function generateSearchQuery($entities, $category, $title = null)
    {
        $query = "";
        
        $orgs = [];

        if (!empty($entities)) {
            foreach ($entities as $ent) {
                $word = trim($ent['word'] ?? '');
                $type = $ent['entity'] ?? '';

                // Filter out short words (noise)
                if (strlen($word) < 3) continue;
                
                // Categorize: ONLY accept ORG
                if ($type === 'ORG') {
                    $orgs[] = $word;
                }
            }
        }
        
        // Remove duplicates and re-index
        $validEntities = array_values(array_unique($orgs));
        
        if (!empty($validEntities)) {
            // Take top 2 most relevant ORGs
            $keywords = array_slice($validEntities, 0, 2);
            
            // Allow exact matching for multi-word entities by quoting them
            $processedKeywords = array_map(function($k) {
                return (strpos($k, ' ') !== false) ? '"' . $k . '"' : $k;
            }, $keywords);
            
            $query = implode(' OR ', $processedKeywords);
        }
        
        // Fallback or Augmentation (Only if no ORGs found)
        if (empty($query)) {
            if ($title) {
                // Secondary Fallback: Title Extraction
                $stopWords = ['the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with'];
                $titleWords = explode(' ', preg_replace('/[^a-zA-Z0-9 ]/', '', strtolower($title)));
                $significantTitleWords = array_diff($titleWords, $stopWords);
                $query = implode(' ', array_slice($significantTitleWords, 0, 5));
            } elseif ($category && $category !== 'Uncategorized') {
                $query = $category;
            }
        }
        
        return $query;
    }

    /**
     * AJAX Endpoint to fetch related news on demand.
     */
    public function fetchRelated(Request $request)
    {
        $request->validate([
            'query' => 'nullable|string',
            'category' => 'nullable|string',
            'title' => 'nullable|string',
        ]);

        $query = $request->input('query');
        $category = $request->input('category');
        $title = $request->input('title');

        try {
            // Strategy 1: Use specific Entity Query
            $articles = [];
            if (!empty($query)) {
                $articles = $this->newsApiService->getRelatedArticles($query, $category);
            }

            // Strategy 2: Fallback to Title (Relaxed) if no results
            if (empty($articles) && !empty($title)) {
                 // Remove special chars and stop words for a cleaner query
                 $cleanTitle = preg_replace('/[^a-zA-Z0-9 ]/', '', $title);
                 $articles = $this->newsApiService->getRelatedArticles($cleanTitle, $category);
            }

            // Strategy 3: Extreme Fallback to Category
            if (empty($articles) && !empty($category) && $category !== 'Uncategorized') {
                $articles = $this->newsApiService->getRelatedArticles($category);
            }
            
            // Return Partial ViewHTML directly for easy integration
            return view('components.related_news_cards', ['articles' => $articles])->render();

        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to fetch news'], 500);
        }
    }
}
