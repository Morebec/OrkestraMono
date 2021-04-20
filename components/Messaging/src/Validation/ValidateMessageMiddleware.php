<?php

namespace Morebec\Orkestra\Messaging\Validation;

use Morebec\Orkestra\Collections\Collection;
use Morebec\Orkestra\Messaging\MessageBusResponseInterface;
use Morebec\Orkestra\Messaging\MessageHeaders;
use Morebec\Orkestra\Messaging\MessageInterface;
use Morebec\Orkestra\Messaging\Middleware\MessageBusMiddlewareInterface;

class ValidateMessageMiddleware implements MessageBusMiddlewareInterface
{
    /**
     * @var MessageValidatorInterface[]
     */
    private $validators;

    public function __construct(iterable $validators = [])
    {
        $this->validators = [];
        foreach ($validators as $validator) {
            $this->addValidator($validator);
        }
    }

    public function __invoke(MessageInterface $message, MessageHeaders $headers, callable $next): MessageBusResponseInterface
    {
        $validators = new Collection($this->validators);

        $errors = $validators
            // Filter validators supporting this message
            ->filter(static function (MessageValidatorInterface $validator) use ($message, $headers) {
                return $validator->supports($message, $headers);
            })
            // Of these, validate all and return their error lists
            ->map(static function (MessageValidatorInterface $validator) use ($message, $headers) {
                return $validator->validate($message, $headers);
            })
            // Filter the error lists that are not empty
            ->filter(static function (MessageValidationErrorList $errors) {
                return !$errors->isEmpty();
            })
            // Finally, merge all errors into a single collection of errors
            ->flatten();

        if (!$errors->isEmpty()) {
            // Convert this collection of errors back into a MessageValidationErrorList and pass this in the response.
            return new InvalidMessageResponse(new MessageValidationErrorList($errors));
        }

        return $next($message, $headers);
    }

    public function addValidator(MessageValidatorInterface $validator): void
    {
        $this->validators[] = $validator;
    }
}
