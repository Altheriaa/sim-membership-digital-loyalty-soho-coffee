<?php

namespace App\Http\Resources;

use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Member
 */
class MemberResource extends JsonResource
{
    /**
     * The member as the register shows it: who they are, their points and the tier bonus that applies to this sale.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $tier = $this->tier();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'phone' => $this->displayPhone(),
            'points_balance' => $this->points_balance,
            'tier' => $tier?->name,
            'multiplier_hundredths' => $tier?->multiplierHundredths() ?? 100,
            'multiplier_label' => $tier?->multiplierLabel() ?? '1',
        ];
    }
}
