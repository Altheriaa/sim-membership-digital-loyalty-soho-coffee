<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PointTransaction;
use App\PaymentMethod;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $period = $request->query('period', '30_days');
        $now = Carbon::now();

        [$startDate, $endDate, $label] = match ($period) {
            'today' => [
                $now->copy()->startOfDay(),
                $now->copy()->endOfDay(),
                'Hari ini ('.$now->translatedFormat('d M Y').')',
            ],
            'yesterday' => [
                $now->copy()->subDay()->startOfDay(),
                $now->copy()->subDay()->endOfDay(),
                'Kemarin ('.$now->copy()->subDay()->translatedFormat('d M Y').')',
            ],
            '7_days' => [
                $now->copy()->subDays(6)->startOfDay(),
                $now->copy()->endOfDay(),
                '7 hari terakhir',
            ],
            'this_month' => [
                $now->copy()->startOfMonth(),
                $now->copy()->endOfMonth(),
                'Bulan ini ('.$now->translatedFormat('F Y').')',
            ],
            'custom' => [
                $request->filled('start_date') ? Carbon::parse($request->query('start_date'))->startOfDay() : $now->copy()->subDays(29)->startOfDay(),
                $request->filled('end_date') ? Carbon::parse($request->query('end_date'))->endOfDay() : $now->copy()->endOfDay(),
                'Rentang kustom',
            ],
            default => [
                $now->copy()->subDays(29)->startOfDay(),
                $now->copy()->endOfDay(),
                '30 hari terakhir',
            ],
        };

        // Query dasar transaksi dalam rentang tanggal
        $ordersQuery = Order::query()->whereBetween('created_at', [$startDate, $endDate]);

        // Ringkasan Finansial & Transaksi
        $totalOrders = (clone $ordersQuery)->count();
        $grossSales = (int) (clone $ordersQuery)->sum('total');
        $subtotalSales = (int) (clone $ordersQuery)->sum('subtotal');
        $totalTax = (int) (clone $ordersQuery)->sum('tax_amount');
        $cashSales = (int) (clone $ordersQuery)->where('payment_method', PaymentMethod::Cash)->sum('total');
        $nonCashSales = (int) (clone $ordersQuery)->where('payment_method', PaymentMethod::NonCash)->sum('total');
        $averageOrderValue = $totalOrders > 0 ? (int) round($grossSales / $totalOrders) : 0;
        $totalPointsEarned = (int) (clone $ordersQuery)->sum('points_earned');

        // Menu Paling Laris (Best Seller)
        $bestSellers = OrderItem::query()
            ->whereHas('order', fn ($q) => $q->whereBetween('created_at', [$startDate, $endDate]))
            ->selectRaw('name, sum(quantity) as total_qty, sum(line_total) as total_sales')
            ->groupBy('name')
            ->orderByDesc('total_qty')
            ->limit(10)
            ->get();

        // Laporan Performa Kasir
        $cashierStats = Order::query()
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->join('users', 'orders.cashier_id', '=', 'users.id')
            ->selectRaw('users.name as cashier_name, count(orders.id) as total_transactions, sum(orders.total) as total_sales')
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total_sales')
            ->get();

        // Log Rekap Mutasi Poin & Transaksi Audit
        $pointTransactions = PointTransaction::query()
            ->with(['member', 'order.cashier'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.reports.index', [
            'period' => $period,
            'periodLabel' => $label,
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
            'totalOrders' => $totalOrders,
            'grossSales' => $grossSales,
            'subtotalSales' => $subtotalSales,
            'totalTax' => $totalTax,
            'cashSales' => $cashSales,
            'nonCashSales' => $nonCashSales,
            'averageOrderValue' => $averageOrderValue,
            'totalPointsEarned' => $totalPointsEarned,
            'bestSellers' => $bestSellers,
            'cashierStats' => $cashierStats,
            'pointTransactions' => $pointTransactions,
        ]);
    }
}
