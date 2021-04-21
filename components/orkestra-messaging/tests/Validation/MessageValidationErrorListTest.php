<?php

namespace Tests\Morebec\Orkestra\Messaging\Validation;

use Morebec\Orkestra\Messaging\Validation\MessageValidationErrorInterface;
use Morebec\Orkestra\Messaging\Validation\MessageValidationErrorList;
use PHPUnit\Framework\TestCase;

class MessageValidationErrorListTest extends TestCase
{
    public function testAdd()
    {
        $errors = new MessageValidationErrorList();

        $errors->add($this->createError('Invalid test'));

        $this->assertFalse($errors->isEmpty());
    }

    public function testMerge()
    {
        $errorsA = new MessageValidationErrorList([
            $this->createError('1'),
            $this->createError('2'),
        ]);
        $errorsB = new MessageValidationErrorList([
            $this->createError('3'),
        ]);

        $errors = $errorsB->merge($errorsA);

        $this->assertCount(3, $errors);
    }

    private function createError(string $message): MessageValidationErrorInterface
    {
        return new class($message) implements MessageValidationErrorInterface {
            /**
             * @var string
             */
            private $message;

            public function __construct(string $message)
            {
                $this->message = $message;
            }

            public function getMessage(): string
            {
                return $this->message;
            }

            public function property(): string
            {
                return 'property';
            }

            public function getValue()
            {
                return 'hello-world';
            }
        };
    }
}
