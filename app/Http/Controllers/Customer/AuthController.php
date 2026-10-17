<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Support\PhoneNumber;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::guard('customer')->check()) {
            return redirect()->route('customer.dashboard');
        }

        return view('customer.auth.login');
    }

    public function sendOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'phone' => ['required', 'string'],
        ], [
            'phone.required' => 'Nomor WhatsApp wajib diisi.',
        ]);

        $normalized = PhoneNumber::normalize($request->input('phone'));

        if (! PhoneNumber::isValid($normalized)) {
            return back()->withInput()->withErrors([
                'phone' => 'Nomor WhatsApp tidak valid. Gunakan format nomor HP Indonesia, misalnya 0812 3456 7890.',
            ]);
        }

        $otp = (string) random_int(1000, 9999);
        $expiresAt = Carbon::now()->addMinutes(5);

        session([
            'customer_otp_phone' => $normalized,
            'customer_otp_code' => $otp,
            'customer_otp_expires' => $expiresAt->timestamp,
            'customer_otp_attempts' => 0,
            'otp_simulated' => [
                'phone' => PhoneNumber::display($normalized),
                'code' => $otp,
            ],
        ]);

        return redirect()->route('customer.otp');
    }

    public function showOtp(): View|RedirectResponse
    {
        if (! session()->has('customer_otp_phone')) {
            return redirect()->route('customer.login');
        }

        return view('customer.auth.otp', [
            'phone' => PhoneNumber::display(session('customer_otp_phone')),
        ]);
    }

    public function verifyOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'otp' => ['required', 'string', 'size:4'],
        ], [
            'otp.required' => 'Kode OTP 4-digit wajib diisi.',
            'otp.size' => 'Kode OTP harus tepat 4 digit angka.',
        ]);

        $storedPhone = session('customer_otp_phone');
        $storedCode = session('customer_otp_code');
        $expires = session('customer_otp_expires');

        if (! $storedPhone || ! $storedCode || ! $expires || Carbon::now()->timestamp > $expires) {
            session()->forget(['customer_otp_phone', 'customer_otp_code', 'customer_otp_expires', 'customer_otp_attempts', 'otp_simulated']);

            return redirect()->route('customer.login')->withErrors([
                'phone' => 'Kode OTP telah kedaluwarsa. Silakan masukkan nomor Anda kembali.',
            ]);
        }

        $attempts = (int) session('customer_otp_attempts', 0);

        if ($request->input('otp') !== $storedCode) {
            $attempts++;

            if ($attempts >= 5) {
                session()->forget(['customer_otp_phone', 'customer_otp_code', 'customer_otp_expires', 'customer_otp_attempts', 'otp_simulated']);

                return redirect()->route('customer.login')->withErrors([
                    'phone' => 'Terlalu banyak percobaan salah. Silakan minta kode OTP baru.',
                ]);
            }

            session(['customer_otp_attempts' => $attempts]);

            $attemptsLeft = 5 - $attempts;

            return back()->withErrors([
                'otp' => "Kode OTP yang Anda masukkan salah. Sisa percobaan: {$attemptsLeft} kali.",
            ]);
        }

        $member = Member::query()->firstOrCreate(
            ['phone' => $storedPhone],
            [
                'name' => 'Pelanggan '.substr($storedPhone, -4),
                'points_balance' => 0,
                'lifetime_spend' => 0,
            ]
        );

        session()->forget(['customer_otp_phone', 'customer_otp_code', 'customer_otp_expires', 'customer_otp_attempts', 'otp_simulated']);

        session()->regenerate();

        Auth::guard('customer')->login($member);

        return redirect()->route('customer.dashboard')->with('status', "Selamat datang, {$member->name}!");
    }

    public function logout(): RedirectResponse
    {
        Auth::guard('customer')->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('customer.login');
    }
}
