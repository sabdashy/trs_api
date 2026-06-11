<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\BaseController;
use App\Models\SalesTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends BaseController
{
    public function monthlySales(Request $request)
    {
        $query = SalesTransaction::select(
            DB::raw('MONTH(transaction_date) as month'),
            DB::raw('SUM(subtotal) as total_sales')
        );

        // Filter date range
        if ($request->start_date && $request->end_date) {
            $query->whereBetween('transaction_date', [
                $request->start_date,
                $request->end_date
            ]);
        }

        $sales = $query
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return $this->successResponse($sales, 'Monthly sales fetched successfully');
    }

    public function monthlyProfit(Request $request)
    {
        $query = SalesTransaction::select(
            DB::raw('MONTH(transaction_date) as month'),
            DB::raw('SUM(profit) as total_profit')
        );
            // Filter date range
        if ($request->start_date && $request->end_date) {

            $query->whereBetween('transaction_date', [
                $request->start_date,
                $request->end_date
            ]);
        }

        $profits = $query
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return $this->successResponse($profits, 'Monthly profit fetched successfully');
    }

    public function topProducts(Request $request)
    {
        $query = SalesTransaction::join(
                'products',
                'sales_transactions.product_id',
                '=',
                'products.id'
            )
            ->select('products.name as product_name',
                DB::raw('SUM(sales_transactions.qty) as total_qty'),
                DB::raw('SUM(sales_transactions.subtotal) as total_sales'),
                DB::raw('SUM(sales_transactions.profit) as total_profit')
            );
            // Filter date range
            if ($request->start_date && $request->end_date) {

                $query->whereBetween('transaction_date', [
                    $request->start_date,
                    $request->end_date
                ]);
            }

            $products = $query
                ->groupBy('products.name')
                ->orderByDesc('total_qty')
                ->limit(10)
                ->get();

        return $this->successResponse($products, 'Top products fetched successfully');
    }
}
