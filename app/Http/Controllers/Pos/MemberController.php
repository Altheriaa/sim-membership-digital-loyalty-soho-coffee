<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Http\Requests\LookupMemberRequest;
use App\Http\Requests\MemberRequest;
use App\Http\Resources\MemberResource;
use App\Models\Member;
use Illuminate\Http\JsonResponse;

class MemberController extends Controller
{
    // cari member berdasar no wa
    public function lookup(LookupMemberRequest $request): JsonResponse
    {
        $member = Member::query()->where('phone', $request->validated('phone'))->first();

        return response()->json([
            'member' => $member ? MemberResource::make($member)->resolve($request) : null,
        ]);
    }

    // Register Cepat di POS
    public function store(MemberRequest $request): JsonResponse
    {
        $member = Member::create($request->validated());

        return response()->json([
            'member' => MemberResource::make($member)->resolve($request),
        ], 201);
    }
}
