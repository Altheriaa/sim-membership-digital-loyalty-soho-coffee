<?php

namespace App\Http\Controllers;

use App\Http\Requests\Pos\QuickRegisterRequest;
use App\Http\Requests\Pos\MemberLookupRequest;
use App\Http\Requests\Pos\CheckoutRequest;
use App\Models\User;
use App\Models\MenuItem;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PosController extends Controller
{
    /**
     * Display the POS main screen.
     */
    public function index()
    {
        // Load menu items for the grid.
        $items = MenuItem::with('category')
            ->orderBy('name')
            ->get();

        return Inertia::render('Pos/Index', [
            'menuItems' => $items,
        ]);
    }

    /**
     * Quick register a new member from the cashier screen.
     */
    public function quickRegister(QuickRegisterRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'role' => 'cashier', // default role for members
            'password' => bcrypt('default'),
            'is_active' => true,
        ]);

        return response()->json(['message' => 'Member registered', 'member' => $user]);
    }

    /**
     * Lookup member by WhatsApp number (phone).
     */
    public function memberLookup(MemberLookupRequest $request)
    {
        $user = User::where('phone', $request->phone)->first();
        if (! $user) {
            return response()->json(['error' => 'Member not found'], 404);
        }

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'points' => $user->points ?? 0,
            'tier' => $user->tier?->name,
        ]);
    }

    /**
     * Process checkout, apply points, vouchers and calculate final total.
     */
    public function checkout(CheckoutRequest $request)
    {
        // Simplified: calculate subtotal from items payload.
        $subtotal = collect($request->items)->reduce(function ($carry, $item) {
            return $carry + ($item['price'] * $item['qty']);
        }, 0);

        $tax = $subtotal * ($request->tax_rate / 100);
        $total = $subtotal + $tax;

        // Apply points if requested.
        $user = Auth::user();
        if ($request->use_points) {
            // Assume 1 point = 1 rupiah (placeholder)
            $pointsValue = $user->points ?? 0;
            $total = max(0, $total - $pointsValue);
            // Deduct points (real logic would verify PIN elsewhere)
            $user->decrement('points', $pointsValue);
        }

        // Apply voucher code if provided.
        if ($request->voucher_code) {
            $voucher = Voucher::where('code', $request->voucher_code)
                ->where('status', 'active')
                ->first();
            if ($voucher) {
                // Simple discount amount, ignore type handling for brevity.
                $total = max(0, $total - $voucher->discount_amount);
                $voucher->update(['status' => 'redeemed']);
            }
        }

        // Record transaction (omitted for brevity).

        return response()->json([
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total,
        ]);
    }
}
