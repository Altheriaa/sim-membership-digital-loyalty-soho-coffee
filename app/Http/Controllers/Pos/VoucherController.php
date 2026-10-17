<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Http\Requests\LookupVoucherRequest;
use App\Models\Member;
use App\Models\MemberVoucher;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class VoucherController extends Controller
{
    /**
     * Look up a claimed voucher by its code for the selected member. Nothing is
     * consumed here; the voucher stays usable until the order is paid and the
     * store endpoint re-verifies everything again with `lockForUpdate`.
     */
    public function lookup(LookupVoucherRequest $request): JsonResponse
    {
        $member = Member::query()->findOrFail($request->validated('member_id'));

        $memberVoucher = MemberVoucher::query()
            ->with(['voucher.menuItem'])
            ->where('code', $request->validated('code'))
            ->first();

        if (! $memberVoucher || $memberVoucher->member_id !== $member->id) {
            throw ValidationException::withMessages([
                'code' => 'Kode voucer tidak ditemukan untuk member ini. Periksa kode di halaman Kupon Saya aplikasi member.',
            ]);
        }

        if ($memberVoucher->status === 'used') {
            throw ValidationException::withMessages([
                'code' => 'Voucer sudah pernah digunakan pada transaksi lain.',
            ]);
        }

        if (! $memberVoucher->isRedeemable()) {
            throw ValidationException::withMessages([
                'code' => 'Voucer sudah kedaluwarsa sejak '.$memberVoucher->expires_at->translatedFormat('d M Y, H:i').' WIB.',
            ]);
        }

        if (! $member->hasPin()) {
            throw ValidationException::withMessages([
                'pin' => 'Member ini belum membuat PIN Keamanan. Minta member membuat PIN di menu PIN Akun aplikasinya.',
            ]);
        }

        return response()->json([
            'voucher' => [
                'id' => $memberVoucher->id,
                'code' => $memberVoucher->code,
                'name' => $memberVoucher->voucher->name,
                'benefit' => $memberVoucher->voucher->benefitLabel(),
                'amount' => $memberVoucher->voucher->benefitAmount(),
                'points_spent' => $memberVoucher->points_spent,
                'expires_label' => $memberVoucher->expires_at->translatedFormat('d M Y, H:i'),
                'owner_name' => $member->name,
            ],
        ]);
    }
}
