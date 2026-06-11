<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\BaseController;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::with('productType')->get();

        return $this->successResponse(
            $products,
            'Products fetched successfully'
        );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_type_id' => 'required|exists:product_types,id',
            'product_code' => 'required|unique:products,product_code',
            'name' => 'required|string|max:255',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'description' => 'nullable|string'
        ]);

        $product = Product::create([
            'product_type_id' => $request->product_type_id,
            'product_code' => $request->product_code,
            'name' => $request->name,
            'purchase_price' => $request->purchase_price,
            'selling_price' => $request->selling_price,
            'description' => $request->description
        ]);

        return $this->successResponse(
            $product,
            'Product created successfully',
            201
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $product = Product::with('productType')->find($id);
        if (!$product) {
            return $this->errorResponse(
                'Product not found',
                null,
                404
            );
        }

        return $this->successResponse(
            $product,
            'Product fetched successfully'
        );
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'product_type_id' => 'required|exists:product_types,id',
            'product_code' => 'required|unique:products,product_code,' . $id,
            'name' => 'required|string|max:255',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'description' => 'nullable|string'
        ]);

        $product->update([
            'product_type_id' => $request->product_type_id,
            'product_code' => $request->product_code,
            'name' => $request->name,
            'purchase_price' => $request->purchase_price,
            'selling_price' => $request->selling_price,
            'description' => $request->description
        ]);

        return $this->successResponse(
            $product,
            'Product updated successfully'
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = Product::find($id);
        if (!$product) {
            return $this->errorResponse(
                'Product not found',
                null,
                404
            );
        }

        $product->delete();
        return $this->successResponse(
            null,
            'Product deleted successfully'
        );
    }
}
