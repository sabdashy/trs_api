<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\BaseController;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends BaseController
{
    /**
     * GET /api/customers
     * Support search by name/phone/email via ?search=
     */
    public function index(Request $request)
    {
        $query = Customer::query();

        if ($request->has('search')) {
            $search = $request->search;
            // Search di name, phone, atau email (OR)
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $query->latest();
        $customers = $query->paginate(10);

        return $this->paginatedResponse(
            $customers,
            'Customers fetched successfully'
        );
    }

    /**
     * POST /api/customers
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'email' => 'required|email|max:255',
            'address' => 'required|string',
        ]);

        $customer = Customer::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'address' => $request->address,
        ]);

        return $this->successResponse(
            $customer,
            'Customer created successfully',
            201
        );
    }

    /**
     * GET /api/customers/{id}
     */
    public function show(string $id)
    {
        // withCount('salesTransactions') aman dipakai sekarang karena
        // kolom customer_id sudah ada di sales_transactions (Item 2).
        $customer = Customer::withCount('salesTransactions')->find($id);

        if (!$customer) {
            return $this->errorResponse(
                'Customer not found',
                null,
                404
            );
        }

        return $this->successResponse(
            $customer,
            'Customer fetched successfully'
        );
    }

    /**
     * GET /api/customers/dropdown
     *
     * Endpoint khusus untuk dropdown di form transaksi.
     * Return SEMUA customer (tanpa pagination), hanya field yang dibutuhkan.
     * Sorted by name untuk UX yang lebih baik di dropdown.
     */
    public function dropdown()
    {
        $customers = Customer::orderBy('name')
            ->select('id', 'name', 'phone', 'email', 'address')
            ->get();

        return $this->successResponse(
            $customers,
            'Customer dropdown fetched successfully'
        );
    }

    /**
     * PUT /api/customers/{id}
     */
    public function update(Request $request, string $id)
    {
        $customer = Customer::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'email' => 'required|email|max:255',
            'address' => 'required|string',
        ]);

        $customer->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'address' => $request->address,
        ]);

        return $this->successResponse(
            $customer,
            'Customer updated successfully'
        );
    }

    /**
     * DELETE /api/customers/{id}
     *
     * Catatan: kalau customer punya transaksi, FK constraint mungkin block delete.
     * Untuk MVP belum di-handle (assume customer baru tidak punya transaksi).
     * Future: pakai softDelete atau cascade.
     */
    public function destroy(string $id)
    {
        $customer = Customer::find($id);

        if (!$customer) {
            return $this->errorResponse(
                'Customer not found',
                null,
                404
            );
        }

        $customer->delete();

        return $this->successResponse(
            null,
            'Customer deleted successfully'
        );
    }
}
