<?php

use App\Services\FonnteService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

uses(TestCase::class);

test('fonnte service simulates send in testing environment by default', function () {
    $service = new FonnteService;
    $result = $service->sendOTP('081234567890', '1234');

    expect($result)->toBeArray()
        ->and($result['status'])->toBeTrue();
});

test('fonnte service sends http request with correct authorization and payload when not simulating', function () {
    Config::set('services.fonnte.token', 'test-fonnte-token');

    Http::fake([
        'https://api.fonnte.com/send' => Http::response([
            'status' => true,
            'target' => ['6281234567890'],
            'id' => ['msg-123'],
        ], 200),
    ]);

    // Override env to production for this test
    app()->detectEnvironment(fn () => 'production');

    $service = new FonnteService;
    $result = $service->sendOTP('6281234567890', '4321');

    expect($result)->toBeArray()
        ->and($result['status'])->toBeTrue()
        ->and($result['id'])->toBe(['msg-123']);

    Http::assertSent(function ($request) {
        return $request->url() === 'https://api.fonnte.com/send'
            && $request->hasHeader('Authorization', 'test-fonnte-token')
            && $request['target'] === '6281234567890'
            && str_contains($request['message'], '4321');
    });

    app()->detectEnvironment(fn () => 'testing');
});

test('fonnte service throws runtime exception when token is not configured', function () {
    Config::set('services.fonnte.token', null);

    app()->detectEnvironment(fn () => 'production');

    $service = new FonnteService;

    expect(fn () => $service->sendOTP('6281234567890', '1234'))
        ->toThrow(RuntimeException::class, 'Token Fonnte belum dikonfigurasi');

    app()->detectEnvironment(fn () => 'testing');
});

test('fonnte service throws runtime exception when api responds with status false', function () {
    Config::set('services.fonnte.token', 'test-fonnte-token');

    Http::fake([
        'https://api.fonnte.com/send' => Http::response([
            'status' => false,
            'reason' => 'device disconnected',
        ], 200),
    ]);

    app()->detectEnvironment(fn () => 'production');

    $service = new FonnteService;

    expect(fn () => $service->sendOTP('6281234567890', '1234'))
        ->toThrow(RuntimeException::class, 'device disconnected');

    app()->detectEnvironment(fn () => 'testing');
});
