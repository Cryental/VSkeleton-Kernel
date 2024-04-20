<?php

namespace Volistx\FrameworkKernel\Tests;

use PHPUnit\Framework\Attributes\Test;
use Volistx\FrameworkKernel\Helpers\PersonalTokensCenter;

class PersonalTokensCenterTest extends TestCase
{
    private ?PersonalTokensCenter $personalTokensCenter;

    protected function setUp(): void
    {
        $this->personalTokensCenter = new PersonalTokensCenter();
    }

    protected function tearDown(): void
    {
        $this->personalTokensCenter = null;
    }

    #[Test]
    public function test_set_token()
    {
        $token = 'my_personal_token';
        $this->personalTokensCenter->setToken($token);

        $this->assertEquals($token, $this->personalTokensCenter->getToken());
    }

    #[Test]
    public function test_get_token()
    {
        $token = 'my_personal_token';
        $this->personalTokensCenter->setToken($token);

        $this->assertEquals($token, $this->personalTokensCenter->getToken());
    }
}
