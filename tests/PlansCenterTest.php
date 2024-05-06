<?php

namespace Volistx\FrameworkKernel\Tests;

use PHPUnit\Framework\Attributes\Test;
use Volistx\FrameworkKernel\Helpers\PlansCenter;

class PlansCenterTest extends TestCase
{
    private ?PlansCenter $plansCenter;

    #[Test]
    public function test_set_plan()
    {
        $plan = 'my_plan';
        $this->plansCenter->setPlan($plan);

        $this->assertEquals($plan, $this->plansCenter->getPlan());
    }

    #[Test]
    public function test_get_plan()
    {
        $plan = 'my_plan';
        $this->plansCenter->setPlan($plan);

        $this->assertEquals($plan, $this->plansCenter->getPlan());
    }

    protected function setUp(): void
    {
        $this->plansCenter = new PlansCenter();
    }

    protected function tearDown(): void
    {
        $this->plansCenter = null;
    }
}
