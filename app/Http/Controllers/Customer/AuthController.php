<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Services\FonnteService;
use App\Support\PhoneNumber;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::guard('customer')->check()) {
            return redirect()->route('customer.dashboard');
        }

        return view('customer.auth.login');
    }

    public function checkPhone(Request $request): JsonResponse
    {
        $raw = (string) $request->query('phone', '');
        $normalized = PhoneNumber::normalize($raw);

        if (! PhoneNumber::isValid($normalized)) {
            return response()->json(['valid' => false, 'registered' => false]);
        }

        $exists = Member::query()->where('phone', $normalized)->exists();

        return response()->json([
            'valid' => true,
            'registered' => $exists,
        ]);
    }

    public function sendOtp(Request $request, FonnteService $fonnteService): RedirectResponse
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

        $member = Member::query()->where('phone', $normalized)->first();

        if (! $member && ! $request->filled('name')) {
            return back()->withInput()->with([
                'is_new_member' => true,
                'status_message' => 'Nomor WhatsApp belum terdaftar. Silakan masukkan nama Anda untuk pendaftaran member baru.',
            ]);
        }

        if (! $member) {
            $request->validate([
                'name' => ['required', 'string', 'max:50'],
            ], [
                'name.required' => 'Nama pelanggan wajib diisi untuk pendaftaran member baru.',
                'name.max' => 'Nama pelanggan tidak boleh lebih dari 50 karakter.',
            ]);
        }

        $otp = (string) random_int(1000, 9999);
        $expiresAt = Carbon::now()->addMinutes(1);

        try {
            $fonnteService->sendOTP($normalized, $otp);
        } catch (\Throwable $e) {
            Log::error('Fonnte sendOTP failed: '.$e->getMessage(), [
                'phone' => $normalized,
                'exception' => $e,
            ]);

            return back()->withInput()->withErrors([
                'phone' => 'Gagal mengirim OTP: '.$e->getMessage(),
            ]);
        }

        session([
            'customer_otp_phone' => $normalized,
            'customer_otp_name' => $member ? $member->name : trim($request->input('name')),
            'customer_otp_code' => $otp,
            'customer_otp_expires' => $expiresAt->timestamp,
            'customer_otp_attempts' => 0,
            'customer_otp_resend_at' => Carbon::now()->addSeconds(60)->timestamp,
        ]);

        return redirect()->route('customer.otp');
    }

    public function showOtp(): View|RedirectResponse
    {
        if (! session()->has('customer_otp_phone')) {
            return redirect()->route('customer.login');
        }

        $resendAt = (int) session('customer_otp_resend_at', 0);
        $countdown = max(0, $resendAt - Carbon::now()->timestamp);

        return view('customer.auth.otp', [
            'phone' => PhoneNumber::display(session('customer_otp_phone')),
            'countdown' => $countdown,
        ]);
    }

    public function resendOtp(FonnteService $fonnteService): RedirectResponse
    {
        if (! session()->has('customer_otp_phone')) {
            return redirect()->route('customer.login');
        }

        $resendAt = (int) session('customer_otp_resend_at', 0);
        if (Carbon::now()->timestamp < $resendAt) {
            $secondsLeft = $resendAt - Carbon::now()->timestamp;

            return redirect()->route('customer.otp')->withErrors([
                'otp' => "Mohon tunggu {$secondsLeft} detik lagi sebelum mengirim ulang kode OTP.",
            ]);
        }

        $phone = session('customer_otp_phone');
        $otp = (string) random_int(1000, 9999);
        $expiresAt = Carbon::now()->addMinutes(5);

        try {
            $fonnteService->sendOTP($phone, $otp);
        } catch (\Throwable $e) {
            Log::error('Fonnte resendOtp failed: '.$e->getMessage(), [
                'phone' => $phone,
                'exception' => $e,
            ]);

            return redirect()->route('customer.otp')->withErrors([
                'otp' => 'Gagal mengirim ulang OTP: '.$e->getMessage(),
            ]);
        }

        session([
            'customer_otp_code' => $otp,
            'customer_otp_expires' => $expiresAt->timestamp,
            'customer_otp_attempts' => 0,
            'customer_otp_resend_at' => Carbon::now()->addSeconds(60)->timestamp,
        ]);

        return redirect()->route('customer.otp')->with('status', 'Kode OTP baru berhasil dikirim ke WhatsApp Anda.');
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
            session()->forget(['customer_otp_phone', 'customer_otp_name', 'customer_otp_code', 'customer_otp_expires', 'customer_otp_attempts', 'customer_otp_resend_at', 'otp_simulated']);

            return redirect()->route('customer.login')->withErrors([
                'phone' => 'Kode OTP telah kedaluwarsa. Silakan masukkan nomor Anda kembali.',
            ]);
        }

        $attempts = (int) session('customer_otp_attempts', 0);

        if ($request->input('otp') !== $storedCode) {
            $attempts++;

            if ($attempts >= 5) {
                session()->forget(['customer_otp_phone', 'customer_otp_name', 'customer_otp_code', 'customer_otp_expires', 'customer_otp_attempts', 'customer_otp_resend_at', 'otp_simulated']);

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

        $storedName = session('customer_otp_name');

        $member = Member::query()->firstOrCreate(
            ['phone' => $storedPhone],
            [
                'name' => $storedName ?: ('Pelanggan '.substr($storedPhone, -4)),
                'points_balance' => 0,
                'lifetime_spend' => 0,
            ]
        );

        session()->forget(['customer_otp_phone', 'customer_otp_name', 'customer_otp_code', 'customer_otp_expires', 'customer_otp_attempts', 'customer_otp_resend_at', 'otp_simulated']);

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
