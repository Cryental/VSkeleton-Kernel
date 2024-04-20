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
    public function testSetToken()
    {
        $token = 'test_token';
        $this->accessTokenCenter->setToken($token);

        $this->assertEquals($token, $this->accessTokenCenter->getToken());
    }

    #[Test]
    public function testGetToken()
    {
        $token = 'test_token';
        $this->accessTokenCenter->setToken($token);

        $this->assertEquals($token, $this->accessTokenCenter->getToken());
    }
}