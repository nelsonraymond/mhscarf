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
use App\Services\CloudinaryService;

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

        $categories = $query->latest()->paginate(15)->withQueryString();

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

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('categories', 'public');
        } else {
            unset($data['image']);
        }

        Category::create($data);

        return redirect()->route('admin.categories.index')
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

        return view('admin.categories.edit', compact('category', 'parentCategories'));
    }

    /**
     * Update the specified category.
     */
    public function update(
    UpdateCategoryRequest $request,
    Category $category,
    CloudinaryService $cloudinary
): RedirectResponse {
    $data = $request->validated();
    $data['is_active'] = $request->boolean('is_active');

    $oldImage = $category->image;
    $oldPublicId = $category->cloudinary_public_id;
    $newUpload = null;

    if ($request->boolean('remove_image')) {
        $data['image'] = null;
        $data['cloudinary_public_id'] = null;
    } elseif ($request->hasFile('image')) {
        $newUpload = $cloudinary->upload(
            $request->file('image'),
            'mh-scarf/categories'
        );

        $data['image'] = $newUpload['url'];
        $data['cloudinary_public_id'] = $newUpload['public_id'];
    } else {
        unset($data['image'], $data['cloudinary_public_id']);
    }

    try {
        $category->update($data);
    } catch (\Throwable $exception) {
        // Bersihkan upload baru jika penyimpanan database gagal.
        if ($newUpload) {
            $cloudinary->delete($newUpload['public_id']);
        }

        throw $exception;
    }

    // Hapus gambar lama hanya setelah perubahan database berhasil.
    if (
        ($request->boolean('remove_image') || $newUpload)
        && $oldImage
    ) {
        if ($oldPublicId) {
            $cloudinary->delete($oldPublicId);
        } elseif (! str_starts_with($oldImage, 'http://')
            && ! str_starts_with($oldImage, 'https://')) {
            // Kompatibilitas untuk gambar lama yang masih tersimpan lokal.
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

        return redirect()->route('admin.categories.index')
            ->with('success', 'Kategori berhasil dihapus.');
    }
}
