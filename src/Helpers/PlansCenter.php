<?php

namespace Volistx\FrameworkKernel\Helpers;

class PlansCenter
{
    private mixed $plan = null;

    /**
     * Get the plan.
     *
     * @return mixed The plan
     */
    public function getPlan(): mixed
    {
        return $this->plan;
    }

    /**
     * Set the plan.
     *
     * @param mixed $plan The plan
     */
    public function setPlan(mixed $plan): void
    {
        $this->plan = $plan;
    }
}
