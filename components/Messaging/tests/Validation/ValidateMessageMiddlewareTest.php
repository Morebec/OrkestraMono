<?php

namespace Tests\Morebec\Orkestra\Messaging\Validation;

use Morebec\Orkestra\Messaging\MessageBusResponseStatusCode;
use Morebec\Orkestra\Messaging\MessageHandlerResponse;
use Morebec\Orkestra\Messaging\MessageHeaders;
use Morebec\Orkestra\Messaging\MessageInterface;
use Morebec\Orkestra\Messaging\Validation\InvalidMessageResponse;
use Morebec\Orkestra\Messaging\Validation\MessageValidationError;
use Morebec\Orkestra\Messaging\Validation\MessageValidationErrorList;
use Morebec\Orkestra\Messaging\Validation\MessageValidatorInterface;
use Morebec\Orkestra\Messaging\Validation\ValidateMessageMiddleware;
use PHPUnit\Framework\TestCase;

class ValidateMessageMiddlewareTest extends TestCase
{
    public function testInvoke(): void
    {
        $validator = $this->createValidator();

        $middleware = new ValidateMessageMiddleware([$validator]);

        $headers = new MessageHeaders();
        $nextMiddleware = static function (MessageInterface $message, MessageHeaders $headers) {
            return new MessageHandlerResponse('handlerName', MessageBusResponseStatusCode::SUCCEEDED());
        };

        $message = $this->createMessage();
        $response = $middleware($message, $headers, $nextMiddleware);

        $this->assertInstanceOf(InvalidMessageResponse::class, $response);
        $this->assertEquals($response->getStatusCode(), MessageBusResponseStatusCode::INVALID());
        $this->assertInstanceOf(MessageValidationErrorList::class, $response->getPayload());
    }

    private function createMessage(): MessageInterface
    {
        return new class() implements MessageInterface {
            /** @var string */
            public $property;

            public static function getTypeName(): string
            {
                return 'message';
            }
        };
    }

    private function createValidator(): MessageValidatorInterface
    {
        return new class() implements MessageValidatorInterface {
            public function validate(MessageInterface $message, MessageHeaders $headers): MessageValidationErrorList
            {
                $errors = new MessageValidationErrorList();

                if (!$message->property) {
                    $errors->add(new MessageValidationError(
                        'Property must not be blank',
                        'property',
                        $message->property
                    ));
                }

                return $errors;
            }

            public function supports(MessageInterface $message, MessageHeaders $headers): bool
            {
                return true;
            }
        };
    }
}
