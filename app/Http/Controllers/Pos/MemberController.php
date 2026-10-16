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
    /**
     * Find a member by WhatsApp number. An unknown number is a normal answer, not an error, so it returns member: null.
     */
    public function lookup(LookupMemberRequest $request): JsonResponse
    {
        $member = Member::query()->where('phone', $request->validated('phone'))->first();

        return response()->json([
            'member' => $member ? MemberResource::make($member)->resolve($request) : null,
        ]);
    }

    /**
     * Quick register from the register: a WhatsApp number and a nickname are all the cashier asks for.
     */
    public function store(MemberRequest $request): JsonResponse
    {
        $member = Member::create($request->validated());

        return response()->json([
            'member' => MemberResource::make($member)->resolve($request),
        ], 201);
    }
}
