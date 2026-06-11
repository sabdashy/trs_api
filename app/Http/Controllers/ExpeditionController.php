<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\BaseController;
use App\Models\Expedition;
use Illuminate\Http\Request;

class ExpeditionController extends BaseController
{
    /**
     * GET /api/expeditions
     * Full list dengan optional search & pagination.
     */
    public function index(Request $request)
    {
        $query = Expedition::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $query->orderBy('name');

        $expeditions = $query->paginate(10);

        return $this->paginatedResponse(
            $expeditions,
            'Expeditions fetched successfully'
        );
    }

    /**
     * GET /api/expeditions/dropdown
     *
     * Untuk dropdown di form transaksi — hanya yang is_active=true,
     * tanpa pagination.
     */
    public function dropdown()
    {
        $expeditions = Expedition::where('is_active', true)
            ->orderBy('name')
            ->select('id', 'name', 'code')
            ->get();

        return $this->successResponse(
            $expeditions,
            'Active expeditions fetched successfully'
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:expeditions,name',
            'code' => 'nullable|string|max:50|unique:expeditions,code',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $expedition = Expedition::create([
            'name' => $request->name,
            'code' => $request->code,
            'description' => $request->description,
            'is_active' => $request->is_active ?? true,
        ]);

        return $this->successResponse(
            $expedition,
            'Expedition created successfully',
            201
        );
    }

    public function show(string $id)
    {
        $expedition = Expedition::withCount('salesTransactions')->find($id);

        if (!$expedition) {
            return $this->errorResponse(
                'Expedition not found',
                null,
                404
            );
        }

        return $this->successResponse(
            $expedition,
            'Expedition fetched successfully'
        );
    }

    public function update(Request $request, string $id)
    {
        $expedition = Expedition::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:expeditions,name,' . $id,
            'code' => 'nullable|string|max:50|unique:expeditions,code,' . $id,
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $expedition->update([
            'name' => $request->name,
            'code' => $request->code,
            'description' => $request->description,
            'is_active' => $request->is_active ?? $expedition->is_active,
        ]);

        return $this->successResponse(
            $expedition,
            'Expedition updated successfully'
        );
    }

    /**
     * DELETE: backend pakai restrictOnDelete FK — kalau ada transaksi
     * yang refer, akan throw exception. Tangkap & kasih error message yang clear.
     */
    public function destroy(string $id)
    {
        $expedition = Expedition::find($id);

        if (!$expedition) {
            return $this->errorResponse(
                'Expedition not found',
                null,
                404
            );
        }

        try {
            $expedition->delete();
        } catch (\Illuminate\Database\QueryException $e) {
            // Likely FK constraint violation (transaksi masih refer)
            return $this->errorResponse(
                'Tidak bisa hapus expedition yang masih dipakai di transaksi. ' .
                'Nonaktifkan saja (set is_active = false) supaya tidak muncul di dropdown.',
                null,
                422
            );
        }

        return $this->successResponse(
            null,
            'Expedition deleted successfully'
        );
    }
}
