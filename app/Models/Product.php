<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Product extends Model
{
    use LogsActivity;

    protected $fillable = [
        'product_type_id',
        'product_code',
        'name',
        'purchase_price',
        'selling_price',
        'description'
    ];

    /**
     * Activity Log config — track master product changes (sensitive: pricing).
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'product_type_id',
                'product_code',
                'name',
                'purchase_price',
                'selling_price',
                'description',
            ])
            ->logOnlyDirty()
            ->useLogName('product')
            ->dontSubmitEmptyLogs();
    }

    public function productType()
    {
        return $this->belongsTo(ProductType::class);
    }

    public function salesTransactions()
    {
        return $this->hasMany(SalesTransaction::class);
    }
}
