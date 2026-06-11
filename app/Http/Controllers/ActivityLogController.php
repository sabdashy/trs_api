<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

/**
 * ActivityLogController — endpoint baca audit trail.
 *
 * Hanya owner yang bisa akses (lihat routes/api.php — di-wrap role:owner).
 *
 * Activity model dari package Spatie:
 *   - id, log_name, description, event
 *   - subject (morph: model yang berubah)
 *   - causer (morph: user yang melakukan)
 *   - properties (JSON: old/attributes per field)
 *   - created_at
 *
 * Filter yang di-support via query params:
 *   - ?causer_id=        — filter by user
 *   - ?event=            — created/updated/deleted
 *   - ?log_name=         — user/customer/product/product_type/transaction
 *   - ?start_date=&end_date=  — range tanggal
 */
class ActivityLogController extends BaseController
{
    public function index(Request $request)
    {
        // Eager load causer supaya display user info di frontend tidak n+1 query.
        // Subject tidak di-eager load karena bisa polymorphic dengan model yang banyak —
        // frontend cuma butuh subject_type + subject_id sebagai info.
        $query = Activity::with('causer')->latest();

        // Filter by causer (user yang melakukan)
        if ($request->causer_id) {
            $query->where('causer_id', $request->causer_id);
        }

        // Filter by event (created / updated / deleted)
        if ($request->event) {
            $query->where('event', $request->event);
        }

        // Filter by log_name (kategori model — user/customer/product/dst)
        if ($request->log_name) {
            $query->where('log_name', $request->log_name);
        }

        // Filter by date range
        if ($request->start_date && $request->end_date) {
            $query->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59',
            ]);
        }

        $logs = $query->paginate(20);

        return $this->paginatedResponse(
            $logs,
            'Activity logs fetched successfully'
        );
    }
}
