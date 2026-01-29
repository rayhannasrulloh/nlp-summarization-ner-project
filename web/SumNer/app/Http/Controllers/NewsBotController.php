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
            'news_pdf' => 'nullable|file|mimes:pdf|max:2048',
        ]);

        $text = $request->input('news_text') ?? '';
        $pdfPath = null;

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
                
                $text .= "\n\n" . $pdfText;
            } catch (Exception $e) {
                return back()->withErrors(['news_pdf' => 'Error parsing PDF: ' . $e->getMessage()])->withInput();
            }
        }

        if (empty(trim($text))) {
            return back()->withErrors(['news_text' => 'Please provide text or a standard PDF file.'])->withInput();
        }

        try {
            $results = $this->mlService->analyze($text);

            // Save History if Logged In
            if (Auth::check()) {
                SummarizationHistory::create([
                    'user_id' => Auth::id(),
                    'input_text' => $request->input('news_text'), // Save original text input
                    'input_pdf_path' => $pdfPath,
                    'summary' => $results['summary'],
                    'entities' => $results['entities'],
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
                    'entities' => $history->entities ?? [] // Handle potential null entities
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
}
