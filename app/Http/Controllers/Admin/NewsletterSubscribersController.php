<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Newsletter;
use Illuminate\Http\Request;

class NewsletterSubscribersController extends Controller
{
    public function index(Request $request)
    {
        $query = Newsletter::query()->latest();

        if ($search = trim((string) $request->get('search', ''))) {
            $query->where('email', 'like', '%' . $search . '%');
        }

        if ($language = $request->get('language')) {
            $query->where('language', $language);
        }

        $subscribers = $query->get();
        $totalCount = Newsletter::count();
        $languages = Newsletter::query()
            ->whereNotNull('language')
            ->distinct()
            ->orderBy('language')
            ->pluck('language');

        return view('admin.pages.newsletter-subscribers.index', compact(
            'subscribers',
            'totalCount',
            'languages'
        ));
    }

    public function destroy(Newsletter $newsletter)
    {
        $email = $newsletter->email;
        $newsletter->delete();

        return redirect()
            ->route('admin.newsletter-subscribers.index')
            ->with('success', 'Removed ' . e($email) . ' from the newsletter list.');
    }
}
