<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Member;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PinController extends Controller
{
    public function show(): View
    {
        /** @var Member $member */
        $member = Auth::guard('customer')->user();

        return view('customer.pin.show', [
            'member' => $member,
            'hasPin' => $member->hasPin(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        /** @var Member $member */
        $member = Auth::guard('customer')->user();

        $rules = [
            'pin' => ['required', 'digits:6', 'confirmed'],
        ];

        $messages = [
            'pin.required' => 'PIN Keamanan 6-digit wajib diisi.',
            'pin.digits' => 'PIN Keamanan harus terdiri dari tepat 6 digit angka.',
            'pin.confirmed' => 'Konfirmasi PIN Keamanan tidak cocok.',
        ];

        if ($member->hasPin()) {
            $rules['current_pin'] = ['required', 'digits:6'];
            $messages['current_pin.required'] = 'PIN Keamanan lama wajib diisi.';
            $messages['current_pin.digits'] = 'PIN lama harus berupa 6 digit angka.';
        }

        $request->validate($rules, $messages);

        if ($member->hasPin() && ! $member->verifyPin($request->input('current_pin'))) {
            return back()->withErrors([
                'current_pin' => 'PIN Keamanan lama yang Anda masukkan salah.',
            ]);
        }

        $member->setPin($request->input('pin'));

        return redirect()->route('customer.pin.show')->with('status', 'PIN Keamanan 6-digit berhasil disimpan.');
    }

    public function requestReset(): RedirectResponse
    {
        /** @var Member $member */
        $member = Auth::guard('customer')->user();

        $otp = (string) random_int(1000, 9999);
        $expiresAt = Carbon::now()->addMinutes(5);

        session([
            'reset_pin_otp' => $otp,
            'reset_pin_expires' => $expiresAt->timestamp,
            'reset_pin_attempts' => 0,
            'otp_simulated' => [
                'phone' => $member->displayPhone(),
                'code' => $otp,
            ],
        ]);

        return redirect()->route('customer.pin.reset.form');
    }

    public function showResetForm(): View|RedirectResponse
    {
        if (! session()->has('reset_pin_otp')) {
            return redirect()->route('customer.pin.show');
        }

        /** @var Member $member */
        $member = Auth::guard('customer')->user();

        return view('customer.pin.reset', [
            'member' => $member,
        ]);
    }

    public function processReset(Request $request): RedirectResponse
    {
        /** @var Member $member */
        $member = Auth::guard('customer')->user();

        $request->validate([
            'otp' => ['required', 'string', 'size:4'],
            'pin' => ['required', 'digits:6', 'confirmed'],
        ], [
            'otp.required' => 'Kode OTP WhatsApp wajib diisi.',
            'otp.size' => 'Kode OTP harus 4 digit.',
            'pin.required' => 'PIN Keamanan baru wajib diisi.',
            'pin.digits' => 'PIN baru harus 6 digit angka.',
            'pin.confirmed' => 'Konfirmasi PIN baru tidak sesuai.',
        ]);

        $storedCode = session('reset_pin_otp');
        $expires = session('reset_pin_expires');

        if (! $storedCode || ! $expires || Carbon::now()->timestamp > $expires) {
            session()->forget(['reset_pin_otp', 'reset_pin_expires', 'reset_pin_attempts', 'otp_simulated']);

            return redirect()->route('customer.pin.show')->withErrors([
                'error' => 'Sesi OTP reset telah kedaluwarsa. Silakan ajukan reset kembali.',
            ]);
        }

        $attempts = (int) session('reset_pin_attempts', 0);

        if ($request->input('otp') !== $storedCode) {
            $attempts++;

            if ($attempts >= 5) {
                session()->forget(['reset_pin_otp', 'reset_pin_expires', 'reset_pin_attempts', 'otp_simulated']);

                return redirect()->route('customer.pin.show')->withErrors([
                    'error' => 'Terlalu banyak percobaan salah. Silakan ajukan reset PIN kembali.',
                ]);
            }

            session(['reset_pin_attempts' => $attempts]);

            $attemptsLeft = 5 - $attempts;

            return back()->withErrors([
                'otp' => "Kode OTP verifikasi salah. Sisa percobaan: {$attemptsLeft} kali.",
            ]);
        }

        $member->setPin($request->input('pin'));
        session()->forget(['reset_pin_otp', 'reset_pin_expires', 'reset_pin_attempts', 'otp_simulated']);

        return redirect()->route('customer.pin.show')->with('status', 'PIN Keamanan berhasil direset dan diperbarui.');
    }
}
