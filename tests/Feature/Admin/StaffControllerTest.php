<?php

use App\Models\User;
use App\Role;
use Illuminate\Support\Facades\Hash;

test('staff index lists staff by name with email, role and status', function () {
    signInAsAdmin();
    User::factory()->cashier()->create(['name' => 'Budi Kasir', 'email' => 'budi@example.com']);
    User::factory()->cashier()->inactive()->create(['name' => 'Cici Kasir', 'email' => 'cici@example.com']);

    $response = $this->get(route('admin.staff.index'));

    $response->assertOk()
        ->assertSeeTextInOrder(['Admin Utama', 'utama@example.com', 'Admin', 'Aktif'])
        ->assertSeeTextInOrder(['Budi Kasir', 'budi@example.com', 'Kasir', 'Aktif'])
        ->assertSeeTextInOrder(['Cici Kasir', 'cici@example.com', 'Kasir', 'Nonaktif']);
});

test('staff index shows how many accounts hold each role in the filter tabs', function () {
    signInAsAdmin();
    User::factory()->owner()->create();
    User::factory()->cashier()->count(3)->create();

    $response = $this->get(route('admin.staff.index'));

    $response->assertOk()
        ->assertSeeTextInOrder(['Semua peran', '5', 'Owner', '1', 'Admin', '1', 'Kasir', '3']);
});

test('staff index only lists accounts of the requested role', function () {
    signInAsAdmin();
    User::factory()->cashier()->create(['name' => 'Budi Kasir']);

    $response = $this->get(route('admin.staff.index', ['role' => 'cashier']));

    $response->assertOk()
        ->assertSeeText('Budi Kasir')
        ->assertDontSeeText('Admin Utama');
});

test('staff index matches the search term against names and emails', function () {
    signInAsAdmin();
    User::factory()->cashier()->create(['name' => 'Maya Lindqvist', 'email' => 'first@example.com']);
    User::factory()->cashier()->create(['name' => 'Aldo Prasetyo', 'email' => 'maya.backup@example.com']);
    User::factory()->cashier()->create(['name' => 'Olivia Hartono', 'email' => 'olivia@example.com']);

    $response = $this->get(route('admin.staff.index', ['search' => 'MAYA']));

    $response->assertOk()
        ->assertSeeText('Maya Lindqvist')
        ->assertSeeText('Aldo Prasetyo')
        ->assertDontSeeText('Olivia Hartono');
});

test('staff index treats percent signs in the search term literally', function () {
    signInAsAdmin();
    User::factory()->cashier()->create(['name' => '100% Rajin']);
    User::factory()->cashier()->create(['name' => 'Olivia Hartono']);

    $response = $this->get(route('admin.staff.index', ['search' => '%']));

    $response->assertOk()
        ->assertSeeText('100% Rajin')
        ->assertDontSeeText('Olivia Hartono');
});

test('staff index redirects an unknown role to the unfiltered list with an error', function () {
    signInAsAdmin();

    $this->get(route('admin.staff.index', ['role' => 'superuser']))
        ->assertRedirect(route('admin.staff.index'))
        ->assertSessionHasErrors('role');

    $this->followingRedirects()
        ->get(route('admin.staff.index', ['role' => 'superuser']))
        ->assertOk()
        ->assertSeeText('Ada yang perlu diperbaiki')
        ->assertSeeText('Admin Utama');
});

test('staff index names the role when nobody holds it', function () {
    signInAsAdmin();

    $response = $this->get(route('admin.staff.index', ['role' => 'owner']));

    $response->assertOk()
        ->assertSeeText('Belum ada staf dengan peran Owner')
        ->assertSeeText('Tampilkan semua peran');
});

test('staff index names the search term when nobody matches it', function () {
    signInAsAdmin();

    $response = $this->get(route('admin.staff.index', ['search' => 'zzz']));

    $response->assertOk()
        ->assertSeeText('Tidak ada staf yang cocok dengan “zzz”')
        ->assertSeeText('Hapus pencarian');
});

