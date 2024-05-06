<?php

namespace Volistx\FrameworkKernel\Tests;

use PHPUnit\Framework\Attributes\Test;
use Volistx\FrameworkKernel\Helpers\AccessTokensCenter;

class AccessTokensCenterTest extends TestCase
{
    private ?AccessTokensCenter $accessTokenCenter;

    #[Test]
    public function test_set_token()
    {
        $token = 'test_token';
        $this->accessTokenCenter->setToken($token);

        $this->assertEquals($token, $this->accessTokenCenter->getToken());
    }

    #[Test]
    public function test_get_token()
    {
        $token = 'test_token';
        $this->accessTokenCenter->setToken($token);

        $this->assertEquals($token, $this->accessTokenCenter->getToken());
    }

    protected function setUp(): void
    {
        $this->accessTokenCenter = new AccessTokensCenter();
    }

    protected function tearDown(): void
    {
        $this->accessTokenCenter = null;
    }
}
