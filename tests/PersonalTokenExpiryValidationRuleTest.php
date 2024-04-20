<?php

namespace Volistx\FrameworkKernel\Tests;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\Test;
use Volistx\FrameworkKernel\AuthValidationRules\Users\PersonalTokenExpiryValidationRule;
use Volistx\FrameworkKernel\Database\Factories\PersonalTokenFactory;
use Volistx\FrameworkKernel\Database\Factories\UserFactory;
use Volistx\FrameworkKernel\Facades\Messages;
use Volistx\FrameworkKernel\Facades\PersonalTokens;

class PersonalTokenExpiryValidationRuleTest extends TestCase
{
    #[Test]
    public function test_access_allowed_when_token_not_expired()
    {
        $user = $this->generateUser(true);
        $personalToken = $this->generatePersonalToken($user->id, [
            'expires_at' => Carbon::now()->addHour()->toDateTimeString(),
        ]);

        PersonalTokens::shouldReceive('getToken')->andReturn($personalToken);

        $request = new Request(); // Use a real request instance
        $expiryValidationRule = new PersonalTokenExpiryValidationRule($request);

        $result = $expiryValidationRule->validate();

        $this->assertTrue($result);
    }

    #[Test]
    public function test_access_allowed_when_token_has_no_expiry()
    {
        $user = $this->generateUser(true);
        $personalToken = $this->generatePersonalToken($user->id, [
            'expires_at' => null,
        ]);

        PersonalTokens::shouldReceive('getToken')->andReturn($personalToken);

        $request = new Request(); // Use a real request instance
        $expiryValidationRule = new PersonalTokenExpiryValidationRule($request);

        $result = $expiryValidationRule->validate();

        $this->assertTrue($result);
    }

    #[Test]
    public function test_access_denied_when_token_expired()
    {
        $user = $this->generateUser(true);
        $personalToken = $this->generatePersonalToken($user->id, [
            'expires_at' => Carbon::now()->subHour()->toDateTimeString(),
        ]);

        PersonalTokens::shouldReceive('getToken')->andReturn($personalToken);

        $request = new Request(); // Use a real request instance
        $expiryValidationRule = new PersonalTokenExpiryValidationRule($request);

        $result = $expiryValidationRule->validate();

        $this->assertEquals(
            [
                'message' => Messages::E403(trans('volistx::token.expired')),
                'code' => 403,
            ],
            $result
        );
    }

    private function generateUser(bool $is_active): Collection|Model
    {
        return UserFactory::new()->create([
            'is_active' => $is_active,
        ]);
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
