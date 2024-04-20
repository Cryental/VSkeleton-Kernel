<?php

namespace Volistx\FrameworkKernel\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\Test;
use Volistx\FrameworkKernel\AuthValidationRules\Users\CountryValidationRule;
use Volistx\FrameworkKernel\Database\Factories\PersonalTokenFactory;
use Volistx\FrameworkKernel\Database\Factories\UserFactory;
use Volistx\FrameworkKernel\Enums\AccessRule;
use Volistx\FrameworkKernel\Facades\PersonalTokens;

class CountryValidationRuleTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_access_allowed_when_country_rule_is_none()
    {
        $user = $this->generateUser();
        $personalToken = $this->generatePersonalToken($user->id, [
            'country_rule' => AccessRule::NONE,
        ]);

        // Use an actual request object
        $request = new Request();
        $countryValidationRule = new CountryValidationRule($request);

        // Set up the facade to return the expected token
        PersonalTokens::shouldReceive('getToken')->andReturn($personalToken);

        // Perform the validation
        $result = $countryValidationRule->validate();

        $this->assertTrue($result);
    }

    private function generateUser()
    {
        return UserFactory::new()->create();
    }

    private function generatePersonalToken(string $user_id, array $inputs)
    {
        return PersonalTokenFactory::new()->create(
            array_merge(
                ['user_id' => $user_id],
                $inputs
            )
        );
    }
}
