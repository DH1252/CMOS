<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\UsefulLink;
use Illuminate\Http\Request;

class LinkController extends Controller
{
    public function index()
    {
        $links = UsefulLink::with('creator')
            ->active()
            ->orderBy('category')
            ->orderBy('sort_order')
            ->get();

        $linksByCategory = $links->groupBy('category');
        $categories = UsefulLink::getCategories();

        return $this->renderInertiaPage(
            'pages/LinkDirectoryPage',
            view: 'links.index',
            scriptId: 'svelte-link-directory-props',
            viewData: compact('links', 'linksByCategory', 'categories'),
        );
    }

    public function create()
    {
        $categories = UsefulLink::getCategories();

        return $this->renderInertiaPage(
            'pages/EntityFormPage',
            view: 'links.create',
            scriptId: 'svelte-entity-form-props',
            viewData: compact('categories'),
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'url' => 'required|url',
            'icon' => 'nullable|string|max:50',
            'category' => 'required|string|max:50',
            'sort_order' => 'nullable|integer',
        ]);

        $validated['created_by'] = auth()->id();
        $validated['icon'] = $validated['icon'] ?? 'fas fa-link';

        $link = UsefulLink::create($validated);

        ActivityLog::log('created', "Created link: {$link->title}", $link);

        return redirect()->route('links.index')
            ->with('success', 'Link berhasil ditambahkan!');
    }

    public function edit(UsefulLink $link)
    {
        $categories = UsefulLink::getCategories();

        return $this->renderInertiaPage(
            'pages/EntityFormPage',
            view: 'links.edit',
            scriptId: 'svelte-entity-form-props',
            viewData: compact('link', 'categories'),
        );
    }

    public function update(Request $request, UsefulLink $link)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'url' => 'required|url',
            'icon' => 'nullable|string|max:50',
            'category' => 'required|string|max:50',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $link->update($validated);

        ActivityLog::log('updated', "Updated link: {$link->title}", $link);

        return redirect()->route('links.index')
            ->with('success', 'Link berhasil diupdate!');
    }

    public function destroy(UsefulLink $link)
    {
        $title = $link->title;

        ActivityLog::log('deleted', "Deleted link: {$title}", $link);

        $link->delete();

        return redirect()->route('links.index')
            ->with('success', "Link {$title} berhasil dihapus!");
    }
}
