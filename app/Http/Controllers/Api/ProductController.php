<?php

namespace App\Http\Controllers\Api;

use App\Helpers\UploadHelper;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * GET ALL PRODUCTS
     */
    public function index()
    {
        $products = Product::with('category')
            ->latest()
            ->get();

        return response()->json([
            'data' => $products
        ]);
    }

    /**
     * STORE PRODUCT
     */
    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',

            'name' => 'required|max:100',

            'code' => 'required|unique:products,code',

            'description' => 'nullable',

            'stock' => 'required|integer|min:0',

            'price' => 'required|numeric|min:0',

            'status' => 'required|in:available,unavailable',

            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',

            'status' => 'required'
        ]);

        $image = null;

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $path = 'products';
            $fileName = UploadHelper::uploadFile($file, $path);
            $image = $path . '/' . $fileName;
        }

        // CREATE PRODUCT
        $product = Product::create([
            'category_id' => $request->category_id,

            'name' => $request->name,

            'slug' => Str::slug($request->name),

            'code' => $request->code,

            'description' => $request->description,

            'stock' => $request->stock,

            'price' => $request->price,

            'image' => $image,

            'status' => $request->status,
        ]);

        return response()->json([
            'message' => 'Product berhasil dibuat',
            'data' => $product
        ], 201);
    }

    /**
     * SHOW PRODUCT
     */
    public function show(string $id)
    {
        $product = Product::with('category')
            ->findOrFail($id);

        return response()->json([
            'data' => $product
        ]);
    }

    /**
     * UPDATE PRODUCT
     */
    public function update(Request $request, string $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'category_id' => 'required|exists:categories,id',

            'name' => 'required|max:100',

            'code' => 'required|unique:products,code,' . $product->id,

            'description' => 'nullable',

            'stock' => 'required|integer|min:0',

            'price' => 'required|numeric|min:0',

            'status' => 'required|in:available,unavailable',

            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $image = $product->image;

        // UPDATE IMAGE
        if ($request->hasFile('image')) {
            // DELETE OLD IMAGE
            if ($product->image) {
                UploadHelper::deleteFile($product->image);
            }

            // STORE NEW IMAGE
            $file = $request->file('image');
            $path = 'products';
            $fileName = UploadHelper::uploadFile($file, $path);
            $image = $path . '/' . $fileName;
        }

        // UPDATE PRODUCT
        $product->update([
            'category_id' => $request->category_id,

            'name' => $request->name,

            'slug' => Str::slug($request->name),

            'code' => $request->code,

            'description' => $request->description,

            'stock' => $request->stock,

            'price' => $request->price,

            'image' => $image,

            'status' => $request->status,
        ]);

        return response()->json([
            'message' => 'Product berhasil diupdate',
            'data' => $product
        ]);
    }

    /**
     * DELETE PRODUCT
     */
    public function destroy(string $id)
    {
        $product = Product::findOrFail($id);

        if ($product->image) {
            UploadHelper::deleteFile($product->image);
        }

        $product->delete();

        return response()->json([
            'message' => 'Product berhasil dihapus'
        ]);
    }
}
