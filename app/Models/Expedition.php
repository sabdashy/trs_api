<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Expedition extends Model
{
    use LogsActivity;

    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'code', 'description', 'is_active'])
            ->logOnlyDirty()
            ->useLogName('expedition')
            ->dontSubmitEmptyLogs();
    }

    /**
     * Relasi: satu expedition dipakai di banyak transaksi.
     */
    public function salesTransactions()
    {
        return $this->hasMany(SalesTransaction::class);
    }
}
