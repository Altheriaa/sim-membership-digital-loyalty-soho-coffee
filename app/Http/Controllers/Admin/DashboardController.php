<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoyaltySetting;
use App\Models\Member;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Tier;
use App\Models\User;
use App\Models\Voucher;
use App\Support\Rupiah;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();
        $startOfLastMonth = $now->copy()->subMonthNoOverflow()->startOfMonth();
        $endOfLastMonth = $now->copy()->subMonthNoOverflow()->endOfMonth();

        $thisMonthOrders = Order::query()->notVoided()->whereBetween('created_at', [$startOfMonth, $endOfMonth]);
        $lastMonthOrders = Order::query()->notVoided()->whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth]);

        $monthlyRevenue = (int) (clone $thisMonthOrders)->sum('total');
        $totalOrdersCount = (clone $thisMonthOrders)->count();

        $MembersCount = Member::query()->count();
        $totalMembersCount = Member::query()->count();
        $todayNewMembers = Member::query()->whereDate('created_at', today())->count();

        $totalMembersWithOrders = Member::query()->has('orders')->count();
        $repeatMembersCount = Member::query()->has('orders', '>=', 2)->count();
        $retentionRate = $totalMembersWithOrders > 0
            ? round(($repeatMembersCount / $totalMembersWithOrders) * 100, 1)
            : 0;

        $revenueFormatted = match (true) {
            $monthlyRevenue >= 1_000_000 => 'Rp '.number_format($monthlyRevenue / 1_000_000, 1, ',', '.').' jt',
            $monthlyRevenue > 0 => Rupiah::format($monthlyRevenue),
            default => 'Rp 0',
        };

        $todayOrdersCount = Order::query()->notVoided()->whereDate('created_at', today())->count();
        $todaySalesAmount = (int) Order::query()->notVoided()->whereDate('created_at', today())->sum('total');

        $metrics = [
            'revenue' => [
                'value' => $revenueFormatted,
                'raw' => $monthlyRevenue,
                'today' => Rupiah::format($todaySalesAmount),
            ],
            'orders' => [
                'value' => number_format($totalOrdersCount, 0, ',', '.'),
                'raw' => $totalOrdersCount,
                'today' => number_format($todayOrdersCount, 0, ',', '.').' transaksi',
            ],
            'new_members' => [
                'value' => number_format($MembersCount, 0, ',', '.'),
                'raw' => $MembersCount,
                'today' => '+'.number_format($todayNewMembers, 0, ',', '.').' member',
            ],
            'retention' => [
                'value' => ($totalMembersWithOrders > 0 ? str_replace('.', ',', (string) $retentionRate) : '0').'%',
                'detail' => $totalMembersWithOrders > 0
                    ? "{$repeatMembersCount} of {$totalMembersWithOrders} "
                    : 'Belum ada transaksi member',
            ],
        ];

        $tiers = Tier::query()->orderBy('min_spend')->get();
        $members = Member::query()->select('id', 'lifetime_spend')->get();
        $tierCounts = [];

        foreach ($tiers as $tier) {
            $tierCounts[$tier->id] = 0;
        }

        $unassignedTierCount = 0;

        foreach ($members as $member) {
            $assignedTier = Tier::forSpendFrom($tiers, $member->lifetime_spend);

            if ($assignedTier !== null) {
                $tierCounts[$assignedTier->id] = ($tierCounts[$assignedTier->id] ?? 0) + 1;
            } else {
                $unassignedTierCount++;
            }
        }

        $circumference = 339.292;
        $accumulatedOffset = 0.0;
        $tierBreakdown = [];
        $tierSegments = [];
        $palette = [
            ['stroke' => 'stroke-[#1c325c] dark:stroke-sky-400', 'dot' => 'bg-[#1c325c] dark:bg-sky-400'],
            ['stroke' => 'stroke-primary', 'dot' => 'bg-primary'],
            ['stroke' => 'stroke-slate-400 dark:stroke-stone-500', 'dot' => 'bg-slate-400 dark:bg-stone-500'],
            ['stroke' => 'stroke-emerald-600 dark:stroke-emerald-400', 'dot' => 'bg-emerald-600 dark:bg-emerald-400'],
            ['stroke' => 'stroke-amber-600 dark:stroke-amber-400', 'dot' => 'bg-amber-600 dark:bg-amber-400'],
        ];

        $orderedTiers = $tiers->sortByDesc('min_spend')->values();

        foreach ($orderedTiers as $index => $tier) {
            $count = $tierCounts[$tier->id] ?? 0;
            $pct = $totalMembersCount > 0 ? (int) round(($count / $totalMembersCount) * 100) : 0;
            $arcLength = round(($pct / 100) * $circumference, 2);
            $colors = $palette[$index % count($palette)];

            $segment = [
                'name' => $tier->name,
                'count' => $count,
                'percentage' => $pct,
                'dasharray' => "{$arcLength} ".round($circumference - $arcLength, 2),
                'dashoffset' => -$accumulatedOffset,
                'stroke' => $colors['stroke'],
                'dot' => $colors['dot'],
            ];

            $tierBreakdown[$tier->name] = $segment;
            $tierSegments[] = $segment;
            $accumulatedOffset += $arcLength;
        }

        if ($unassignedTierCount > 0) {
            $pct = $totalMembersCount > 0 ? (int) round(($unassignedTierCount / $totalMembersCount) * 100) : 0;
            $arcLength = round(($pct / 100) * $circumference, 2);
            $colors = $palette[count($orderedTiers) % count($palette)];

            $tierSegments[] = [
                'name' => 'Lainnya',
                'count' => $unassignedTierCount,
                'percentage' => $pct,
                'dasharray' => "{$arcLength} ".round($circumference - $arcLength, 2),
                'dashoffset' => -$accumulatedOffset,
                'stroke' => $colors['stroke'],
                'dot' => $colors['dot'],
            ];
        }

        $displayTotalMembers = number_format($totalMembersCount, 0, ',', '.');

        // Prepare Dynamic Revenue Trend Chart Data from real non-voided orders
        $daysInMonth = $now->daysInMonth;
        $currentDay = $now->day;
        $monthName = $now->translatedFormat('M');

        $thisMonthOrdersList = Order::query()
            ->notVoided()
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->get(['total', 'created_at']);

        $lastMonthOrdersList = Order::query()
            ->notVoided()
            ->whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])
            ->get(['total', 'created_at']);

        $dailyRevenueThisMonth = array_fill(1, $daysInMonth, 0);
        foreach ($thisMonthOrdersList as $order) {
            $day = (int) $order->created_at->format('j');
            if ($day <= $daysInMonth) {
                $dailyRevenueThisMonth[$day] += (int) $order->total;
            }
        }

        $daysInLastMonth = $startOfLastMonth->daysInMonth;
        $dailyRevenueLastMonth = array_fill(1, $daysInLastMonth, 0);
        foreach ($lastMonthOrdersList as $order) {
            $day = (int) $order->created_at->format('j');
            if ($day <= $daysInLastMonth) {
                $dailyRevenueLastMonth[$day] += (int) $order->total;
            }
        }

        $maxDaily = max(max($dailyRevenueThisMonth), max($dailyRevenueLastMonth));
        $maxCeiling = match (true) {
            $maxDaily > 5_000_000 => (int) (ceil($maxDaily / 1_000_000) * 1_000_000),
            $maxDaily > 1_000_000 => (int) (ceil($maxDaily / 500_000) * 500_000),
            $maxDaily > 500_000 => 1_000_000,
            $maxDaily > 0 => 500_000,
            default => 500_000,
        };

        $yAxisLabels = [
            ['label' => $this->formatShortRupiah($maxCeiling), 'y' => 30],
            ['label' => $this->formatShortRupiah((int) round($maxCeiling * 2 / 3)), 'y' => 90],
            ['label' => $this->formatShortRupiah((int) round($maxCeiling / 3)), 'y' => 150],
            ['label' => '0', 'y' => 210],
        ];

        $pointsThisMonth = [];
        for ($d = 1; $d <= $currentDay; $d++) {
            $amount = $dailyRevenueThisMonth[$d] ?? 0;
            $x = round(50 + (($d - 1) / ($daysInMonth - 1)) * 570, 1);
            $y = round(210 - (($amount / $maxCeiling) * 180), 1);
            $y = max(30, min(210, $y));
            $pointsThisMonth[] = [
                'day' => $d,
                'date' => "{$d} {$monthName}",
                'amount' => $amount,
                'amountFormatted' => Rupiah::format($amount),
                'x' => $x,
                'y' => $y,
            ];
        }

        $pointsLastMonth = [];
        $lastMonthDivisor = max(1, $daysInLastMonth - 1);
        for ($d = 1; $d <= $daysInLastMonth; $d++) {
            $amount = $dailyRevenueLastMonth[$d] ?? 0;
            $x = round(50 + (($d - 1) / $lastMonthDivisor) * 570, 1);
            $y = round(210 - (($amount / $maxCeiling) * 180), 1);
            $y = max(30, min(210, $y));
            $pointsLastMonth[] = [
                'day' => $d,
                'x' => $x,
                'y' => $y,
            ];
        }

        $pathThisMonth = $this->pointsToSmoothSvgPath($pointsThisMonth);
        $pathLastMonth = $this->pointsToSmoothSvgPath($pointsLastMonth);

        $lastPt = end($pointsThisMonth);
        $firstPt = reset($pointsThisMonth);
        $areaThisMonth = $pathThisMonth !== '' ? "{$pathThisMonth} L {$lastPt['x']},210 L {$firstPt['x']},210 Z" : '';

        $xAxisDates = [
            ['label' => "1 {$monthName}", 'x' => 50, 'anchor' => 'start'],
            ['label' => "7 {$monthName}", 'x' => (int) round(50 + (6 / ($daysInMonth - 1)) * 570), 'anchor' => 'middle'],
            ['label' => "14 {$monthName}", 'x' => (int) round(50 + (13 / ($daysInMonth - 1)) * 570), 'anchor' => 'middle'],
            ['label' => "21 {$monthName}", 'x' => (int) round(50 + (20 / ($daysInMonth - 1)) * 570), 'anchor' => 'middle'],
            ['label' => "{$daysInMonth} {$monthName}", 'x' => 620, 'anchor' => 'end'],
        ];

        $chartData = [
            'pathThisMonth' => $pathThisMonth,
            'areaThisMonth' => $areaThisMonth,
            'pathLastMonth' => $pathLastMonth,
            'lastPoint' => $lastPt,
            'points' => $pointsThisMonth,
            'yAxisLabels' => $yAxisLabels,
            'xAxisDates' => $xAxisDates,
            'hasOrders' => $thisMonthOrdersList->isNotEmpty(),
        ];

        return view('admin.dashboard', [
            'availableItems' => MenuItem::query()->where('is_available', true)->count(),
            'totalItems' => MenuItem::query()->count(),
            'totalCategories' => MenuCategory::query()->count(),
            'openVouchers' => Voucher::query()->openForClaims()->count(),
            'totalTiers' => Tier::query()->count(),
            'totalMembers' => $totalMembersCount,
            'todayOrders' => Order::query()->notVoided()->whereDate('created_at', today())->count(),
            'todaySales' => (int) Order::query()->notVoided()->whereDate('created_at', today())->sum('total'),
            'activeStaff' => User::query()->where('is_active', true)->count(),
            'settings' => LoyaltySetting::current(),
            'metrics' => $metrics,
            'tierBreakdown' => $tierBreakdown,
            'tierSegments' => $tierSegments,
            'displayTotalMembers' => $displayTotalMembers,
            'chartData' => $chartData,
        ]);
    }

    /**
     * Convert an array of ['x' => float, 'y' => float] points to a smooth SVG cubic bezier path string.
     *
     * @param  array<int, array{x: float|int, y: float|int}>  $points
     */
    private function pointsToSmoothSvgPath(array $points): string
    {
        $count = count($points);
        if ($count === 0) {
            return '';
        }
        if ($count === 1) {
            return "M {$points[0]['x']},{$points[0]['y']}";
        }

        $path = "M {$points[0]['x']},{$points[0]['y']}";
        for ($i = 0; $i < $count - 1; $i++) {
            $p0 = $points[max(0, $i - 1)];
            $p1 = $points[$i];
            $p2 = $points[$i + 1];
            $p3 = $points[min($count - 1, $i + 2)];

            $cp1x = round($p1['x'] + ($p2['x'] - $p0['x']) / 6, 1);
            $cp1y = round($p1['y'] + ($p2['y'] - $p0['y']) / 6, 1);
            $cp2x = round($p2['x'] - ($p3['x'] - $p1['x']) / 6, 1);
            $cp2y = round($p2['y'] - ($p3['y'] - $p1['y']) / 6, 1);

            $path .= " C {$cp1x},{$cp1y} {$cp2x},{$cp2y} {$p2['x']},{$p2['y']}";
        }

        return $path;
    }

    private function formatShortRupiah(int $amount): string
    {
        if ($amount >= 1_000_000) {
            $val = round($amount / 1_000_000, 1);

            return str_replace('.0', '', (string) $val).' jt';
        }
        if ($amount >= 1_000) {
            return round($amount / 1_000).' rb';
        }

        return (string) $amount;
    }
}
