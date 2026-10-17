<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MemberRequest;
use App\Models\Member;
use App\Models\Tier;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MemberController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));

        $members = Member::query()
            ->matching($search)
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.members.index', [
            'members' => $members,
            'tiers' => Tier::query()->orderByDesc('min_spend')->get(),
            'search' => $search,
            'totalMembers' => Member::query()->count(),
            'totalPoints' => (int) Member::query()->sum('points_balance'),
            'totalSpend' => (int) Member::query()->sum('lifetime_spend'),
        ]);
    }

    public function show(Member $member): View
    {
        return view('admin.members.show', [
            'member' => $member,
            'tier' => $member->tier(),
            'orders' => $member->orders()->with('cashier')->latest('id')->paginate(10, ['*'], 'orders_page'),
            'transactions' => $member->pointTransactions()->with('order')->latest('id')->paginate(10, ['*'], 'points_page'),
        ]);
    }

    public function edit(Member $member): View
    {
        return view('admin.members.form', [
            'member' => $member,
        ]);
    }

    public function update(MemberRequest $request, Member $member): RedirectResponse
    {
        $member->update($request->validated());

        return redirect()->route('admin.members.show', $member)->with('status', "Data member {$member->name} berhasil diperbarui.");
    }

    public function adjustPoints(Request $request, Member $member): RedirectResponse
    {
        $validated = $request->validate([
            'points' => ['required', 'integer', 'not_in:0', 'min:-999999999', 'max:999999999'],
            'description' => ['required', 'string', 'max:100'],
        ], [
            'points.required' => 'Jumlah poin wajib diisi.',
            'points.not_in' => 'Jumlah poin tidak boleh 0.',
            'points.min' => 'Jumlah poin terlalu kecil.',
            'points.max' => 'Jumlah poin terlalu besar.',
            'description.required' => 'Keterangan penyesuaian wajib diisi.',
        ]);

        $points = (int) $validated['points'];

        if ($points < 0 && $member->points_balance < abs($points)) {
            return back()->withErrors(['points' => "Pengurangan poin melebihi saldo member saat ini ({$member->points_balance} poin)."]);
        }

        DB::transaction(function () use ($member, $points, $validated): void {
            $member = Member::query()->lockForUpdate()->findOrFail($member->id);

            if ($points < 0 && $member->points_balance < abs($points)) {
                throw ValidationException::withMessages([
                    'points' => "Pengurangan poin melebihi saldo member saat ini ({$member->points_balance} poin).",
                ]);
            }

            $member->points_balance += $points;
            $member->save();

            $member->pointTransactions()->create([
                'order_id' => null,
                'points' => $points,
                'balance_after' => $member->points_balance,
                'description' => 'Penyesuaian: '.$validated['description'],
            ]);
        });

        return redirect()->route('admin.members.show', $member)->with('status', 'Poin member berhasil disesuaikan.');
    }
}
