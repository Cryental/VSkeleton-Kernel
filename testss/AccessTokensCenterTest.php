<?php

namespace Volistx\FrameworkKernel\Tests;

use Volistx\FrameworkKernel\Helpers\AccessTokensCenter;
use PHPUnit\Framework\Attributes\Test;

class AccessTokensCenterTest extends TestCase
{
    private ?AccessTokensCenter $accessTokenCenter;

    protected function setUp(): void
    {
        $this->accessTokenCenter = new AccessTokensCenter();
    }

    protected function tearDown(): void
    {
        $this->accessTokenCenter = null;
    }

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
}