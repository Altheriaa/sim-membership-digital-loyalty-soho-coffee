<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\VoucherRequest;
use App\Models\MenuItem;
use App\Models\Voucher;
use App\VoucherType;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class VoucherController extends Controller
{
    public function index(): View
    {
        return view('admin.vouchers.index', [
            'vouchers' => Voucher::query()
                ->with('menuItem:id,name')
                ->latest()
                ->latest('id')
                ->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('admin.vouchers.form', [
            'voucher' => new Voucher([
                'type' => VoucherType::DiscountAmount,
                'valid_days' => 30,
                'is_active' => true,
            ]),
            'menuItems' => MenuItem::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(VoucherRequest $request): RedirectResponse
    {
        $voucher = Voucher::create($request->voucherData());

        return redirect()->route('admin.vouchers.index')->with('status', "Voucer {$voucher->name} dibuat.");
    }

    public function edit(Voucher $voucher): View
    {
        return view('admin.vouchers.form', [
            'voucher' => $voucher,
            'menuItems' => MenuItem::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(VoucherRequest $request, Voucher $voucher): RedirectResponse
    {
        $voucher->update($request->voucherData());

        return redirect()->route('admin.vouchers.index')->with('status', "Voucer {$voucher->name} diperbarui.");
    }

    public function destroy(Voucher $voucher): RedirectResponse
    {
        $voucher->delete();

        return redirect()->route('admin.vouchers.index')->with('status', "Voucer {$voucher->name} dihapus.");
    }
}
