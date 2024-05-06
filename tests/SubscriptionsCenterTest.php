<?php

namespace Volistx\FrameworkKernel\Tests;

use Carbon\Carbon;
use Illuminate\Contracts\Events\Dispatcher;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Volistx\FrameworkKernel\Helpers\SubscriptionCenter;
use Volistx\FrameworkKernel\Jobs\SubscriptionCancelled;
use Volistx\FrameworkKernel\Jobs\SubscriptionExpired;
use Volistx\FrameworkKernel\Models\Subscription;
use Volistx\FrameworkKernel\Repositories\SubscriptionRepository;

class SubscriptionsCenterTest extends TestCase
{
    private ?SubscriptionCenter $subscriptionCenter;

    private ?MockObject $subscriptionRepositoryMock;

    private ?MockObject $eventDispatcherMock;

    #[Test]
    public function test_should_subscription_be_expired()
    {
        $subscription = (object)[
            'expires_at' => Carbon::now()->subDay(),
        ];

        $result = $this->subscriptionCenter->shouldSubscriptionBeExpired($subscription);

        $this->assertTrue($result);
    }

    #[Test]
    public function test_should_subscription_be_cancelled()
    {
        $subscription = (object)[
            'cancels_at' => Carbon::now()->subDay(),
        ];

        $result = $this->subscriptionCenter->shouldSubscriptionBeCancelled($subscription);

        $this->assertTrue($result);
    }

    #[Test]
    public function test_update_subscription_expiry_status()
    {
        $userId = 'user123';
        $subscriptionId = 'subscription123';
        $subscription = (object)[
            'id' => $subscriptionId,
            'user_id' => $userId,
            'expires_at' => Carbon::now()->subDay(),
        ];

        $this->subscriptionRepositoryMock->expects($this->once())
            ->method('update')
            ->with($userId, $subscriptionId);

        $this->eventDispatcherMock->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(SubscriptionExpired::class));

        $this->subscriptionCenter->updateSubscriptionExpiryStatus($userId, $subscription);
    }

    #[Test]
    public function test_update_subscription_cancellation_status()
    {
        $userId = 'user123';
        $subscriptionId = 'subscription123';
        $subscription = (object)[
            'id' => $subscriptionId,
            'user_id' => $userId,
            'cancels_at' => Carbon::now()->subDay(),
        ];

        $this->subscriptionRepositoryMock->expects($this->once())
            ->method('update')
            ->with($userId, $subscriptionId);

        $this->eventDispatcherMock->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(SubscriptionCancelled::class));

        $this->subscriptionCenter->updateSubscriptionCancellationStatus($userId, $subscription);
    }

    #[Test]
    public function test_process_user_active_subscriptions_status()
    {
        $userId = 'user123';
        $activeSubscription = new Subscription([
            'id' => 'subscription123',
            'user_id' => $userId,
        ]);

        $this->subscriptionRepositoryMock->expects($this->once())
            ->method('findUserActiveSubscription')
            ->with($userId)
            ->willReturn($activeSubscription);

        $this->subscriptionCenter->setSubscription($activeSubscription);

        $result = $this->subscriptionCenter->processUserActiveSubscriptionsStatus($userId);

        $this->assertSame($activeSubscription, $result);
    }

    #[Test]
    public function test_process_user_inactive_subscriptions_status()
    {
        $userId = 'user123';
        $inactiveSubscriptionId = 'subscription123';
        $inactiveSubscription = new Subscription([
            'user_id' => $userId,
            'activated_at' => Carbon::now()->subDay(),
        ]);
        $inactiveSubscription->id = $inactiveSubscriptionId;

        $this->subscriptionRepositoryMock->expects($this->once())
            ->method('findUserInactiveSubscription')
            ->with($userId)
            ->willReturn($inactiveSubscription);

        $this->subscriptionRepositoryMock->expects($this->once())
            ->method('update')
            ->with($userId, $inactiveSubscriptionId);

        $this->subscriptionCenter->setSubscription($inactiveSubscription);

        $result = $this->subscriptionCenter->processUserInactiveSubscriptionsStatus($userId);

        $this->assertSame($inactiveSubscription, $result);
    }

    protected function setUp(): void
    {
        $this->subscriptionRepositoryMock = $this->createMock(SubscriptionRepository::class);
        $this->eventDispatcherMock = $this->createMock(Dispatcher::class);
        $this->subscriptionCenter = new SubscriptionCenter($this->subscriptionRepositoryMock, $this->eventDispatcherMock);
    }

    protected function tearDown(): void
    {
        $this->subscriptionCenter = null;
        $this->subscriptionRepositoryMock = null;
        $this->eventDispatcherMock = null;
    }
}
