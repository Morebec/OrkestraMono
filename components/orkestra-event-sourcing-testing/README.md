# Event Sourcing Testing
Utilities to easily test event sourced systems based on Orkestra and Symfony using
a fluent API.

## Installation
```shell
composer require morebec/orkestra-orkestra-exceptions
```

## Usage:
```php
class RegisterCustomerCommandHandlerTest extends EventSourcedTestCase
{
    /**
     * @return void
     * @throws Throwable
     */
    public function test(): void
    {
        $customerId = uniqid('cus_', true);
        $this
            ->defineScenario()
            ->givenCurrentDateIs(new DateTime("2020-01-01"))
            ->whenCommand(from(static function() use ($customerId) {
                $command = new RegistercustomerCommand();
                $command->customerId = $customerId;

                return $command;
            }))
            ->messageBusShouldRespondWithPayload(null)
            ->messageBusShouldRespondWithStatusCodeSucceeded()
            ->expectSingleEventSameAs(from(static function() use ($customerId) {
                $event = new CustomerRegisteredEvent();
                $event->customerId = $customerId;

                return $event;
            }))
            ->runScenario()
        ;
    }
}
```
