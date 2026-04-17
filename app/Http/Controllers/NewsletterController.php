<?php

namespace App\Http\Controllers;

use App\Newsletter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class NewsletterController extends Controller
{
    /**
     * Display a listing of the newsletters.
     */
    public function index()
    {
        $newsletters = Newsletter::latest()->paginate(10); // Paginate results
        return view('Newsletter.index', compact('newsletters'));
    }
    public function showNewsletters()
    {
        $newsletters = Newsletter::where('is_published', false)->latest()->get();
        return view('website.News', compact('newsletters'));
    }
    public function show($id) {
        $newsletter = Newsletter::findOrFail($id);
        return view('website.Show', compact('newsletter'));
    }
    public function show1($id) {
        $newsletter = Newsletter::findOrFail($id);
        return view('website.show');
    }
    
    /**
     * Show the form for creating a new newsletter.
     */
    public function create()
    {
        return view('Newsletter.create');
    }

    /**
     * Store a newly created newsletter.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'is_published' => 'boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('newsletters', 'public');
        }

        Newsletter::create([
            'title' => $request->title,
            'content' => $request->content,
            'is_published' => $request->has('is_published') ? 1 : 0,
            'image_path' => $imagePath,
        ]);

        if ($request->input('action') === 'post_next') {
            return redirect()->route('newsletters.create')->with('success', 'Newsletter posted! Create your next one.');
        }

        return redirect()->route('newsletters.index')->with('success', 'Newsletter created successfully.');
    }

    /**
     * Show the form for editing the specified newsletter.
     */
    public function edit(Newsletter $newsletter)
    {
        return view('Newsletter.edit', compact('newsletter'));
    }

    /**
     * Update the specified newsletter.
     */
    public function update(Request $request, Newsletter $newsletter)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'is_published' => 'boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($newsletter->image_path) {
                Storage::disk('public')->delete($newsletter->image_path);
            }

            $imagePath = $request->file('image')->store('newsletters', 'public');
            $newsletter->image_path = $imagePath;
        }

        $newsletter->update([
            'title' => $request->title,
            'content' => $request->content,
            'is_published' => $request->has('is_published') ? 1 : 0,
        ]);

        return redirect()->route('newsletters.index')->with('success', 'Newsletter updated successfully.');
    }

    /**
     * Remove the specified newsletter.
     */
    public function destroy(Newsletter $newsletter)
    {
        if ($newsletter->image_path) {
            Storage::disk('public')->delete($newsletter->image_path);
        }

        $newsletter->delete();
        return redirect()->route('newsletters.index')->with('success', 'Newsletter deleted successfully.');
    }
}
