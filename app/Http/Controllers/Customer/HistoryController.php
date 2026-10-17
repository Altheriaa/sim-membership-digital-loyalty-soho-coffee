<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HistoryController extends Controller
{
    public function index(Request $request): View
    {
        /** @var Member $member */
        $member = Auth::guard('customer')->user();
        $tab = $request->query('tab', 'orders');

        $orders = $member->orders()->with(['cashier'])->latest('id')->paginate(10, ['*'], 'orders_page')->withQueryString();
        $pointTransactions = $member->pointTransactions()->with('order')->latest('id')->paginate(15, ['*'], 'points_page')->withQueryString();

        return view('customer.history.index', [
            'member' => $member,
            'tab' => $tab,
            'orders' => $orders,
            'pointTransactions' => $pointTransactions,
        ]);
    }

    public function receipt(Order $order): View
    {
        /** @var Member $member */
        $member = Auth::guard('customer')->user();

        abort_if($order->member_id !== $member->id, 403);

        $order->load(['cashier', 'member', 'items']);

        return view('customer.history.receipt', [
            'member' => $member,
            'order' => $order,
        ]);
    }
}
