<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MLService;
use App\Models\SummarizationHistory;
use Smalot\PdfParser\Parser;
use Illuminate\Support\Facades\Auth;
use Exception;

class NewsBotController extends Controller
{
    protected $mlService;

    public function __construct(MLService $mlService)
    {
        $this->mlService = $mlService;
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
            'news_pdf' => 'nullable|file|mimes:pdf|max:10240', // Increased to 10MB
            'summary_type' => 'nullable|in:abstractive,extractive',
        ]);

        $text = $request->input('news_text') ?? '';
        $summaryType = $request->input('summary_type') ?? 'abstractive';
        $pdfPath = null;
        $pdfParsed = false;

        if ($request->hasFile('news_pdf')) {
            try {
                $pdf = $request->file('news_pdf');
                $parser = new Parser();
                $pdfObject = $parser->parseFile($pdf->getPathname());
                $pdfText = $pdfObject->getText();
                
                // Store file if user is logged in (optional, but good for history)
                if (Auth::check()) {
                    $pdfPath = $pdf->store('pdfs', 'public');
                }
                
                if (trim($pdfText) === '') {
                     return back()->withErrors(['news_pdf' => 'The uploaded PDF contains no extractable text (it might be an image).'])->withInput();
                }

                // Clean the extracted text
                $pdfText = $this->cleanText($pdfText);

                $text .= "\n\n" . $pdfText;
                $pdfParsed = true;
            } catch (Exception $e) {
                return back()->withErrors(['news_pdf' => 'Error parsing PDF: ' . $e->getMessage()])->withInput();
            }
        }

        if (empty(trim($text))) {
            return back()->withErrors(['news_text' => 'Please provide text or a valid PDF file.'])->withInput();
        }

        try {
            $results = $this->mlService->analyze($text, $summaryType);
            
            // Add summary type to results for display if needed
            $results['summary_type'] = $summaryType;

            // Save History if Logged In
            if (Auth::check()) {
                SummarizationHistory::create([
                    'user_id' => Auth::id(),
                    'input_text' => $request->input('news_text'), // Save original text input
                    'input_pdf_path' => $pdfPath,
                    'summary' => $results['summary'],
                    'entities' => $results['entities'],
                    'summary_type' => $summaryType,
                    'sentiment_label' => $results['sentiment']['label'] ?? null,
                    'sentiment_score' => $results['sentiment']['score'] ?? null,
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
                    ]
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
}
