<?php

namespace Volistx\FrameworkKernel\Tests;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Volistx\FrameworkKernel\Database\Factories\AccessTokenFactory;
use Volistx\FrameworkKernel\Database\Factories\UserFactory;
use Volistx\FrameworkKernel\DataTransferObjects\UserDTO;
use Volistx\FrameworkKernel\Helpers\SHA256Hasher;
use Volistx\FrameworkKernel\Models\User;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function authorize_create_user_permissions()
    {
        $key = Str::random(64);
        $token = $this->GenerateAccessToken($key);

        $this->TestPermissions($token, $key, 'post', '/sys-bin/admin/users', [
            'user:*' => 201,
            '' => 401,
            'user:create' => 201,
        ]);
    }

    private function GenerateAccessToken(string $key): Collection|Model
    {
        $salt = Str::random(16);

        $token = AccessTokenFactory::new()
            ->create(['key' => substr($key, 0, 32),
                'secret' => SHA256Hasher::make(substr($key, 32), ['salt' => $salt]),
                'secret_salt' => $salt,
                'permissions' => ['user:*'],]);

        UserFactory::new()->create();

        return $token;
    }

    #[Test]
    public function create_user(): void
    {
        $key = Str::random(64);
        $this->GenerateAccessToken($key);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $key,
        ])->post('/sys-bin/admin/users');

        $response->assertStatus(201);
    }

    #[Test]
    public function authorize_update_user_permissions()
    {
        $key = Str::random(64);
        $token = $this->GenerateAccessToken($key);
        $user = User::query()->first();

        $this->TestPermissions($token, $key, 'patchJson', "/sys-bin/admin/users/$user->id", [
            'user:*' => 200,
            '' => 401,
            'user:update' => 200,
        ]);
    }

    #[Test]
    public function update_user(): void
    {
        $key = Str::random(64);
        $this->GenerateAccessToken($key);
        $user = User::query()->first();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $key,
            'Content-Type' => 'application/json',
        ])->patchJson("/sys-bin/admin/users/$user->id", [
            'is_active' => false,
        ]);

        $user = User::query()->first();
        $response->assertStatus(200);
        $response->assertJson(UserDTO::fromModel($user)->GetDTO());
    }

    #[Test]
    public function authorize_delete_user_permissions()
    {
        $key = Str::random(64);
        $token = $this->GenerateAccessToken($key);
        $user = User::query()->first();

        $this->TestPermissions($token, $key, 'delete', "/sys-bin/admin/users/$user->id", [
            '' => 401,
            'user:delete' => 204,
        ]);
    }

    #[Test]
    public function delete_user(): void
    {
        $key = Str::random(64);
        $this->GenerateAccessToken($key);
        $user = User::query()->first();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $key,
            'Content-Type' => 'application/json',
        ])->delete("/sys-bin/admin/users/$user->id");

        $response->assertStatus(204);
    }

    #[Test]
    public function authorize_get_user_permissions()
    {
        $key = Str::random(64);
        $token = $this->GenerateAccessToken($key);
        $user = User::query()->first();

        $this->TestPermissions($token, $key, 'get', "/sys-bin/admin/users/$user->id", [
            'user:*' => 200,
            '' => 401,
            'user:view' => 200,
        ]);
    }

    #[Test]
    public function get_user(): void
    {
        $key = Str::random(64);
        $this->GenerateAccessToken($key);
        $user = User::query()->first();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $key,
            'Content-Type' => 'application/json',
        ])->get("/sys-bin/admin/users/$user->id");

        $response->assertStatus(200);
        $response->assertJson(UserDTO::fromModel($user)->GetDTO());
    }
}
