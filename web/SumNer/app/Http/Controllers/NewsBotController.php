<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MLService;
use Smalot\PdfParser\Parser;
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
        return view('news_bot');
    }

    public function process(Request $request)
    {
        $request->validate([
            'news_text' => 'nullable|string',
            'news_pdf' => 'nullable|file|mimes:pdf|max:2048', // Max 2MB
        ]);

        $text = $request->input('news_text');

        // Handle PDF Upload
        if ($request->hasFile('news_pdf')) {
            try {
                $pdf = $request->file('news_pdf');
                $parser = new Parser();
                $pdfObject = $parser->parseFile($pdf->getPathname());
                // Append PDF text to existing text or use it as main text
                $pdfText = $pdfObject->getText();
                $text .= "\n\n" . $pdfText;
            } catch (Exception $e) {
                return back()->withErrors(['news_pdf' => 'Error parsing PDF: ' . $e->getMessage()])->withInput();
            }
        }

        if (empty(trim($text))) {
            return back()->withErrors(['news_text' => 'Please provide text or a standard PDF file.'])->withInput();
        }

        try {
            // Call ML Service
            $results = $this->mlService->analyze($text);
            
            return back()->with('results', $results)->withInput();

        } catch (Exception $e) {
            return back()->withErrors(['api_error' => 'Analysis failed: ' . $e->getMessage()])->withInput();
        }
    }
}
