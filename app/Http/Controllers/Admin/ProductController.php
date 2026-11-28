<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Tampilkan Daftar Produk Lumpia Ubi
     */
    public function index()
    {
        $products = Product::latest()->get();
        return view('admin.products.index', compact('products'));
    }

    /**
     * Form Tambah Produk Baru
     */
    public function create()
    {
        return view('admin.products.create');
    }

    /**
     * Simpan Produk Baru ke Database
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'description'  => 'nullable|string',
            'price_medium' => 'required|numeric|min:0',
            'price_large'  => 'required|numeric|min:0',
            'stock'        => 'required|integer|min:0',
            'image'        => 'nullable|file|mimes:jpeg,png,jpg,webp,jfif|max:4096',
            'is_available' => 'nullable|boolean',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        $baseSlug = Str::slug($validated['name']);
        $slug = $baseSlug ?: 'produk';
        if (Product::where('slug', $slug)->exists()) {
            $slug .= '-' . time();
        }

        Product::create([
            'name'         => $validated['name'],
            'slug'         => $slug,
            'description'  => $validated['description'] ?? null,
            'price_medium' => $validated['price_medium'],
            'price_large'  => $validated['price_large'],
            'price'        => $validated['price_medium'],
            'stock'        => $validated['stock'],
            'image'        => $imagePath,
            'is_available' => $request->has('is_available') ? (bool) $request->is_available : true,
        ]);

        return redirect()->route('admin.products.index')->with('success', 'Produk Lumpia Ubi berhasil ditambahkan!');
    }

    /**
     * Form Edit Produk
     */
    public function edit(Product $product)
    {
        return view('admin.products.edit', compact('product'));
    }

    /**
     * Update Data Produk
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'description'  => 'nullable|string',
            'price_medium' => 'required|numeric|min:0',
            'price_large'  => 'required|numeric|min:0',
            'stock'        => 'required|integer|min:0',
            'image'        => 'nullable|file|mimes:jpeg,png,jpg,webp,jfif|max:4096',
            'is_available' => 'nullable|boolean',
        ]);

        $imagePath = $product->image;
        if ($request->hasFile('image')) {
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }
            $imagePath = $request->file('image')->store('products', 'public');
        }

        $slug = $product->slug;
        if (empty($slug) || $product->name !== $validated['name']) {
            $baseSlug = Str::slug($validated['name']);
            $candidate = $baseSlug ?: 'produk';
            $exists = Product::where('slug', $candidate)
                ->where('id', '!=', $product->id)
                ->exists();
            $slug = $exists ? ($candidate . '-' . $product->id) : $candidate;
        }

        $product->update([
            'name'         => $validated['name'],
            'slug'         => $slug,
            'description'  => $validated['description'] ?? $product->description,
            'price_medium' => $validated['price_medium'],
            'price_large'  => $validated['price_large'],
            'price'        => $validated['price_medium'],
            'stock'        => $validated['stock'],
            'image'        => $imagePath,
            'is_available' => $request->has('is_available') ? (bool) $request->is_available : ($product->is_available ?? true),
        ]);

        return redirect()->route('admin.products.index')->with('success', 'Data produk berhasil diperbarui!');
    }

    /**
     * Hapus Produk
     */
    public function destroy(Product $product)
    {
        if ($product->image && Storage::disk('public')->exists($product->image)) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil dihapus!');
    }
}