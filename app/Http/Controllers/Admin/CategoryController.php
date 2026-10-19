<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories.
     */
    public function index(Request $request): View
    {
        $query = Category::with('parent')
            ->withCount('products');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%");
        }

        if ($request->filled('status')) {
            $isActive = $request->input('status') === 'active';
            $query->where('is_active', $isActive);
        }

        $categories = $query->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new category.
     */
    public function create(): View
    {
        $parentCategories = Category::whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.categories.create', compact('parentCategories'));
    }

    /**
     * Store a newly created category.
     */
    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active', true);

        $uploadedPath = null;

        if ($request->hasFile('image')) {
    $uploadedPath = $request->file('image')
        ->store('categories', 'supabase');

    $data['image'] = $uploadedPath;
}
        try {
            Category::create($data);
        } catch (\Throwable $exception) {
            if ($uploadedPath) {
                Storage::disk('supabase')->delete($uploadedPath);
            }

            throw $exception;
        }

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Kategori berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified category.
     */
    public function edit(Category $category): View
    {
        $parentCategories = Category::whereNull('parent_id')
            ->where('id', '!=', $category->id)
            ->orderBy('name')
            ->get();

        return view(
            'admin.categories.edit',
            compact('category', 'parentCategories')
        );
    }

    /**
     * Update the specified category.
     */
    public function update(
        UpdateCategoryRequest $request,
        Category $category
    ): RedirectResponse {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');

        $oldImage = $category->image;
        $newPath = null;
        $removeImage = $request->boolean('remove_image');

       if ($removeImage) {
    $data['image'] = null;
} elseif ($request->hasFile('image')) {
    $newPath = $request->file('image')
        ->store('categories', 'supabase');

    $data['image'] = $newPath;
} else {
    unset($data['image']);
}

        try {
            $category->update($data);
        } catch (\Throwable $exception) {
            // Hapus file baru jika update database gagal.
            if ($newPath) {
                Storage::disk('supabase')->delete($newPath);
            }

            throw $exception;
        }

        
        // Hapus gambar lama hanya setelah update database berhasil.
        if (($removeImage || $newPath) && $oldImage) {
            // Jangan menghapus aset eksternal berdasarkan URL.
            if (
                ! str_starts_with($oldImage, 'http://')
                && ! str_starts_with($oldImage, 'https://')
            ) {
                // Gambar kategori baru disimpan di disk Supabase.
                Storage::disk('supabase')->delete($oldImage);

                // Bersihkan juga file lokal lama jika memang ada
                // dari penyimpanan versi sebelumnya.
                Storage::disk('public')->delete($oldImage);
            }
        }


        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Kategori berhasil diperbarui.');
    }

    /**
     * Remove the specified category.
     */
    public function destroy(Category $category): RedirectResponse
    {
        $category->delete();

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Kategori berhasil dihapus.');
    }
}
