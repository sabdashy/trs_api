<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\BaseController;
use App\Models\Product;
use App\Models\SalesTransaction;
use App\Models\TransactionCost;
use Illuminate\Http\Request;

class SalesTransactionController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // ACTIVE transactions only — exclude completed & cancelled.
        // Completed/cancelled di-tampilkan di endpoint /history terpisah.
        $query = SalesTransaction::with(['product', 'customer', 'expedition', 'transactionCosts'])
            ->whereNotIn('status', ['completed', 'cancelled']);

        // Search Product
        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('product', function ($q) use ($search) {
                $q->where(
                    'name',
                    'like',
                    '%' . $search . '%'
                );
            });
        }

        //Filter Date
        if (
            $request->start_date &&
            $request->end_date
        ) {
            $query->whereBetween(
                'transaction_date',
                [
                    $request->start_date,
                    $request->end_date
                ]
            );
        }

        // Sort Latest
        $query->latest();

        $transactions = $query->paginate(10);

        return $this->paginatedResponse(
            $transactions,
            'Transaction fetched successfully'
        );
    }

    /**
     * Display list of HISTORY transactions — yang sudah completed/cancelled.
     *
     * Endpoint: GET /api/sales-transactions/history
     * Mirror dari index() tapi dengan filter status terbalik.
     *
     * Catatan: ini "Filtered View" dari tabel yang sama (sales_transactions),
     * BUKAN tabel terpisah. Lihat memory project_transaction_workflow untuk
     * alasan kenapa pakai pattern ini (data integrity, FK transaction_costs).
     */
    public function history(Request $request)
    {
        $query = SalesTransaction::with(['product', 'customer', 'expedition', 'transactionCosts'])
            ->whereIn('status', ['completed', 'cancelled']);

        // Search Product (sama dengan index)
        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('product', function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%');
            });
        }

        // Filter Date
        if ($request->start_date && $request->end_date) {
            $query->whereBetween('transaction_date', [
                $request->start_date,
                $request->end_date,
            ]);
        }

        // Filter status spesifik (mis. hanya completed atau hanya cancelled)
        if ($request->has('status') &&
            in_array($request->status, ['completed', 'cancelled'])) {
            $query->where('status', $request->status);
        }

        // Sort by updated_at DESC supaya yang baru di-completed/cancelled muncul atas
        $query->latest('updated_at');

        $transactions = $query->paginate(10);

        return $this->paginatedResponse(
            $transactions,
            'Transaction history fetched successfully'
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
            'product_id' => 'required|exists:products,id',
            'customer_id' => 'required|exists:customers,id', // wajib pilih customer
            'qty' => 'required|integer|min:1',
            'tax_percentage' => 'required|numeric|min:0',
            'transaction_date' => 'required|date',
            // Ekspedisi wajib dipilih saat create (user PT TRS commit ekspedisi sejak quotation).
            // Hardcoded 3 pilihan; future iteration: master data table.
            'expedition_id' => 'required|exists:expeditions,id',

            'costs' => 'nullable|array',
            'costs.*.cost_type' => 'required|string',
            'costs.*.calculation_type' => 'required|in:fixed,per_qty',
            'costs.*.amount' => 'required|numeric|min:0',
        ]);

        $product = Product::findOrFail($request->product_id);

        // Snapshot harga
        $sellingPrice = $product->selling_price;
        $purchasePrice = $product->purchase_price;

        // Hitung subtotal
        $subtotal = $sellingPrice * $request->qty;

        // Hitung pajak
        $taxAmount = $subtotal * ($request->tax_percentage / 100);

        // Hitung modal
        $totalPurchase = $purchasePrice * $request->qty;

        // Total cost awal
        $totalCost = 0;

        // Simpan temporary costs
        $transactionCosts = [];
        if ($request->has('costs')) {
            foreach ($request->costs as $cost) {
                // Fixed cost
                if ($cost['calculation_type'] === 'fixed') {
                    $totalAmount = $cost['amount'];
                } else {
                    // Per qty
                    $totalAmount = $cost['amount'] * $request->qty;
                }
                $totalCost += $totalAmount;
                $transactionCosts[] = [
                    'cost_type' => $cost['cost_type'],
                    'calculation_type' => $cost['calculation_type'],
                    'amount' => $cost['amount'],
                    'total_amount' => $totalAmount,
                    'notes' => null
                ];
            }
        }

        // Hitung profit
        $profit = $subtotal - $totalPurchase - $taxAmount - $totalCost;

        // Generate kode transaksi
        $transactionCode = 'TRX-' . now()->format('YmdHis');

        $transaction = SalesTransaction::create([
            'transaction_code' => $transactionCode,
            'product_id' => $product->id,
            'customer_id' => $request->customer_id,
            'qty' => $request->qty,
            'selling_price' => $sellingPrice,
            'purchase_price' => $purchasePrice,
            'subtotal' => $subtotal,
            'tax_percentage' => $request->tax_percentage,
            'tax_amount' => $taxAmount,
            'total_cost' => $totalCost,
            'profit' => $profit,
            'transaction_date' => $request->transaction_date,
            'expedition_id' => $request->expedition_id,
            // tracking_number TIDAK diisi di create (baru terisi saat status → shipping)
        ]);

        foreach ($transactionCosts as $cost) {
            TransactionCost::create([
                'sales_transaction_id' => $transaction->id,
                'cost_type' => $cost['cost_type'],
                'calculation_type' => $cost['calculation_type'],
                'amount' => $cost['amount'],
                'total_amount' => $cost['total_amount'],
                'notes' => $cost['notes']
            ]);
        }

        return $this->successResponse(
            $transaction,
            'Transaction created successfully',
            201
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $transaction = SalesTransaction::with(['product', 'customer', 'expedition', 'transactionCosts'])->find($id);
        if (!$transaction) {
        return $this->errorResponse(
            'Transaction not found',
            null,
            404
        );
}

        return $this->successResponse(
            $transaction,
            'Transaction fetched successfully'
        );
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $transaction = SalesTransaction::findOrFail($id);

        // === EDIT LOCK ===
        // Transaksi cuma boleh di-edit kalau status masih 'quotation'.
        // Status lain (pre_order/processing/shipping/completed/cancelled) terkunci.
        if ($transaction->status !== 'quotation') {
            return $this->errorResponse(
                "Transaksi tidak bisa di-edit karena status sudah '{$transaction->status}'. Hanya status 'quotation' yang bisa di-edit.",
                null,
                422
            );
        }

        $request->validate([
            'product_id' => 'required|exists:products,id',
            'customer_id' => 'required|exists:customers,id',
            'qty' => 'required|integer|min:1',
            'selling_price' => 'required|numeric|min:0',
            'tax_percentage' => 'required|numeric|min:0',
            // Ekspedisi tetap required saat update (sesuai aturan create).
            'expedition_id' => 'required|exists:expeditions,id',

            // Validation untuk array costs (boleh kosong)
            'costs' => 'nullable|array',
            'costs.*.cost_type' => 'required|string',
            'costs.*.calculation_type' => 'required|in:fixed,per_qty',
            'costs.*.amount' => 'required|numeric|min:0',
        ]);

        $product = Product::findOrFail($request->product_id);

        // Subtotal
        $subtotal = $request->qty * $request->selling_price;

        // Purchase Cost
        $purchaseCost = $product->purchase_price * $request->qty;

        // Tax Amount
        $taxAmount = $subtotal * ($request->tax_percentage / 100);

        // === COSTS: hapus lama, insert baru ===
        // Strategi simple: delete-and-reinsert (semua cost diperlakukan sebagai baru).
        // Trade-off: ID cost lama hilang. Untuk MVP & audit cost belum tracked, ini aman.
        $transaction->transactionCosts()->delete();

        // Operational Cost
        $totalCost = 0;
        if ($request->has('costs')) {
            foreach ($request->costs as $cost) {
                $totalAmount = $cost['calculation_type'] === 'fixed'
                    ? $cost['amount']
                    : $cost['amount'] * $request->qty;
                $totalCost += $totalAmount;

                TransactionCost::create([
                    'sales_transaction_id' => $transaction->id,
                    'cost_type' => $cost['cost_type'],
                    'calculation_type' => $cost['calculation_type'],
                    'amount' => $cost['amount'],
                    'total_amount' => $totalAmount,
                    'notes' => $cost['notes'] ?? null,
                ]);
            }
        }

        // Profit
        $profit = $subtotal - $purchaseCost - $taxAmount - $totalCost;

        $transaction->update([
            'product_id' => $request->product_id,
            'customer_id' => $request->customer_id,
            'qty' => $request->qty,
            'selling_price' => $request->selling_price,
            'purchase_price' => $product->purchase_price,
            'subtotal' => $subtotal,
            'tax_percentage' => $request->tax_percentage,
            'tax_amount' => $taxAmount,
            'total_cost' => $totalCost,
            'profit' => $profit,
            'expedition_id' => $request->expedition_id,
        ]);

        return $this->successResponse(
            $transaction->load([
                'product',
                'transactionCosts'
            ]),
            'Transaction updated successfully'
        );
    }

    /**
     * Update STATUS transaksi (state machine transition).
     *
     * Aturan transisi yang valid (dari → ke):
     *   quotation  → pre_order, cancelled
     *   pre_order  → processing, cancelled
     *   processing → shipping, cancelled
     *   shipping   → completed, cancelled
     *   completed  → (terminal, tidak bisa pindah)
     *   cancelled  → (terminal, tidak bisa pindah)
     *
     * Endpoint: PATCH /api/sales-transactions/{id}/status
     * Body: { "status": "pre_order" }
     */
    public function updateStatus(Request $request, string $id)
    {
        $request->validate([
            'status' => 'required|in:quotation,pre_order,processing,shipping,completed,cancelled',
            // Shipping fields — required HANYA saat status target = shipping.
            // expedition_id optional override (default tetap pakai yang dari create).
            'expedition_id' => 'nullable|exists:expeditions,id',
            'tracking_number' => 'required_if:status,shipping|nullable|string|max:255',
        ]);

        $transaction = SalesTransaction::findOrFail($id);

        // Mapping transisi yang diizinkan dari setiap status.
        // Disimpan di array supaya gampang baca/edit kalau aturan bisnis berubah.
        $allowedTransitions = [
            'quotation' => ['pre_order', 'cancelled'],
            'pre_order' => ['processing', 'cancelled'],
            'processing' => ['shipping', 'cancelled'],
            'shipping' => ['completed', 'cancelled'],
            'completed' => [], // terminal — tidak bisa pindah kemana-mana
            'cancelled' => [], // terminal
        ];

        $currentStatus = $transaction->status;
        $newStatus = $request->status;

        // Tidak ada perubahan? Return early — tidak perlu update.
        if ($currentStatus === $newStatus) {
            return $this->successResponse(
                $transaction->load(['product', 'customer', 'expedition', 'transactionCosts']),
                'Status tidak berubah'
            );
        }

        // Validasi transisi
        if (!in_array($newStatus, $allowedTransitions[$currentStatus])) {
            return $this->errorResponse(
                "Tidak bisa pindah status dari '{$currentStatus}' ke '{$newStatus}'. " .
                "Transisi yang valid dari '{$currentStatus}': " .
                (empty($allowedTransitions[$currentStatus])
                    ? '(none — terminal state)'
                    : implode(', ', $allowedTransitions[$currentStatus])),
                null,
                422
            );
        }

        // Build update payload — selalu update status, conditional update expedition & tracking_number
        $updateData = ['status' => $newStatus];

        // Kalau user kirim expedition_id (override), update juga
        if ($request->filled('expedition_id')) {
            $updateData['expedition_id'] = $request->expedition_id;
        }

        // Tracking number HANYA di-set saat transition ke shipping
        if ($newStatus === 'shipping' && $request->filled('tracking_number')) {
            $updateData['tracking_number'] = $request->tracking_number;
        }

        $transaction->update($updateData);

        return $this->successResponse(
            $transaction->load(['product', 'customer', 'expedition', 'transactionCosts']),
            "Status berhasil diubah dari '{$currentStatus}' ke '{$newStatus}'"
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $transaction = SalesTransaction::findOrFail($id);

        $transaction->delete();

        return $this->successResponse(
            null,
            'Transaction deleted successfully'
        );
    }
}
