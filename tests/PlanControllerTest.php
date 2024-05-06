<?php

namespace Volistx\FrameworkKernel\Tests;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Volistx\FrameworkKernel\Database\Factories\AccessTokenFactory;
use Volistx\FrameworkKernel\Database\Factories\PlanFactory;
use Volistx\FrameworkKernel\DataTransferObjects\PlanDTO;
use Volistx\FrameworkKernel\Helpers\SHA256Hasher;
use Volistx\FrameworkKernel\Models\PersonalToken;
use Volistx\FrameworkKernel\Models\Plan;
use Volistx\FrameworkKernel\Repositories\PlanRepository;

class PlanControllerTest extends TestCase
{
    use RefreshDatabase;

    private PlanRepository $planRepository;

    #[Test]
    public function authorize_create_plan_permissions()
    {
        $key = Str::random(64);
        $token = $this->generateAccessToken($key);

        $this->TestPermissions($token, $key, 'postJson', '/sys-bin/admin/plans', [
            '' => 401,
            'plans:create' => 201,
        ], [
            'name' => 'plan name',
            'tag' => 'plan-tag',
            'description' => 'plan description',
            'data' => ['requests' => 500],
            'price' => 10,
            'tier' => 1,
            'custom' => false,
        ]);
    }

    private function generateAccessToken(string $key): Collection|Model
    {
        $salt = Str::random(16);

        $token = AccessTokenFactory::new()
            ->create(['key' => substr($key, 0, 32),
                'secret' => SHA256Hasher::make(substr($key, 32), ['salt' => $salt]),
                'secret_salt' => $salt,
                'permissions' => ['plans:*'],]);

        PlanFactory::new()->create();

        return $token;
    }

    #[Test]
    public function create_plan(): void
    {
        $key = Str::random(64);
        $this->generateAccessToken($key);
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $key,
        ])->postJson('/sys-bin/admin/plans', [
            'name' => 'plan name',
            'tag' => 'plan-tag',
            'description' => 'plan description',
            'data' => ['requests' => 500],
            'price' => 10,
            'tier' => 1,
            'custom' => false,
        ]);

        $response->assertStatus(201);
    }

    #[Test]
    public function authorize_update_plan_permissions()
    {
        $key = Str::random(64);
        $token = $this->generateAccessToken($key);
        $plan = Plan::query()->first();

        $this->TestPermissions($token, $key, 'patchJson', "/sys-bin/admin/plans/$plan->id", [
            'plans:*' => 200,
            '' => 401,
            'plans:update' => 200,
        ]);
    }

    #[Test]
    public function update_plan(): void
    {
        $key = Str::random(64);
        $this->generateAccessToken($key);
        $plan = Plan::query()->first();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $key,
            'Content-Type' => 'application/json',
        ])->patchJson("/sys-bin/admin/plans/$plan->id", [
            'name' => 'updated name',
        ]);

        $plan = Plan::query()->first();
        $response->assertStatus(200);
        $response->assertJson(PlanDTO::fromModel($plan)->getDTO());
    }

    #[Test]
    public function authorize_delete_plan_permissions()
    {
        $key = Str::random(64);
        $token = $this->generateAccessToken($key);
        $plan = Plan::query()->first();

        $this->testPermissions($token, $key, 'delete', "/sys-bin/admin/plans/$plan->id", [
            '' => 401,
            'plans:delete' => 204,
        ]);
    }

    #[Test]
    public function delete_plan(): void
    {
        $key = Str::random(64);
        $this->generateAccessToken($key);
        $plan = Plan::query()->first();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $key,
            'Content-Type' => 'application/json',
        ])->delete("/sys-bin/admin/plans/$plan->id");

        $response->assertStatus(204);
        self::assertNull(PersonalToken::query()->first());
    }

    #[Test]
    public function authorize_get_plan_permissions()
    {
        $key = Str::random(64);
        $token = $this->generateAccessToken($key);
        $plan = Plan::query()->first();

        $this->testPermissions($token, $key, 'get', "/sys-bin/admin/plans/$plan->id", [
            'plans:*' => 200,
            '' => 401,
            'plans:view' => 200,
        ]);
    }

    #[Test]
    public function get_plan(): void
    {
        $key = Str::random(64);
        $this->generateAccessToken($key);
        $plan = Plan::query()->first();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $key,
            'Content-Type' => 'application/json',
        ])->get("/sys-bin/admin/plans/$plan->id");

        $response->assertStatus(200);
        $response->assertJson(PlanDTO::fromModel($plan)->getDTO());
    }

    #[Test]
    public function authorize_get_plans_permissions()
    {
        $key = Str::random(64);
        $token = $this->generateAccessToken($key);

        $this->testPermissions($token, $key, 'get', '/sys-bin/admin/plans', [
            'plans:*' => 200,
            '' => 401,
            'plans:view-all' => 200,
        ]);
    }

    #[Test]
    public function get_plans(): void
    {
        $key = Str::random(64);
        $this->generateAccessToken($key);
        $plansCount = 50;
        PlanFactory::new()->count($plansCount)->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $key,
            'Content-Type' => 'application/json',
        ])->get('/sys-bin/admin/plans');

        $response->assertStatus(200);
        self::assertCount($plansCount, json_decode($response->getContent())->items);
    }
}
