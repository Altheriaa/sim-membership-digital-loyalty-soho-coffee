<?php

use App\Models\User;
use App\Role;

test('login page shows the sign-in form to guests', function () {
    $response = $this->get(route('login'));

    $response->assertOk()
        ->assertSeeText('Masuk ke back office')
        ->assertSee('name="email"', false)
        ->assertSee('name="password"', false);
});

test('guests who open the home page are sent to the login page', function () {
    $this->get('/')->assertRedirect(route('login'));
});

test('staff land on the screen that belongs to their role after signing in', function (Role $role, string $expectedRoute) {
    $user = User::factory()->create(['role' => $role, 'email' => 'staff@example.com']);

    $response = $this->post(route('login'), ['email' => 'staff@example.com', 'password' => 'password']);

    $response->assertRedirect(route($expectedRoute));
    $this->assertAuthenticatedAs($user);
})->with([
    'owner' => [Role::Owner, 'admin.dashboard'],
    'admin' => [Role::Admin, 'admin.dashboard'],
    'cashier' => [Role::Cashier, 'pos.index'],
]);

test('login rejects a wrong password with a message and stays signed out', function () {
    User::factory()->admin()->create(['email' => 'admin@example.com']);

    $response = $this->from(route('login'))->post(route('login'), ['email' => 'admin@example.com', 'password' => 'salah-total']);

    $response->assertRedirect(route('login'))
        ->assertSessionHasErrors(['email' => 'Email atau kata sandi salah.']);
    $this->assertGuest();
});

test('login treats an inactive account exactly like a wrong password', function () {
    User::factory()->admin()->inactive()->create(['email' => 'off@example.com']);

    $response = $this->from(route('login'))->post(route('login'), ['email' => 'off@example.com', 'password' => 'password']);

    $response->assertSessionHasErrors(['email' => 'Email atau kata sandi salah.']);
    $this->assertGuest();
});

test('login requires an email and a password', function () {
    $response = $this->from(route('login'))->post(route('login'), []);

    $response->assertSessionHasErrors([
        'email' => 'Email wajib diisi.',
        'password' => 'Kata sandi wajib diisi.',
    ]);
});

test('login locks out an email after five failed attempts even with the right password', function () {
    User::factory()->admin()->create(['email' => 'admin@example.com']);

    foreach (range(1, 5) as $attempt) {
        $this->post(route('login'), ['email' => 'admin@example.com', 'password' => 'salah-total']);
    }

    $response = $this->post(route('login'), ['email' => 'admin@example.com', 'password' => 'password']);

    $response->assertSessionHasErrors('email');
    expect(session('errors')->first('email'))->toStartWith('Terlalu banyak percobaan masuk');
    $this->assertGuest();
});

test('signed-in staff are redirected away from the login page to their own screen', function () {
    $this->actingAs(User::factory()->cashier()->create());

    $this->get(route('login'))->assertRedirect(route('pos.index'));
});

test('logout signs the user out and returns to the login page', function () {
    $this->actingAs(User::factory()->admin()->create());

    $this->post(route('logout'))->assertRedirect(route('login'));

    $this->assertGuest();
});

test('an account switched off during a session is signed out on its next request', function () {
    $this->actingAs(User::factory()->admin()->inactive()->create());

    $response = $this->get(route('admin.dashboard'));

    $response->assertRedirect(route('login'))
        ->assertSessionHasErrors(['email' => 'Akun ini sudah dinonaktifkan. Hubungi admin.']);
    $this->assertGuest();
});
