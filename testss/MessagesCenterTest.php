<?php

namespace Volistx\FrameworkKernel\Tests;

use PHPUnit\Framework\Attributes\Test;
use Volistx\FrameworkKernel\Helpers\MessagesCenter;

class MessagesCenterTest extends TestCase
{
    #[Test]
    public function test_error()
    {
        $type = 'InvalidParameter';
        $info = 'Some information about the error';
        $expectedResult = [
            'error' => [
                'type' => $type,
                'info' => $info,
            ],
        ];

        $messagesCenter = new MessagesCenter();
        $result = $messagesCenter->Error($type, $info);

        $this->assertEquals($expectedResult, $result);
    }

    #[Test]
    public function test_e400()
    {
        $error = 'Some specific error message';
        $expectedResult = [
            'error' => [
                'type' => 'InvalidParameter',
                'info' => $error,
            ],
        ];

        $messagesCenter = new MessagesCenter();
        $result = $messagesCenter->E400($error);

        $this->assertEquals($expectedResult, $result);
    }

    #[Test]
    public function test_e401()
    {
        $error = 'Some specific error message';
        $expectedResult = [
            'error' => [
                'type' => 'Unauthorized',
                'info' => $error,
            ],
        ];

        $messagesCenter = new MessagesCenter();
        $result = $messagesCenter->E401($error);

        $this->assertEquals($expectedResult, $result);
    }

    #[Test]
    public function test_e403()
    {
        $error = 'Some specific error message';
        $expectedResult = [
            'error' => [
                'type' => 'Forbidden',
                'info' => $error,
            ],
        ];

        $messagesCenter = new MessagesCenter();
        $result = $messagesCenter->E403($error);

        $this->assertEquals($expectedResult, $result);
    }

    #[Test]
    public function test_e404()
    {
        $error = 'Some specific error message';
        $expectedResult = [
            'error' => [
                'type' => 'NotFound',
                'info' => $error,
            ],
        ];

        $messagesCenter = new MessagesCenter();
        $result = $messagesCenter->E404($error);

        $this->assertEquals($expectedResult, $result);
    }

    #[Test]
    public function test_e409()
    {
        $error = 'Some specific error message';
        $expectedResult = [
            'error' => [
                'type' => 'Conflict',
                'info' => $error,
            ],
        ];

        $messagesCenter = new MessagesCenter();
        $result = $messagesCenter->E409($error);

        $this->assertEquals($expectedResult, $result);
    }

    #[Test]
    public function test_e429()
    {
        $error = 'Some specific error message';
        $expectedResult = [
            'error' => [
                'type' => 'RateLimitReached',
                'info' => $error,
            ],
        ];

        $messagesCenter = new MessagesCenter();
        $result = $messagesCenter->E429($error);

        $this->assertEquals($expectedResult, $result);
    }

    #[Test]
    public function test_e500()
    {
        $error = 'Some specific error message';
        $expectedResult = [
            'error' => [
                'type' => 'Unknown',
                'info' => $error,
            ],
        ];

        $messagesCenter = new MessagesCenter();
        $result = $messagesCenter->E500($error);

        $this->assertEquals($expectedResult, $result);
    }
}
