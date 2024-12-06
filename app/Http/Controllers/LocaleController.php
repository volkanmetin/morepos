<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function setLocale(Request $request)
    {
        $locale = $request->input('locale');
        $locale = in_array($locale, ['tr', 'en']) ? $locale : 'tr';
        session(['locale' => $locale]);

        if (auth()->check()) {
            $user = auth()->user();
            $user->locale = $locale;
            $user->save();
        }

        return response()->json(['success' => true]);
    }
}
