<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\BaseController;
use App\Models\ProductType;
use Illuminate\Http\Request;

class ProductTypeController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $productType = ProductType::all();

        return $this->successResponse(
            $productType,
            'Product types fetched successfully'
        );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $productType = ProductType::create([
            'name' => $request->name
        ]);

        return $this->successResponse(
            $productType,
            'Product type created successfully',
            201
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $productType = ProductType::find($id);
        if (!$productType) {
            return $this->errorResponse(
                'Product type not found',
                null,
                404
            );
        }

        return $this->successResponse(
            $productType,
            'Product type fetched successfully'
        );
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProductType $productType)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $productType = ProductType::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $productType->update([
            'name' => $request->name
        ]);

        return $this->successResponse(
            $productType,
            'Product type updated successfully'
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $productType = ProductType::findOrFail($id);

        $productType->delete();

        return $this->successResponse(
            null,
            'Product type deleted successfully'
        );
    }
}
