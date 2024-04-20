<?php

namespace Volistx\FrameworkKernel\Tests;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\Test;
use Volistx\FrameworkKernel\AuthValidationRules\Users\SubscriptionValidationRule;
use Volistx\FrameworkKernel\Database\Factories\PersonalTokenFactory;
use Volistx\FrameworkKernel\Database\Factories\PlanFactory;
use Volistx\FrameworkKernel\Database\Factories\SubscriptionFactory;
use Volistx\FrameworkKernel\Database\Factories\UserFactory;
use Volistx\FrameworkKernel\Enums\SubscriptionStatus;
use Volistx\FrameworkKernel\Facades\Messages;
use Volistx\FrameworkKernel\Facades\PersonalTokens;
use Volistx\FrameworkKernel\Facades\Plans;
use Volistx\FrameworkKernel\Facades\Subscriptions;

class SubscriptionValidationRuleTest extends TestCase
{
    #[Test]
    public function testAccessAllowedWithActiveSubscription()
    {
        $user = $this->generateUser();
        $plan = $this->generatePlan(['requests' => 500]);
        $personalToken = $this->generatePersonalToken($user->id, []);
        $subscription = $this->generateSubscription(
            $user->id,
            [
                'status' => SubscriptionStatus::ACTIVE,
                'plan_id' => $plan->id,
            ]
        );
        PersonalTokens::shouldReceive('getToken')->andReturn($personalToken);
        Subscriptions::shouldReceive('ProcessUserActiveSubscriptionsStatus')->andReturn($subscription);
        Subscriptions::shouldReceive('setSubscription')->once();
        Plans::shouldReceive('setPlan')->once();

        $request = new Request();
        $subscriptionValidationRule = new SubscriptionValidationRule($request);
        $result = $subscriptionValidationRule->validate();

        $this->assertTrue($result);
    }

    #[Test]
    public function testAccessAllowedWithInactiveSubscription()
    {
        $user = $this->generateUser();
        $plan = $this->generatePlan(['requests' => 500]);
        $personalToken = $this->generatePersonalToken($user->id, []);
        $subscription = $this->generateSubscription(
            $user->id,
            [
                'status' => SubscriptionStatus::INACTIVE,
                'plan_id' => $plan->id,
            ]
        );
        PersonalTokens::shouldReceive('getToken')->andReturn($personalToken);
        Subscriptions::shouldReceive('ProcessUserActiveSubscriptionsStatus')->andReturn(null);
        Subscriptions::shouldReceive('ProcessUserInactiveSubscriptionsStatus')->andReturn($subscription);
        Subscriptions::shouldReceive('setSubscription')->once();
        Plans::shouldReceive('setPlan')->once();

        $request = new Request();
        $subscriptionValidationRule = new SubscriptionValidationRule($request);
        $result = $subscriptionValidationRule->validate();

        $this->assertTrue($result);
    }

    #[Test]
    public function testAccessDeniedWithoutActiveOrInactiveSubscription()
    {
        $user = $this->generateUser();
        $personalToken = $this->generatePersonalToken($user->id, []);
        PersonalTokens::shouldReceive('getToken')->andReturn($personalToken);
        Subscriptions::shouldReceive('ProcessUserActiveSubscriptionsStatus')->andReturn(null);
        Subscriptions::shouldReceive('ProcessUserInactiveSubscriptionsStatus')->andReturn(null);

        $request = new Request();
        $subscriptionValidationRule = new SubscriptionValidationRule($request);
        $result = $subscriptionValidationRule->validate();

        $this->assertEquals(
            [
                'message' => Messages::E403(trans('volistx::subscription.expired')),
                'code' => 403,
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

    private function generateSubscription(string $user_id, array $inputs): Collection|Model
    {
        return SubscriptionFactory::new()->create(
            array_merge(
                [
                    'user_id' => $user_id,
                ],
                $inputs
            )
        );
    }
}
