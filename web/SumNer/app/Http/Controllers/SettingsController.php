<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserPreference;

class SettingsController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        // Get existing preference or create default (blank, will be filled by fallback in view)
        $preferences = $user->preferences ?? new UserPreference([
            'abstractive_min_length' => 40,
            'abstractive_max_length' => 150,
            'abstractive_num_beams' => 4,
            'extractive_retention_ratio' => 0.3,
            'ner_threshold' => 0.50,
        ]);

        return view('settings', compact('preferences'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'abstractive_min_length' => 'required|integer|min:10|max:500',
            'abstractive_max_length' => 'required|integer|min:20|max:1000',
            'abstractive_num_beams' => 'required|integer|min:1|max:10',
            'extractive_retention_ratio' => 'required|numeric|min:0.1|max:1.0',
            'ner_threshold' => 'required|numeric|min:0.1|max:1.0',
        ]);

        // Validate logic: min < max
        if ($validated['abstractive_min_length'] >= $validated['abstractive_max_length']) {
            return back()->withErrors(['abstractive_min_length' => 'Min length must be smaller than Max length.']);
        }

        // Update or Create
        $user->preferences()->updateOrCreate(
            ['user_id' => $user->id],
            $validated
        );

        return back()->with('status', 'Settings updated successfully!');
    }
    public function destroy()
    {
        $user = Auth::user();
        
        // Reset to system defaults explicitly
        $user->preferences()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'abstractive_min_length' => 40,
                'abstractive_max_length' => 150,
                'abstractive_num_beams' => 4,
                'extractive_retention_ratio' => 0.3,
                'ner_threshold' => 0.50,
            ]
        );

        return back()->with('status', 'Settings reset to defaults!');
    }
}
