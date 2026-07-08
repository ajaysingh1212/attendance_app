<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProductApiController extends Controller
{
    /**
     * ✅ Get all products
     */
    public function index()
    {
        $products = Product::with(['categories', 'tags', 'companies', 'media'])
            ->get()
            ->map(fn($product) => $this->formatProduct($product));

        return response()->json([
            'success' => true,
            'message' => 'Products fetched successfully',
            'data'    => $products
        ], Response::HTTP_OK);
    }

    /**
     * ✅ Get single product by ID
     */
    public function show($id)
    {
        $product = Product::with(['categories', 'tags', 'companies', 'media'])->find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'success' => true,
            'message' => 'Product fetched successfully',
            'data'    => $this->formatProduct($product)
        ], Response::HTTP_OK);
    }

    /**
     * ✅ Format product data (for index & show)
     */
    private function formatProduct($product)
    {
        return [
            'id'        => $product->id,
            'name'      => $product->name,
            'slug'      => $product->slug,
            'sku'       => $product->sku,
            'price'     => $product->price,
            'item_code' => $product->item_code,
            'hsn_code'  => $product->hsn_code,
            'status'    => $product->status,
            'created_at'=> $product->created_at,
            'updated_at'=> $product->updated_at,

            // ✅ Only main image URL (no thumbnail/preview)
            'photo' => $product->photo ? [
                'url' => $product->photo->getUrl(),
            ] : null,

            // ✅ Categories
            'categories' => $product->categories->map(fn($c) => [
                'id'   => $c->id,
                'name' => $c->name,
            ]),

            // ✅ Tags
            'tags' => $product->tags->map(fn($t) => [
                'id'   => $t->id,
                'name' => $t->name,
            ]),

            // ✅ Company
            'company' => $product->companies->first() ? [
                'id'    => $product->companies->first()->id,
                'title' => $product->companies->first()->title
            ] : null
        ];
    }
}
