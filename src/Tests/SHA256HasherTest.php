<?php

namespace Volistx\FrameworkKernel\Tests;

use PHPUnit\Framework\Attributes\Test;
use Volistx\FrameworkKernel\Helpers\SHA256Hasher;

class SHA256HasherTest extends TestCase
{
    #[Test]
    public function testInfo()
    {
        $hashedValue = password_hash('password123', PASSWORD_DEFAULT);
        $info = SHA256Hasher::info($hashedValue);

        $this->assertIsArray($info);
        $this->assertArrayHasKey('algo', $info);
        $this->assertArrayHasKey('algoName', $info);
        $this->assertArrayHasKey('options', $info);
    }

    #[Test]
    public function testMake()
    {
        $value = 'password123';
        $hashedValue = SHA256Hasher::make($value);

        $this->assertIsString($hashedValue);
        $this->assertNotEmpty($hashedValue);
    }

    #[Test]
    public function testCheck()
    {
        $value = 'password123';
        $hashedValue = SHA256Hasher::make($value);

        $this->assertTrue(SHA256Hasher::check($value, $hashedValue));
    }
}
