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
        return view('admin.reports.index', $this->prepareReportData($request, paginate: true));
    }

    public function print(Request $request): View
    {
        return view('admin.reports.print', $this->prepareReportData($request, paginate: false));
    }

    /**
     * @return array<string, mixed>
     */
    private function prepareReportData(Request $request, bool $paginate = true): array
    {
        $request->validate([
            'period' => ['sometimes', 'in:today,yesterday,7_days,this_month,custom,30_days'],
            'start_date' => ['nullable', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:start_date'],
        ], [
            'period.in' => 'Periode laporan tidak valid.',
            'start_date.date_format' => 'Tanggal mulai harus berformat tanggal (YYYY-MM-DD).',
            'end_date.date_format' => 'Tanggal akhir harus berformat tanggal (YYYY-MM-DD).',
        ]);

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

        // Query dasar seluruh transaksi dan transaksi sah (tidak di-void) dalam rentang tanggal
        $allOrdersQuery = Order::query()->whereBetween('created_at', [$startDate, $endDate]);
        $validOrdersQuery = (clone $allOrdersQuery)->notVoided();

        // Ringkasan Finansial & Transaksi (hanya transaksi sah)
        $totalOrders = (clone $validOrdersQuery)->count();
        $totalVoidedOrders = (clone $allOrdersQuery)->whereNotNull('voided_at')->count();
        $grossSales = (int) (clone $validOrdersQuery)->sum('total');
        $subtotalSales = (int) (clone $validOrdersQuery)->sum('subtotal');
        $totalDiscount = (int) (clone $validOrdersQuery)->sum('discount_amount');
        $totalTax = (int) (clone $validOrdersQuery)->sum('tax_amount');
        $cashSales = (int) (clone $validOrdersQuery)->where('payment_method', PaymentMethod::Cash)->sum('total');
        $nonCashSales = (int) (clone $validOrdersQuery)->where('payment_method', PaymentMethod::NonCash)->sum('total');
        $averageOrderValue = $totalOrders > 0 ? (int) round($grossSales / $totalOrders) : 0;
        $totalPointsEarned = (int) (clone $validOrdersQuery)->sum('points_earned');

        // Menu Paling Laris (Best Seller) hanya dari transaksi sah
        $bestSellersQuery = OrderItem::query()
            ->with('menuItem')
            ->whereHas('order', fn ($q) => $q->notVoided()->whereBetween('created_at', [$startDate, $endDate]))
            ->selectRaw('menu_item_id, max(name) as fallback_name, sum(quantity) as total_qty, sum(line_total) as total_sales')
            ->groupBy('menu_item_id')
            ->orderByDesc('total_qty');

        $bestSellers = $paginate ? $bestSellersQuery->limit(10)->get() : $bestSellersQuery->get();

        // Laporan Performa Kasir (hanya transaksi sah)
        $cashierStats = Order::query()
            ->whereNull('orders.voided_at')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->join('users', 'orders.cashier_id', '=', 'users.id')
            ->selectRaw('users.name as cashier_name, count(orders.id) as total_transactions, sum(orders.total) as total_sales')
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total_sales')
            ->get();

        // Daftar Transaksi Penjualan (Riwayat Struk, tetap menampilkan void dengan status jelas)
        if ($paginate) {
            $orders = (clone $allOrdersQuery)
                ->with(['cashier', 'member', 'items', 'voidedBy'])
                ->latest('id')
                ->paginate(15, ['*'], 'orders_page')
                ->withQueryString();

            $pointTransactions = PointTransaction::query()
                ->with(['member', 'order.cashier'])
                ->whereBetween('created_at', [$startDate, $endDate])
                ->latest('id')
                ->paginate(15)
                ->withQueryString();
        } else {
            $orders = (clone $allOrdersQuery)
                ->with(['cashier', 'member', 'items', 'voidedBy'])
                ->latest('id')
                ->get();

            $pointTransactions = PointTransaction::query()
                ->with(['member', 'order.cashier'])
                ->whereBetween('created_at', [$startDate, $endDate])
                ->latest('id')
                ->get();
        }

        return [
            'period' => $period,
            'periodLabel' => $label,
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
            'totalOrders' => $totalOrders,
            'totalVoidedOrders' => $totalVoidedOrders,
            'grossSales' => $grossSales,
            'subtotalSales' => $subtotalSales,
            'totalDiscount' => $totalDiscount,
            'totalTax' => $totalTax,
            'cashSales' => $cashSales,
            'nonCashSales' => $nonCashSales,
            'averageOrderValue' => $averageOrderValue,
            'totalPointsEarned' => $totalPointsEarned,
            'bestSellers' => $bestSellers,
            'cashierStats' => $cashierStats,
            'orders' => $orders,
            'pointTransactions' => $pointTransactions,
        ];
    }
}
