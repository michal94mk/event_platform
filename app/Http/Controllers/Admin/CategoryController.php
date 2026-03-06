<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EventCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function index(): Response
    {
        $categories = EventCategory::withCount('events')
            ->orderBy('name')
            ->get();

        return Inertia::render('admin/Categories/Index', [
            'categories' => $categories,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:event_categories,name'],
            'description' => ['nullable', 'string'],
        ]);

        $slug = Str::slug($validated['name']);
        $original = $slug;
        $i = 1;
        while (EventCategory::where('slug', $slug)->exists()) {
            $slug = $original.'-'.$i++;
        }

        EventCategory::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Kategoria została dodana.');
    }

    public function update(Request $request, EventCategory $category)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:event_categories,name,'.$category->id],
            'description' => ['nullable', 'string'],
        ]);

        $category->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        if ($category->slug !== Str::slug($validated['name'])) {
            $slug = Str::slug($validated['name']);
            $original = $slug;
            $i = 1;
            while (EventCategory::where('slug', $slug)->where('id', '!=', $category->id)->exists()) {
                $slug = $original.'-'.$i++;
            }
            $category->update(['slug' => $slug]);
        }

        return redirect()->route('admin.categories.index')
            ->with('success', 'Kategoria została zaktualizowana.');
    }

    public function destroy(EventCategory $category)
    {
        if ($category->events()->count() > 0) {
            return redirect()->route('admin.categories.index')
                ->with('error', 'Nie można usunąć kategorii, która ma przypisane wydarzenia.');
        }

        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Kategoria została usunięta.');
    }
}
