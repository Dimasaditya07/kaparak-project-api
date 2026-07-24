<?php

namespace App\Http\Controllers\Api;

use App\Helpers\UploadHelper;
use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\PackageItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PackageController extends Controller
{
    /**
     * GET ALL PACKAGE
     */
    public function index()
    {
        $packages = Package::with('packageItems.product')
            ->latest()
            ->get();

        return response()->json([
            'data' => $packages
        ]);
    }

    /**
     * STORE PACKAGE
     */
    public function store(Request $request)
    {
        $request->validate([

            'code' => 'required|unique:packages,code',

            'name' => 'required|max:255',

            'description' => 'nullable',

            'package_price' => 'required|numeric|min:0',

            'status' => 'required|in:available,inactive',

            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',

            'products' => 'required|array|min:1',

            'products.*.product_id' => 'required|exists:products,id',

            'products.*.quantity' => 'required|integer|min:1',

        ]);
        $productIds = collect($request->products)->pluck('product_id');

        if ($productIds->duplicates()->isNotEmpty()) {
            return response()->json([
                'message' => 'Produk tidak boleh dipilih lebih dari satu kali.'
            ], 422);
        }

        DB::beginTransaction();

        try {

            /*
            |--------------------------------------------------------------------------
            | Upload Image
            |--------------------------------------------------------------------------
            */

            $image = null;

            if ($request->hasFile('image')) {

                $file = $request->file('image');

                $path = 'packages';

                $fileName = UploadHelper::uploadFile($file, $path);

                $image = $path . '/' . $fileName;
            }

            /*
            |--------------------------------------------------------------------------
            | Create Package
            |--------------------------------------------------------------------------
            */

            $package = Package::create([

                'code' => $request->code,

                'name' => $request->name,

                'description' => $request->description,

                'image' => $image,

                'normal_price' => 0,

                'package_price' => $request->package_price,

                'discount_amount' => 0,

                'status' => $request->status,

            ]);

            /*
            |--------------------------------------------------------------------------
            | Create Package Item
            |--------------------------------------------------------------------------
            */

            $normalPrice = 0;

            foreach ($request->products as $item) {

                $product = Product::findOrFail($item['product_id']);

                if ($product->stock < $item['quantity']) {

                    throw new \Exception(
                        "Stok {$product->name} hanya tersisa {$product->stock}."
                    );
                }

                $normalPrice +=
                    $product->price * $item['quantity'];

                PackageItem::create([
                    'package_id' => $package->id,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                ]);
            }
            if ($request->package_price > $normalPrice) {

                throw new \Exception(
                    'Harga paket tidak boleh lebih besar dari harga normal.'
                );
            }
            $package->update([
                'normal_price' => $normalPrice,
                'discount_amount' => $normalPrice - $request->package_price,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Calculate Discount
            |--------------------------------------------------------------------------
            */

            $discountAmount = max(
                0,
                $normalPrice - $request->package_price
            );

            $package->update([

                'normal_price' => $normalPrice,

                'discount_amount' => $discountAmount,

            ]);

            DB::commit();

            return response()->json([

                'message' => 'Package berhasil dibuat',

                'data' => $package->load('packageItems.product')

            ], 201);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([

                'message' => $e->getMessage()

            ], 500);
        }
    }
    /**
     * SHOW PACKAGE
     */
    public function show(string $id)
    {
        $package = Package::with('packageItems.product')
            ->findOrFail($id);

        return response()->json([
            'data' => $package
        ]);
    }

    /**
     * UPDATE PACKAGE
     */
    public function update(Request $request, string $id)
    {
        $package = Package::findOrFail($id);

        $request->validate([

            'code' => 'required|unique:packages,code,' . $package->id,

            'name' => 'required|max:255',

            'description' => 'nullable',

            'package_price' => 'required|numeric|min:0',

            'status' => 'required|in:available,inactive',

            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',

            'products' => 'required|array|min:1',

            'products.*.product_id' => 'required|exists:products,id',

            'products.*.quantity' => 'required|integer|min:1',

        ]);
        $productIds = collect($request->products)->pluck('product_id');

        if ($productIds->duplicates()->isNotEmpty()) {
            return response()->json([
                'message' => 'Produk tidak boleh dipilih lebih dari satu kali.'
            ], 422);
        }

        DB::beginTransaction();

        try {

            /*
            |--------------------------------------------------------------------------
            | Upload Image
            |--------------------------------------------------------------------------
            */

            $image = $package->image;

            if ($request->hasFile('image')) {

                // hapus gambar lama
                if ($package->image) {
                    UploadHelper::deleteFile($package->image);
                }

                $file = $request->file('image');

                $path = 'packages';

                $fileName = UploadHelper::uploadFile($file, $path);

                $image = $path . '/' . $fileName;
            }

            /*
            |--------------------------------------------------------------------------
            | Update Package
            |--------------------------------------------------------------------------
            */

            $package->update([

                'code' => $request->code,

                'name' => $request->name,

                'description' => $request->description,

                'image' => $image,

                'package_price' => $request->package_price,

                'status' => $request->status,

            ]);

            /*
            |--------------------------------------------------------------------------
            | Delete Old Items
            |--------------------------------------------------------------------------
            */

            PackageItem::where(
                'package_id',
                $package->id
            )->delete();

            /*
            |--------------------------------------------------------------------------
            | Insert New Items
            |--------------------------------------------------------------------------
            */

            $normalPrice = 0;

            foreach ($request->products as $item) {

                $product = Product::findOrFail($item['product_id']);

                if ($product->stock < $item['quantity']) {

                    throw new \Exception(
                        "Stok {$product->name} hanya tersisa {$product->stock}."
                    );
                }

                $normalPrice +=
                    $product->price * $item['quantity'];

                PackageItem::create([
                    'package_id' => $package->id,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                ]);
            }

            if ($request->package_price > $normalPrice) {

                throw new \Exception(
                    'Harga paket tidak boleh lebih besar dari harga normal.'
                );
            }
            $package->update([
                'normal_price' => $normalPrice,
                'discount_amount' => $normalPrice - $request->package_price,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Update Price
            |--------------------------------------------------------------------------
            */

            $discountAmount = max(
                0,
                $normalPrice - $request->package_price
            );

            $package->update([

                'normal_price' => $normalPrice,

                'discount_amount' => $discountAmount,

            ]);

            DB::commit();

            return response()->json([

                'message' => 'Package berhasil diupdate',

                'data' => $package->load('packageItems.product')

            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([

                'message' => $e->getMessage()

            ], 500);
        }
    }
    /**
     * DELETE PACKAGE
     */
    public function destroy(string $id)
    {
        $package = Package::findOrFail($id);

        DB::beginTransaction();

        try {

            /*
            |--------------------------------------------------------------------------
            | Delete Image Cloudflare
            |--------------------------------------------------------------------------
            */

            if ($package->image) {
                UploadHelper::deleteFile($package->image);
            }

            /*
            |--------------------------------------------------------------------------
            | Delete Package Items
            |--------------------------------------------------------------------------
            */

            PackageItem::where(
                'package_id',
                $package->id
            )->delete();

            /*
            |--------------------------------------------------------------------------
            | Delete Package
            |--------------------------------------------------------------------------
            */

            $package->delete();

            DB::commit();

            return response()->json([
                'message' => 'Package berhasil dihapus'
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
