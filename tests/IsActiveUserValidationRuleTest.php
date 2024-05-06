<?php

namespace Volistx\FrameworkKernel\Tests;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\Test;
use Volistx\FrameworkKernel\AuthValidationRules\Users\IsActiveUserValidationRule;
use Volistx\FrameworkKernel\Database\Factories\PlanFactory;
use Volistx\FrameworkKernel\Database\Factories\SubscriptionFactory;
use Volistx\FrameworkKernel\Database\Factories\UserFactory;
use Volistx\FrameworkKernel\Enums\SubscriptionStatus;
use Volistx\FrameworkKernel\Facades\Messages;
use Volistx\FrameworkKernel\Facades\Subscriptions;

class IsActiveUserValidationRuleTest extends TestCase
{
    #[Test]
    public function test_access_allowed_when_user_is_active()
    {
        $user = $this->generateUser(true);
        $plan = $this->generatePlan(['requests' => 500]);
        $subscription = $this->generateSubscription($user->id, $plan->id, SubscriptionStatus::ACTIVE);
        Subscriptions::shouldReceive('getSubscription')->andReturn($subscription);

        $request = new Request(); // Real request instance
        $isActiveUserValidationRule = new IsActiveUserValidationRule($request);

        $result = $isActiveUserValidationRule->validate();

        $this->assertTrue($result);
    }

    private function generateUser(bool $is_active): Collection|Model
    {
        return UserFactory::new()->create([
            'is_active' => $is_active,
        ]);
    }

    private function generatePlan(array $data): Collection|Model
    {
        return PlanFactory::new()->create(['data' => $data]);
    }

    private function generateSubscription($user_id, $plan_id, SubscriptionStatus $status): Collection|Model
    {
        return SubscriptionFactory::new()->create([
            'user_id' => $user_id,
            'plan_id' => $plan_id,
            'status' => $status,
        ]);
    }

    #[Test]
    public function test_access_denied_when_user_is_inactive()
    {
        $user = $this->generateUser(false);
        $plan = $this->generatePlan(['requests' => 500]);
        $subscription = $this->generateSubscription($user->id, $plan->id, SubscriptionStatus::ACTIVE);
        Subscriptions::shouldReceive('getSubscription')->andReturn($subscription);

        $request = new Request(); // Real request instance
        $isActiveUserValidationRule = new IsActiveUserValidationRule($request);

        $result = $isActiveUserValidationRule->validate();

        $this->assertEquals(
            [
                'message' => Messages::E403(trans('volistx::user:inactive_user')),
                'code' => 403,
            ],
            $result
        );
    }
}
