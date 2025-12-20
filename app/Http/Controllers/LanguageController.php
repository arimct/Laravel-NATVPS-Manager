<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class LanguageController extends Controller
{
    /**
     * Supported locales
     */
    protected array $supportedLocales = ['en', 'id'];

    /**
     * Switch application language
     */
    public function switch(Request $request, string $locale)
    {
        if (!in_array($locale, $this->supportedLocales)) {
            return back()->with('error', __('app.invalid_language'));
        }

        Session::put('locale', $locale);

        // Update user preference if authenticated
        if (auth()->check()) {
            auth()->user()->update(['locale' => $locale]);
        }

        return back()->with('success', __('app.language_changed'));
    }
}
