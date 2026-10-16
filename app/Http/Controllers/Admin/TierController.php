<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\TierRequest;
use App\Models\Tier;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class TierController extends Controller
{
    public function index(): View
    {
        return view('admin.tiers.index', [
            'tiers' => Tier::query()->ascending()->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.tiers.form', [
            'tier' => new Tier(['multiplier' => 1]),
        ]);
    }

    public function store(TierRequest $request): RedirectResponse
    {
        $tier = Tier::create($request->validated());

        return redirect()->route('admin.tiers.index')->with('status', "Tier {$tier->name} ditambahkan.");
    }

    public function edit(Tier $tier): View
    {
        return view('admin.tiers.form', ['tier' => $tier]);
    }

    public function update(TierRequest $request, Tier $tier): RedirectResponse
    {
        $tier->update($request->validated());

        return redirect()->route('admin.tiers.index')->with('status', "Tier {$tier->name} diperbarui.");
    }

    public function destroy(Tier $tier): RedirectResponse
    {
        if ($tier->isBase()) {
            return back()->withErrors([
                'tier' => 'Tier dasar tidak bisa dihapus karena setiap member harus punya tier.',
            ]);
        }

        $tier->delete();

        return redirect()->route('admin.tiers.index')->with('status', "Tier {$tier->name} dihapus.");
    }
}
