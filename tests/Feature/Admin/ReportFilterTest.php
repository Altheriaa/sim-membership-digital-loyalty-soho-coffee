<?php

test('reports reject an unknown period with a validation error', function () {
    signInAsAdmin();

    $this->from(route('admin.reports.index'))
        ->get(route('admin.reports.index', ['period' => 'next_year']))
        ->assertSessionHasErrors(['period' => 'Periode laporan tidak valid.']);
});

test('reports only accept real calendar dates in the custom range', function () {
    signInAsAdmin();

    $this->from(route('admin.reports.index'))
        ->get(route('admin.reports.index', ['period' => 'custom', 'start_date' => '26-10-2026']))
        ->assertSessionHasErrors(['start_date' => 'Tanggal mulai harus berformat tanggal (YYYY-MM-DD).']);

    $this->from(route('admin.reports.index'))
        ->get(route('admin.reports.index', ['period' => 'custom', 'start_date' => '2026-10-20', 'end_date' => '2026-10-02']))
        ->assertSessionHasErrors(['end_date']);
});

test('reports accept a sane custom date range', function () {
    signInAsAdmin();

    $this->get(route('admin.reports.index', ['period' => 'custom', 'start_date' => now()->subDay()->toDateString(), 'end_date' => now()->toDateString()]))
        ->assertOk()
        ->assertSeeText('Rentang kustom');
});
