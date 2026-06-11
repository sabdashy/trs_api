<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Customer extends Model
{
    use LogsActivity;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'address',
    ];

    /**
     * Activity Log config — track perubahan info customer untuk audit trail.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'phone', 'email', 'address'])
            ->logOnlyDirty()
            ->useLogName('customer')
            ->dontSubmitEmptyLogs();
    }

    /**
     * Relasi: satu customer bisa punya banyak transaksi.
     */
    public function salesTransactions()
    {
        return $this->hasMany(SalesTransaction::class);
    }
}
