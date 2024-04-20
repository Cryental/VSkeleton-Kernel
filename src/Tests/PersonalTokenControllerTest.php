<?php

namespace Volistx\FrameworkKernel\Tests;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Volistx\FrameworkKernel\Database\Factories\AccessTokenFactory;
use Volistx\FrameworkKernel\Database\Factories\PersonalTokenFactory;
use Volistx\FrameworkKernel\Database\Factories\UserFactory;
use Volistx\FrameworkKernel\DataTransferObjects\PersonalTokenDTO;
use Volistx\FrameworkKernel\Enums\AccessRule;
use Volistx\FrameworkKernel\Enums\RateLimitMode;
use Volistx\FrameworkKernel\Helpers\SHA256Hasher;
use Volistx\FrameworkKernel\Models\PersonalToken;

class PersonalTokenControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function AuthorizeCreatePersonalTokenPermissions()
    {
        $key = Str::random(64);
        $token = $this->generateAccessToken($key, 1);
        $user = $this->generateUserWithTokens(1);

        $this->TestPermissions($token, $key, 'postJson', "/sys-bin/admin/users/$user->id/personal-tokens", [
            'personal-tokens:*' => 201,
            '' => 401,
            'personal-tokens:create' => 201,
        ], [
            'name' => 'Test Token',
            'expires_at' => null,
            'permissions' => ['*'],
            'ip_rule' => AccessRule::NONE,
            'ip_range' => [],
            'country_rule' => 0,
            'country_range' => [],
            'disable_logging' => false,
            'rate_limit_mode' => RateLimitMode::SUBSCRIPTION,
        ]);
    }

    #[Test]
    public function CreatePersonalToken(): void
    {
        $key = Str::random(64);
        $this->generateAccessToken($key, 1);
        $user = $this->generateUserWithTokens(1);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$key,
        ])->postJson("/sys-bin/admin/users/$user->id/personal-tokens", [
            'name' => 'Test Token',
            'expires_at' => null,
            'permissions' => ['*'],
            'ip_rule' => AccessRule::NONE,
            'ip_range' => [],
            'country_rule' => 0,
            'country_range' => [],
            'disable_logging' => false,
            'rate_limit_mode' => RateLimitMode::SUBSCRIPTION,
        ]);

        $response->assertStatus(201);
    }

    #[Test]
    public function AuthorizeUpdatePersonalTokenPermissions()
    {
        $key = Str::random(64);
        $token = $this->generateAccessToken($key);
        $user = $this->generateUserWithTokens(1);
        $personalToken = PersonalToken::query()->first();

        $this->TestPermissions($token, $key, 'patchJson', "/sys-bin/admin/users/$user->id/personal-tokens/$personalToken->id", [
            'personal-tokens:*' => 200,
            '' => 401,
            'personal-tokens:update' => 200,
        ], ['name' => 'Updated Token']);
    }

    #[Test]
    public function UpdatePersonalToken(): void
    {
        $key = Str::random(64);
        $this->generateAccessToken($key);
        $user = $this->generateUserWithTokens(1);
        $personalToken = PersonalToken::query()->first();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$key,
            'Content-Type' => 'application/json',
        ])->patchJson("/sys-bin/admin/users/{$user->id}/personal-tokens/{$personalToken->id}", [
            'name' => 'Updated Token',
        ]);

        $personalToken = PersonalToken::query()->first();
        $response->assertStatus(200);
        $response->assertJson(PersonalTokenDTO::fromModel($personalToken)->GetDTO());
    }

    #[Test]
    public function AuthorizeResetPersonalToken()
    {
        $key = Str::random(64);
        $token = $this->generateAccessToken($key);
        $user = $this->generateUserWithTokens(1);
        $personalToken = PersonalToken::query()->first();

        $this->TestPermissions($token, $key, 'post', "/sys-bin/admin/users/$user->id/personal-tokens/$personalToken->id/reset", [
            'personal-tokens:*' => 200,
            '' => 401,
            'personal-tokens:reset' => 200,
        ]);
    }

    #[Test]
    public function ResetPersonalToken(): void
    {
        $key = Str::random(64);
        $this->generateAccessToken($key);
        $user = $this->generateUserWithTokens(1);
        $personalToken = PersonalToken::query()->first();
        $oldKey = $personalToken->key;
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$key,
        ])->post("/sys-bin/admin/users/{$user->id}/personal-tokens/{$personalToken->id}/reset");

        $personalToken = PersonalToken::query()->first();
        $newKey = $personalToken->key;
        $response->assertStatus(200);
        self::assertNotSame($oldKey, $newKey);
    }

    #[Test]
    public function AuthorizeDeletePersonalToken()
    {
        $key = Str::random(64);
        $token = $this->generateAccessToken($key);
        $user = $this->generateUserWithTokens(1);
        $personalToken = PersonalToken::query()->first();

        $this->TestPermissions($token, $key, 'delete', "/sys-bin/admin/users/$user->id/personal-tokens/$personalToken->id", [
            '' => 401,
            'personal-tokens:delete' => 204,
        ]);
    }

    #[Test]
    public function DeletePersonalToken(): void
    {
        $key = Str::random(64);
        $this->generateAccessToken($key);
        $user = $this->generateUserWithTokens(1);
        $personalToken = PersonalToken::query()->first();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$key,
            'Content-Type' => 'application/json',
        ])->delete("/sys-bin/admin/users/{$user->id}/personal-tokens/{$personalToken->id}");

        $response->assertStatus(204);
        self::assertNull(PersonalToken::query()->first());
    }

    #[Test]
    public function AuthorizeGetPersonalToken()
    {
        $key = Str::random(64);
        $token = $this->generateAccessToken($key);
        $user = $this->generateUserWithTokens(1);
        $personalToken = PersonalToken::query()->first();

        $this->TestPermissions($token, $key, 'get', "/sys-bin/admin/users/$user->id/personal-tokens/$personalToken->id", [
            'personal-tokens:*' => 200,
            '' => 401,
            'personal-tokens:view' => 200,
        ]);
    }

    #[Test]
    public function GetPersonalToken(): void
    {
        $key = Str::random(64);
        $this->generateAccessToken($key);
        $user = $this->generateUserWithTokens(1);
        $personalToken = PersonalToken::query()->first();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$key,
        ])->get("/sys-bin/admin/users/{$user->id}/personal-tokens/{$personalToken->id}");

        $personalToken = PersonalToken::query()->first();
        $response->assertStatus(200);
        $response->assertJson(PersonalTokenDTO::fromModel($personalToken)->GetDTO());
    }

    #[Test]
    public function AuthorizeGetPersonalTokens()
    {
        $key = Str::random(64);
        $token = $this->generateAccessToken($key);
        $user = $this->generateUserWithTokens(5);

        $this->TestPermissions($token, $key, 'get', "/sys-bin/admin/users/$user->id/personal-tokens", [
            'personal-tokens:*' => 200,
            '' => 401,
            'personal-tokens:view-all' => 200,
        ]);
    }

    #[Test]
    public function GetPersonalTokens(): void
    {
        $key = Str::random(64);
        $this->generateAccessToken($key);
        $user = $this->generateUserWithTokens(5);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$key,
        ])->get("/sys-bin/admin/users/{$user->id}/personal-tokens");

        $response->assertStatus(200);
        self::assertCount(5, json_decode($response->getContent())->items);
    }

    #[Test]
    public function SyncPersonalTokens(): void
    {
        $key = Str::random(64);
        $this->generateAccessToken($key);
        $user = $this->generateUserWithTokens(1);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$key,
        ])->post("/sys-bin/admin/users/$user->id/personal-tokens/sync");

        $response->assertStatus(201);
    }

    private function generateAccessToken(string $key): Collection|Model
    {
        $salt = Str::random(16);

        return AccessTokenFactory::new()
            ->create(['key' => substr($key, 0, 32),
                'secret' => SHA256Hasher::make(substr($key, 32), ['salt' => $salt]),
                'secret_salt' => $salt,
                'permissions' => ['personal-tokens:*'], ]);
    }

    private function generateUserWithTokens($tokensCount): Collection|Model
    {
        $user = UserFactory::new()->create();

        PersonalTokenFactory::new()->count($tokensCount)->create([
            'user_id' => $user->id,
        ]);

        return $user;
    }
}
