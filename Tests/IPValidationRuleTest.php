<?php

namespace Volistx\FrameworkKernel\Tests;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\Test;
use Volistx\FrameworkKernel\AuthValidationRules\Users\IPValidationRule;
use Volistx\FrameworkKernel\Database\Factories\PersonalTokenFactory;
use Volistx\FrameworkKernel\Database\Factories\UserFactory;
use Volistx\FrameworkKernel\Enums\AccessRule;
use Volistx\FrameworkKernel\Facades\Messages;
use Volistx\FrameworkKernel\Facades\PersonalTokens;
use Wikimedia\IPSet;

class IPValidationRuleTest extends TestCase
{
    #[Test]
    public function test_access_allowed_when_i_p_rule_is_none()
    {
        $user = $this->GenerateUser();
        $token = $this->generatePersonalToken($user->id, [
            'ip_rule' => AccessRule::NONE,
        ]);
        PersonalTokens::shouldReceive('getToken')->andReturn($token);

        $request = new Request(); // Real request instance
        $ipValidationRule = new IPValidationRule($request);

        $result = $ipValidationRule->validate();

        $this->assertTrue($result);
    }

    #[Test]
    public function test_access_denied_when_i_p_blacklisted()
    {
        $user = $this->GenerateUser();
        $token = $this->generatePersonalToken($user->id, [
            'ip_rule' => AccessRule::BLACKLIST,
            'ip_range' => ['192.168.1.1', '192.168.2.1'],
        ]);
        PersonalTokens::shouldReceive('getToken')->andReturn($token);

        $request = new Request();
        $request->server->set('REMOTE_ADDR', '192.168.1.1');
        $ipValidationRule = new IPValidationRule($request);
        $this->app->instance(IPSet::class, $this->createIPSetMock(true));

        $result = $ipValidationRule->validate();

        $this->assertEquals(
            [
                'message' => Messages::E403(trans('volistx::service.not_allowed_to_access_from_your_ip')),
                'code' => 403,
            ],
            $result
        );
    }

    #[Test]
    public function test_access_denied_when_i_p_whitelisted()
    {
        $user = $this->GenerateUser();
        $token = $this->generatePersonalToken($user->id, [
            'ip_rule' => AccessRule::WHITELIST,
            'ip_range' => ['192.168.1.1', '192.168.2.1'],
        ]);
        PersonalTokens::shouldReceive('getToken')->andReturn($token);

        $request = new Request();
        $request->server->set('REMOTE_ADDR', '111.111.1.1');
        $ipValidationRule = new IPValidationRule($request);
        $this->app->instance(IPSet::class, $this->createIPSetMock(false));

        $result = $ipValidationRule->validate();

        $this->assertEquals(
            [
                'message' => Messages::E403(trans('volistx::service.not_allowed_to_access_from_your_ip')),
                'code' => 403,
            ],
            $result
        );
    }

    private function GenerateUser(): Collection|Model
    {
        return UserFactory::new()->create();
    }

    private function GeneratePersonalToken(string $user_id, array $inputs): Collection|Model
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

    private function createIPSetMock(bool $matchResult): IPSet
    {
        $ipSetMock = $this->createMock(IPSet::class);
        $ipSetMock->method('match')->willReturn($matchResult);
        return $ipSetMock;
    }
}