test('staff index paginates fifteen accounts per page and keeps the filter in the page links', function () {
    signInAsAdmin();
    User::factory()->cashier()->count(16)->sequence(fn ($sequence) => [
        'name' => sprintf('Petugas %02d', $sequence->index + 1),
    ])->create();

    $firstPage = $this->get(route('admin.staff.index', ['role' => 'cashier']));

    $firstPage->assertOk()
        ->assertSeeText('Petugas 01')
        ->assertSeeText('Petugas 15')
        ->assertDontSeeText('Petugas 16')
        ->assertSeeText('Menampilkan 1 sampai 15 dari 16')
        ->assertSee(e(route('admin.staff.index', ['role' => 'cashier', 'page' => 2])), false);

    $this->get(route('admin.staff.index', ['role' => 'cashier', 'page' => 2]))
        ->assertOk()
        ->assertSeeText('Petugas 16')
        ->assertDontSeeText('Petugas 01');
});

test('staff index escapes names and the search term it repeats back', function () {
    signInAsAdmin();
    User::factory()->cashier()->create(['name' => '<script>alert("nama")</script>']);

    $this->get(route('admin.staff.index'))
        ->assertOk()
        ->assertDontSee('<script>alert("nama")</script>', false)
        ->assertSee(e('<script>alert("nama")</script>'), false);

    $this->get(route('admin.staff.index', ['search' => '<b>tidak ada</b>']))
        ->assertOk()
        ->assertDontSee('<b>tidak ada</b>', false)
        ->assertSee(e('<b>tidak ada</b>'), false);
});

test('staff index does not offer to edit an owner account', function () {
    signInAsAdmin();
    $owner = User::factory()->owner()->create(['name' => 'Pak Owner']);

    $response = $this->get(route('admin.staff.index'));

    $response->assertOk()
        ->assertSeeText('Dikelola di luar panel')
        ->assertDontSee(route('admin.staff.edit', $owner), false);
});

test('admin creates a cashier account with a hashed password', function () {
    signInAsAdmin();

    $response = $this->post(route('admin.staff.store'), [
        'name' => 'Rina Kasir',
        'email' => 'rina@example.com',
        'role' => 'cashier',
        'is_active' => '1',
        'password' => 'rahasia-123',
    ]);

    $response->assertRedirect(route('admin.staff.index'))
        ->assertSessionHas('status', 'Akun Rina Kasir dibuat.');

    $staff = User::query()->where('email', 'rina@example.com')->firstOrFail();

    expect($staff->name)->toBe('Rina Kasir')
        ->and($staff->role)->toBe(Role::Cashier)
        ->and($staff->is_active)->toBeTrue()
        ->and(Hash::check('rahasia-123', $staff->password))->toBeTrue();
});

test('admin can create an account that starts switched off', function () {
    signInAsAdmin();

    $this->post(route('admin.staff.store'), [
        'name' => 'Sari Cadangan',
        'email' => 'sari@example.com',
        'role' => 'cashier',
        'is_active' => '0',
        'password' => 'rahasia-123',
    ])->assertRedirect(route('admin.staff.index'));

    expect(User::query()->where('email', 'sari@example.com')->firstOrFail()->is_active)->toBeFalse();
});

test('creating staff requires a name, email, role and password', function () {
    signInAsAdmin();

    $response = $this->from(route('admin.staff.create'))->post(route('admin.staff.store'), []);

    $response->assertRedirect(route('admin.staff.create'))
        ->assertSessionHasErrors([
            'name' => 'Nama wajib diisi.',
            'email' => 'Email wajib diisi.',
            'role' => 'Peran wajib diisi.',
            'password' => 'Kata sandi wajib diisi.',
        ]);
    expect(User::query()->count())->toBe(1);
});

test('creating staff rejects an email that is already in use', function () {
    signInAsAdmin();

    $response = $this->post(route('admin.staff.store'), [
        'name' => 'Kembaran',
        'email' => 'utama@example.com',
        'role' => 'cashier',
        'password' => 'rahasia-123',
    ]);

    $response->assertSessionHasErrors(['email' => 'Email sudah dipakai.']);
});

test('creating staff rejects a password shorter than eight characters', function () {
    signInAsAdmin();

    $response = $this->post(route('admin.staff.store'), [
        'name' => 'Rina Kasir',
        'email' => 'rina@example.com',
        'role' => 'cashier',
        'password' => 'pendek',
    ]);

    $response->assertSessionHasErrors(['password' => 'Kata sandi minimal 8 karakter.']);
});

test('admins cannot hand out the owner role', function () {
    signInAsAdmin();

    $response = $this->post(route('admin.staff.store'), [
        'name' => 'Calon Owner',
        'email' => 'calon@example.com',
        'role' => 'owner',
        'password' => 'rahasia-123',
    ]);

    $response->assertSessionHasErrors(['role' => 'Peran yang dipilih tidak valid.']);
    expect(User::query()->where('email', 'calon@example.com')->exists())->toBeFalse();
});

