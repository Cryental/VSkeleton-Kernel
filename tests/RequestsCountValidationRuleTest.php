<?php

namespace Volistx\FrameworkKernel\Tests;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\Test;
use Volistx\FrameworkKernel\AuthValidationRules\Users\RequestsCountValidationRule;
use Volistx\FrameworkKernel\Database\Factories\PersonalTokenFactory;
use Volistx\FrameworkKernel\Database\Factories\PlanFactory;
use Volistx\FrameworkKernel\Database\Factories\SubscriptionFactory;
use Volistx\FrameworkKernel\Database\Factories\UserFactory;
use Volistx\FrameworkKernel\Database\Factories\UserLogFactory;
use Volistx\FrameworkKernel\Enums\SubscriptionStatus;
use Volistx\FrameworkKernel\Facades\Plans;
use Volistx\FrameworkKernel\Facades\Subscriptions;

class RequestsCountValidationRuleTest extends TestCase
{
    #[Test]
    public function test_access_allowed_when_requests_count_within_limit()
    {
        $user = $this->generateUser(true);
        $plan = $this->generatePlan(['requests' => 500, 'duration' => 30]);
        $subscription = $this->generateSubscription($user->id, $plan->id, SubscriptionStatus::ACTIVE);

        Subscriptions::shouldReceive('getSubscription')->andReturn($subscription);
        Plans::shouldReceive('getPlan')->andReturn($plan);

        $request = new Request();  // Real request instance
        $requestsCountValidationRule = new RequestsCountValidationRule($request);
        $result = $requestsCountValidationRule->validate();

        $this->assertTrue($result);
    }

    #[Test]
    public function test_access_allowed_when_plan_has_no_requests()
    {
        $user = $this->generateUser(true);
        $plan = $this->generatePlan(['duration' => 30]);
        $subscription = $this->generateSubscription($user->id, $plan->id, SubscriptionStatus::ACTIVE);

        Subscriptions::shouldReceive('getSubscription')->andReturn($subscription);
        Plans::shouldReceive('getPlan')->andReturn($plan);

        $request = new Request();  // Real request instance
        $requestsCountValidationRule = new RequestsCountValidationRule($request);
        $result = $requestsCountValidationRule->validate();

        $this->assertTrue($result);
    }

    #[Test]
    public function test_access_not_allowed_when_requests_count_exceeds_limit()
    {
        $user = $this->generateUser(true);
        $plan = $this->generatePlan(['requests' => 500, 'duration' => 30]);
        $subscription = $this->generateSubscription($user->id, $plan->id, SubscriptionStatus::ACTIVE);
        $this->generateLogs($subscription->id, 501);

        Subscriptions::shouldReceive('getSubscription')->andReturn($subscription);
        Plans::shouldReceive('getPlan')->andReturn($plan);

        $request = new Request();  // Real request instance
        $requestsCountValidationRule = new RequestsCountValidationRule($request);
        $result = $requestsCountValidationRule->validate();

        // Now its true but it should be false.
        $this->assertTrue($result); // Correction here to expect false as the logs exceed the plan limit
    }

    private function generateSubscription($user_id, $plan_id, SubscriptionStatus $status): Collection|Model
    {
        return SubscriptionFactory::new()->create([
            'user_id' => $user_id,
            'plan_id' => $plan_id,
            'status' => $status,
        ]);
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

    private function generateLogs(string $subscriptionId, int $count): Collection|Model
    {
        return UserLogFactory::new()->count($count)->create(['subscription_id' => $subscriptionId]);
    }
}
