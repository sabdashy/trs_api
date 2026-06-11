<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\BaseController;
use App\Models\Product;
use App\Models\SalesTransaction;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends BaseController
{
    public function index()
    {
        $totalTransactions = SalesTransaction::count();

        $totalProducts = Product::count();

        $totalSales = SalesTransaction::sum('subtotal');

        $totalTax = SalesTransaction::sum('tax_amount');

        $totalProfit = SalesTransaction::sum('profit');

        // WEEKLY
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        $weeklySales = SalesTransaction::whereBetween(
            'transaction_date',
            [$startOfWeek, $endOfWeek]
        )->sum('subtotal');

        $weeklyProfit = SalesTransaction::whereBetween(
            'transaction_date',
            [$startOfWeek, $endOfWeek]
        )->sum('profit');

        // MONTHLY
        $monthlySales = SalesTransaction::whereMonth(
            'transaction_date',
            Carbon::now()->month
        )->sum('subtotal');

        $monthlyProfit = SalesTransaction::whereMonth(
            'transaction_date',
            Carbon::now()->month
        )->sum('profit');

        // YEARLY
        $yearlySales = SalesTransaction::whereYear(
            'transaction_date',
            Carbon::now()->year
        )->sum('subtotal');

        $yearlyProfit = SalesTransaction::whereYear(
            'transaction_date',
            Carbon::now()->year
        )->sum('profit');

        return $this->successResponse([
                // TOTAL
                'total_transactions' => $totalTransactions,
                'total_products' => $totalProducts,
                'total_sales' => $totalSales,
                'total_tax' => $totalTax,
                'total_profit' => $totalProfit,

                // WEEKLY
                'weekly' => [
                    'sales' => $weeklySales,
                    'profit' => $weeklyProfit,
                ],

                // MONTHLY
                'monthly' => [
                    'sales' => $monthlySales,
                    'profit' => $monthlyProfit,
                ],

                // YEARLY
                'yearly' => [
                    'sales' => $yearlySales,
                    'profit' => $yearlyProfit,
                ],
            ], 'Dashboard data fetched successfully');
    }

    public function financialReport(Request $request)
    {
        $query = SalesTransaction::query();

        // Filter date range
        if ($request->start_date && $request->end_date) {

            $query->whereBetween('transaction_date', [
                $request->start_date,
                $request->end_date
            ]);
        }

        // Income
        $income = (clone $query)->sum('subtotal');

        // Purchase cost
        $purchaseCost = (clone $query)
        ->selectRaw('SUM(purchase_price * qty) as total')
        ->value('total');

        // Tax
        $tax = (clone $query)->sum('tax_amount');

        // Operational cost
        $operationalCost = (clone $query)->sum('total_cost');

        // Net profit
        $netProfit = $income - $purchaseCost - $tax - $operationalCost;

        return $this->successResponse([
                'income' => $income,
                'purchase_cost' => $purchaseCost,
                'tax' => $tax,
                'operational_cost' => $operationalCost,
                'net_profit' => $netProfit
            ], 'Financial report fetched successfully');
    }
}
