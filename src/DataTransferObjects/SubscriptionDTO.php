<?php

namespace Volistx\FrameworkKernel\DataTransferObjects;

use Carbon\Carbon;

class SubscriptionDTO extends DataTransferObjectBase
{
    public string $id;
    public int $user_id;
    public string $hmac_token;
    public string $plan_activated_at;
    public ?string $plan_expires_at;
    public string $created_at;
    public string $updated_at;
    public ?string $plan_cancels_at;
    public ?string $plan_cancelled_at;

    public static function fromModel($subscription): self
    {
        return new self($subscription);
    }

    public function GetDTO(): array
    {
        return [
            'id'          => $this->id,
            'user_id'     => $this->user_id,
            'plan'        => PlanDTO::fromModel($this->entity->plan()->first())->GetDTO(),
            'hmac_token'  => $this->hmac_token,
            'plan_status' => [
                'is_expired'   => $this->plan_expires_at != null && Carbon::now()->gte(Carbon::createFromTimeString($this->plan_expires_at)),
                'is_cancelled' => $this->plan_cancels_at && Carbon::now()->gte($this->plan_cancels_at),
                'activated_at' => $this->plan_activated_at,
                'expires_at'   => $this->plan_expires_at,
                'cancels_at'   => $this->plan_cancels_at,
                'cancelled_at' => $this->plan_cancelled_at,
            ],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
