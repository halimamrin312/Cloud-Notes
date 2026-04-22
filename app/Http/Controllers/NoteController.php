<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class NoteController extends Controller
{
    public function index(Request $request)
    {
        $query = Note::where('user_id', auth()->id())
            ->where('is_archived', false);

        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if ($request->category) {
            $query->where('category', $request->category);
        }

        $notes = $query->orderByDesc('is_pinned')
            ->orderByDesc('updated_at')
            ->get();

        $categories = Note::where('user_id', auth()->id())
            ->whereNotNull('category')
            ->distinct('category')
            ->pluck('category');

        return Inertia::render('Notes/Index', [
            'notes' => $notes,
            'categories' => $categories,
            'filters' => $request->only(['search', 'category']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'color' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'tags' => 'nullable|array',
        ]);

        $note = Note::create([
            ...$validated,
            'user_id' => auth()->id(),
        ]);

        return back()->with('success', 'Note created successfully!');
    }

    public function update(Request $request, Note $note)
    {
        Gate::authorize('update', $note);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'color' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'tags' => 'nullable|array',
            'is_pinned' => 'boolean',
            'is_archived' => 'boolean',
        ]);

        $note->update($validated);

        return back()->with('success', 'Note updated successfully!');
    }

    public function destroy(Note $note)
    {
        Gate::authorize('delete', $note);
        $note->delete();
        return back()->with('success', 'Note deleted!');
    }

    public function archive(Note $note)
    {
        Gate::authorize('update', $note);
        $note->update(['is_archived' => !$note->is_archived]);
        return back()->with('success', $note->is_archived ? 'Note archived!' : 'Note unarchived!');
    }

    public function archived()
    {
        $notes = Note::where('user_id', auth()->id())
            ->where('is_archived', true)
            ->orderByDesc('updated_at')
            ->get();

        return Inertia::render('Notes/Archived', ['notes' => $notes]);
    }

    public function pin(Note $note)
    {
        Gate::authorize('update', $note);
        $note->update(['is_pinned' => !$note->is_pinned]);
        return back()->with('success', $note->is_pinned ? 'Note pinned!' : 'Note unpinned!');
    }
}
