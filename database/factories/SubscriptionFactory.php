<?php

namespace Volistx\FrameworkKernel\Database\Factories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Volistx\FrameworkKernel\Enums\SubscriptionStatus;
use Volistx\FrameworkKernel\Models\Subscription;

class SubscriptionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Subscription::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => Str::ulid()->toRfc4122(),
            'plan_id' => Str::ulid()->toRfc4122(),
            'activated_at' => Carbon::now(),
            'expires_at' => Carbon::now()->addHours($this->faker->numberBetween(24, 720)),
            'status' => SubscriptionStatus::ACTIVE,
            'cancels_at' => null,
            'cancelled_at' => null,
        ];
    }
}
