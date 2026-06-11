<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionCost extends Model
{
    protected $fillable = [
        'sales_transaction_id',
        'cost_type',
        'calculation_type',
        'amount',
        'total_amount',
        'notes'
    ];

    public function salesTransaction()
    {
        return $this->belongsTo(SalesTransaction::class);
    }
}