test('admin updates a cashier and keeps the old password when the field is left blank', function () {
    signInAsAdmin();
    $cashier = User::factory()->cashier()->create(['password' => 'password-lama-1']);

    $response = $this->put(route('admin.staff.update', $cashier), [
        'name' => 'Nama Baru',
        'email' => 'baru@example.com',
        'role' => 'admin',
        'is_active' => '1',
        'password' => '',
    ]);

    $response->assertRedirect(route('admin.staff.index'));

    $cashier->refresh();

    expect($cashier->name)->toBe('Nama Baru')
        ->and($cashier->email)->toBe('baru@example.com')
        ->and($cashier->role)->toBe(Role::Admin)
        ->and(Hash::check('password-lama-1', $cashier->password))->toBeTrue();
});

test('admin resets a cashier password by filling the field', function () {
    signInAsAdmin();
    $cashier = User::factory()->cashier()->create(['password' => 'password-lama-1']);

    $this->put(route('admin.staff.update', $cashier), [
        'name' => $cashier->name,
        'email' => $cashier->email,
        'role' => 'cashier',
        'is_active' => '1',
        'password' => 'password-baru-2',
    ])->assertRedirect(route('admin.staff.index'));

    expect(Hash::check('password-baru-2', $cashier->refresh()->password))->toBeTrue();
});

test('admin can switch another account off', function () {
    signInAsAdmin();
    $cashier = User::factory()->cashier()->create();

    $this->put(route('admin.staff.update', $cashier), [
        'name' => $cashier->name,
        'email' => $cashier->email,
        'role' => 'cashier',
        'is_active' => '0',
    ])->assertRedirect(route('admin.staff.index'));

    expect($cashier->refresh()->is_active)->toBeFalse();
});

test('updating staff may keep the account email but not take another account email', function () {
    signInAsAdmin();
    $cashier = User::factory()->cashier()->create(['email' => 'kasir@example.com']);

    $this->put(route('admin.staff.update', $cashier), [
        'name' => 'Sama Saja',
        'email' => 'kasir@example.com',
        'role' => 'cashier',
        'is_active' => '1',
    ])->assertRedirect(route('admin.staff.index'));

    $this->put(route('admin.staff.update', $cashier), [
        'name' => 'Sama Saja',
        'email' => 'utama@example.com',
        'role' => 'cashier',
        'is_active' => '1',
    ])->assertSessionHasErrors(['email' => 'Email sudah dipakai.']);
});

test('admin gets 403 when editing an owner account', function () {
    signInAsAdmin();
    $owner = User::factory()->owner()->create();

    $this->get(route('admin.staff.edit', $owner))->assertForbidden();
    $this->put(route('admin.staff.update', $owner), [
        'name' => 'Diganti',
        'email' => $owner->email,
        'role' => 'cashier',
        'is_active' => '1',
    ])->assertForbidden();

    expect($owner->refresh()->name)->not->toBe('Diganti')
        ->and($owner->role)->toBe(Role::Owner);
});

test('admins can change their own name but not their own role or status', function () {
    $admin = signInAsAdmin();

    $this->put(route('admin.staff.update', $admin), [
        'name' => 'Admin Berganti Nama',
        'email' => $admin->email,
    ])->assertRedirect(route('admin.staff.index'));

    expect($admin->refresh()->name)->toBe('Admin Berganti Nama');

    $this->put(route('admin.staff.update', $admin), [
        'name' => $admin->name,
        'email' => $admin->email,
        'role' => 'cashier',
        'is_active' => '0',
    ])->assertSessionHasErrors(['role' => 'Peran tidak boleh diubah.', 'is_active' => 'Status akun tidak boleh diubah.']);

    expect($admin->refresh()->role)->toBe(Role::Admin)
        ->and($admin->is_active)->toBeTrue();
});

test('the staff form hides role and status controls when editing your own account', function () {
    $admin = signInAsAdmin();

    $this->get(route('admin.staff.edit', $admin))
        ->assertOk()
        ->assertSeeText('tidak bisa diubah dari akun sendiri')
        ->assertDontSee('name="role"', false);

    $cashier = User::factory()->cashier()->create();

    $this->get(route('admin.staff.edit', $cashier))
        ->assertOk()
        ->assertSee('name="role"', false)
        ->assertSee('name="is_active"', false);
});
