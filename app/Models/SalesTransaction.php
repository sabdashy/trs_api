<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class SalesTransaction extends Model
{
    use LogsActivity;

    /**
     * Activity Log config — track perubahan penting transaksi.
     * Skip field yang derived (subtotal, tax_amount, total_cost, profit) karena
     * itu computed otomatis dari field lain — log noisy.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'customer_id',
                'product_id',
                'qty',
                'selling_price',
                'tax_percentage',
                'status',
                'expedition_id',
                'tracking_number',
                'transaction_date',
            ])
            ->logOnlyDirty()
            ->useLogName('transaction')
            ->dontSubmitEmptyLogs();
    }

    protected $fillable = [
        'transaction_code',
        'product_id',
        'customer_id', // FK ke customers table — required di validation, nullable di DB untuk data lama
        'qty',
        'selling_price',
        'purchase_price',
        'subtotal',
        'tax_percentage',
        'tax_amount',
        'total_cost',
        'profit',
        'transaction_date',
        'status', // lifecycle: quotation → pre_order → processing → shipping → completed (atau cancelled)
        'expedition_id', // FK ke master expeditions (sebelumnya kolom string `expedition`)
        'tracking_number', // nomor resi (terisi saat status → shipping)
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function expedition()
    {
        return $this->belongsTo(Expedition::class);
    }

    public function transactionCosts()
    {
        return $this->hasMany(TransactionCost::class);
    }
}
