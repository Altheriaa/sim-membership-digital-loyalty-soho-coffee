<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoyaltySettingRequest;
use App\Models\LoyaltySetting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class LoyaltySettingController extends Controller
{
    public function edit(): View
    {
        return view('admin.loyalty.edit', ['settings' => LoyaltySetting::current()]);
    }

    public function update(LoyaltySettingRequest $request): RedirectResponse
    {
        LoyaltySetting::current()->update($request->validated());

        return redirect()->route('admin.loyalty.edit')->with('status', 'Aturan poin dan pajak disimpan.');
    }
}
