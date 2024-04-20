<?php

namespace Volistx\FrameworkKernel\Tests;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use PHPUnit\Framework\Attributes\Test;
use Volistx\FrameworkKernel\AuthValidationRules\Users\SubscriptionRateLimitValidationRule;
use Volistx\FrameworkKernel\Database\Factories\PersonalTokenFactory;
use Volistx\FrameworkKernel\Database\Factories\PlanFactory;
use Volistx\FrameworkKernel\Database\Factories\UserFactory;
use Volistx\FrameworkKernel\Enums\RateLimitMode;
use Volistx\FrameworkKernel\Facades\Messages;
use Volistx\FrameworkKernel\Facades\PersonalTokens;
use Volistx\FrameworkKernel\Facades\Plans;

class SubscriptionRateLimitValidationRuleTest extends TestCase
{
    #[Test]
    public function testAccessAllowedWhenRateLimitModeIsNotSubscription()
    {
        $this->generatePlan(['requests' => 500]);
        $user = $this->generateUser();
        $token = $this->generatePersonalToken($user->id, ['rate_limit_mode' => RateLimitMode::IP]);
        PersonalTokens::shouldReceive('getToken')->andReturn($token);

        $request = new Request(); // Use a real request instance
        $subscriptionRateLimitValidationRule = new SubscriptionRateLimitValidationRule($request);

        $result = $subscriptionRateLimitValidationRule->validate();

        $this->assertTrue($result);
    }

    #[Test]
    public function testAccessAllowedWhenRateLimitNotExceeded()
    {
        $plan = $this->generatePlan(['requests' => 500]);
        $user = $this->generateUser();
        $token = $this->generatePersonalToken($user->id, ['rate_limit_mode' => RateLimitMode::SUBSCRIPTION]);
        PersonalTokens::shouldReceive('getToken')->andReturn($token);

        Plans::shouldReceive('getPlan')->andReturn($plan);

        RateLimiter::shouldReceive('attempt')->andReturn(true);

        $request = new Request(); // Use a real request instance
        $subscriptionRateLimitValidationRule = new SubscriptionRateLimitValidationRule($request);

        $result = $subscriptionRateLimitValidationRule->validate();

        $this->assertTrue($result);
    }

    #[Test]
    public function testAccessDeniedWhenRateLimitExceeded()
    {
        $plan = $this->generatePlan(['requests' => 500, 'rate_limit' => 1]);
        $user = $this->generateUser();
        $token = $this->generatePersonalToken($user->id, ['rate_limit_mode' => RateLimitMode::SUBSCRIPTION]);
        PersonalTokens::shouldReceive('getToken')->andReturn($token);

        Plans::shouldReceive('getPlan')->andReturn($plan);

        RateLimiter::shouldReceive('attempt')->andReturn(false);

        $request = new Request(); // Use a real request instance
        $subscriptionRateLimitValidationRule = new SubscriptionRateLimitValidationRule($request);

        $result = $subscriptionRateLimitValidationRule->validate();

        $this->assertEquals(
            [
                'message' => Messages::E429(),
                'code' => 429,
            ],
            $result
        );
    }

    private function generateUser(): Collection|Model
    {
        return UserFactory::new()->create();
    }

    private function generatePlan(array $data): Collection|Model
    {
        return PlanFactory::new()->create(['data' => $data]);
    }

    private function generatePersonalToken(string $user_id, array $inputs): Collection|Model
    {
        return PersonalTokenFactory::new()->create(
            array_merge(
                [
                    'user_id' => $user_id,
                ],
                $inputs
            )
        );
    }
}
