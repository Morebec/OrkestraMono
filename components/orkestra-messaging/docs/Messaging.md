# Messaging
*This document explains the core concepts that make up the Messaging Component and how it should be used.*

The Messaging component provides functionality to route Messages to Handlers in a uniform way. 
It is responsible for routing these messages from one component to others. It serves as a way to implement Pub/Sub
in an application. See the [Mediator pattern](https://en.wikipedia.org/wiki/Mediator_pattern) for more information.

In a system allowing users to register themselves to have access to some services, when a new user registers an account, 
many things are bound to happen: A "Welcome" transactional email can be sent, the user in question can be added to
a Marketing email list or subscribed to the company's CRM, statistics can be compiled regarding the registration for company reports etc.

These use cases can be numerous. In a traditional non-message based system, these operations would likely happen in a single 
Web Controller. However, this has a lot of drawbacks and shortcomings:
- This can very quickly make the controller handle too many responsibilities,
- One of these operation could fail, leaving the system in an inconsistent state, e.g. user was registered, but not added to CRM, or Welcome Email not send.
- If we ever allow a different client such as a CLI application, Android App, or a completely different system, to use the feature, the logic would need to be duplicated
in different locations.
- Unit testing all these operations would become harder and one would be forced to test the whole work of the controller as a single unit, instead of on a per-operation basis.

This is where Messaging Patterns can come to the rescue. Messaging allows to have a decoupled system, 
as well as providing extensibility in how these messages travel from one point to another when needed. 

In essence, a messaging mechanism means that the flow of a program would instead be changed as follows:
- A `Message Producer` (Such as a Controller, or a GRPC endpoint)  will produce a `Message` that will be sent, and handled by a `Message Handler`.

This is somewhat similar to the HTTP protocol where a Client (Message Producer)  sends a Request (Message) to a server  (Message Handler) that handles it 
and returns a response indicating how the handling of the request went.

For people unfamiliar with this type of programming, this can look overly complex at first. 
However, it provides powerful possibilities:
- Message publishers do not need to keep track of all the other components interested in a given message **improving decoupling**. Meaning that a Controller or multiple controllers 
  can send the message, a GRPC endpoint, a CLI application without duplicating the code for handling a complex flow of operations.
- Messages become first class citizen of the source code allowing them to be **logged, validated, filtered, traced or stored for later processing (asynchronous processing)**.
- Provides the possibility to **easily distribute these messages to other remote systems**, if required.

In a complex system there can be a lot of different types of messages.
In CQRS based applications there are usually three types that are defined: **Commands**, **Queries** and **Events**.

The messaging component provides a few core interfaces for these concepts:
- `MessageInteface` used to indicate that a given class is a messages.
- `MessageHandlerInterface` used to indicate that a given class is a message handler.

> There is no interface for the Message Producers as a Message can be produced by a lot of different components in an application
> and does not require any specific differentiation or indicators.
> The important pieces of that contract are the messages themselves and their handlers.

## Domain Layer
In the most common implementation of DDD based applications, the way the other layers (**Application Layer** and **Infrastructure Layer**) of an application usually 
communicate with the **Domain Layer** is through a messaging mechanism.

For this reason a number of the base interfaces have their Domain flavored versions:
- `DomainMessageInterface` used to indicate that a given class is a domain messages.
- `DomainMessageHandlerInterface` used to indicate that a given class is a domain message handler.

**However, you can still use the component for other purposes beyond the domain layer, such as Event Publishing, or providing hooks into the flow of some components.**

## Types of Domain Messages
Out of the box, the Messaging component defines four types of specific messages:
- `DomainCommandInterface` represents a Command in a CQRS sense and extends `DomainMessageInterface`. 
- `DomainEventInterface` represents an Event in an Event-Driven Programming/Event Sourcing sense and extends `DomainMessageInterface`
- `DomainQueryInterface` represents a Query in a CQRS sense and extends `DomainMessageInterface`.
- `TimeoutInterface` represents time based messages, i.e. messages that should be sent in the future (more on this in later sections).

These messages have very specific intents and meaning and cannot be used interchangeably.
From a conceptual point of view, a message is immutable and cannot be changed once created.
Indeed, this is because they represent specific intents or facts.

> In a few circumstances, it can make sense to alter messages. Some of these use cases are outlined in later sections
> such as [Middleware](#Middleware) and [Interceptors](#Message Interceptors) for various uses cases.

### Domain Commands
Domain Commands represent a desire or intent to do something in the system. (e.g. Registering a user account, activating/deactivating it etc).
They can be implemented using the `DomainCommandInterface` that inherits the `DomainMessageInterface`.
For example, we might have `RegisterUserAccount`, `OrderProduct`, `CancelOrder` etc.

> Best Practice: Only store primitive values in Commands for serialization purposes, also consider Commands as Immutable.

Domain Commands are **always sent to a single destination** which is a Domain Command Handler (implementing the `DomainCommandHandlerInterface`).

```php
use Morebec\Orkestra\Messaging\Domain\Command\DomainCommandInterface;

class RegisterUserAccountCommand implements DomainCommandInterface
{
    /** @var string */
    public $emailAddress;
    
    /** @var string */
    public $password;
    
    public static function getTypeName(): string 
    {
        return 'user_account.register';        
    }
}
```

### Domain Queries
Queries represent a request for information on something. 
They can be implemented using the `DomainQueryInterface` that inherits the `DomainMessageInterface`.
For example, we might have `GetRegisteredUsersDuringTimeFrame`, `GetCanceledOrders`, `GetMostExpensiveOrder` etc.

> Best Practice: Only store primitive values in Queries for serialization purposes, also consider Queries as Immutable.

Queries are **always sent to a single destination** which is a Query Handler (implementing the `DomainQueryHandlerInterface`).

```php
use Morebec\Orkestra\Messaging\Domain\Query\DomainQueryInterface;

class GetRegisteredUsersDuringTimeFrameQuery implements DomainQueryInterface
{
    /** @var Date */
    public $startDate;
    
    /** @var Date */
    public $endDate;
    
    public static function getTypeName(): string 
    {
        return 'user_account.registered_during_time_frame';        
    }
}
```

### Domain Events
Domain Events are a very different type of message. They conceptually represent things that happened in the past and are immutable facts.
They can be implemented using the `DomainEventInterface` that inherits the `DomainMessageInterface`.
For example, we might have `UserAccountRegistered`, `ProductOrdered`, `OrderCancelled` etc.

> Best Practice: Only store primitive values in Events for serialization purposes, also since they represent things that already happened, 
> consider Events as Immutable.

In terms of messaging, they can get sent to **multiple locations** (in contrast to Commands and Queries) depending on the processes in place.
For example, they can be stored in a database or a file storage, sent over the network etc.

They are handled Asynchronously (since events are after the fact occurrences) by Domain Event Handlers implementing the `DomainEventHandlerInterface`.

```php
use Morebec\Orkestra\Messaging\Domain\Event\DomainEventInterface;

class UserAccountRegisteredEvent implements DomainEventInterface
{
    /** @var string */
    public $emailAddress;
    
    /** @var string */
    public $password;
    
    public static function getTypeName(): string 
    {
        return 'user_account.registered';        
    }
}
```

## Message Bus
Messages are good, but we need a way to send these messages to their interested message handlers.
One way could be to directly call the message handler:

```php
class UserController 
{
  public function register(Request $request) {
      $registerAccountCommand = new RegisterAccountCommand (
          $request->post->get('emailAddress'),
          $request->post->get('password')
      );
      $this->registerAccountCommandHandler->handle($registerAccountCommand);
  }
}
```

This can be effective for very small projects, however, as explained earlier, it presents important shortcomings:
- If multiple parts of the system need to register a user account,
  they would need to have access to the handler in all those places.
- If we ever want to add logging before and after processing a given command, we'd need to manually add all that code
  everywhere this communication takes place.
- There is no easy way to reroute the command to a more specialized handler such as a `CustomerSpecificAccountRegistrationHandler`,
  unless we bloat our code with `if` statements, again in all the places where this communication happens.
- We might want to send this command instead to a remote location, or AMQP, this would require updating all the locations where this takes place.

A better option is to use the Mediator Pattern, which essentially prescribes hiding the actual
communication of a message to a handler behind a dedicated service.

To this purpose, this component provides an interface that has precisely this responsibility: `MessageBusInterface`.

The role of this interface is to effectively route a message from its producer to its handler(s), while providing ways to hook into
this routing process, in order to do custom work before and after a message is handled.
It also returns a response, that serves as a form of acknowledgement or result of operation.

> **Note**: There is an interface `DomainMessageBusInterface` that can serve to work with an implementation of a Message Bus that only accepts `DomainMessageInterface`
> for communicating exclusively with the domain layer.
> A Default `MessageBus` implementation is available out of the box. The same goes for the `DomainmessageBus`.
> However, nothing prevents you from using a single `MessageBusInterface` for all the messages if it better suits your case.

### Middleware
In order to allow for customization of their behaviour, the `MessageBusInterface` and `DomainMessageBusInterface` 
prescribe the use of `MessageBusMiddlewareInterface` which are services that can hook into the process
of sending a message to their subscribed handlers by performing tasks before and after a message is handled.

These message sending operations can be nested depending on the operational flow of the domain. 

(E.g. `Command` => `Command Handler => Event(s)` => `Event Handler(s)`).

The `MessageBusInterface` and the `DomainMessageBusInterface` also provide a way to send metadata along with the message to possibly alter the behaviour
of the message bus and the handlers. This is done using the `MessageHeaders` and `DomainMessageHeaders` object.

> The messaging component proposes a few middleware out of the box:
> - `LoggerMiddleware` which logs whenever a message is received by the bus as well as the response that was returned.
> - `MessageBusContextMiddleware` which allows populating an `MessageBusContext` that describes the current processing of a message, such as the current message, the nesting depth etc.
> - `RouteMessageMiddleware` which is responsible for resolving the different message handlers that should receive the message (more on that in the next section).
> - `HandleMessageMiddleware` which is responsible for calling the message handlers as resolved by the router, while providing additional hooks to 
    > do work before and after a message is handled by a handler (more on this in the next few sections).
> `AuthorizeMessageMiddleware` which allows deciding, if a message is allowed to be processed or not currently.
> `ValidateMessageMiddleware` which allows to validate a message and its attributes before processing.
> `MessagingTransformationMiddleware` which allows to transform a message or response, for example to reduce the information returned based on the role of the user.

Using this middleware system, it is easy to implement advanced use cases such as sending messages to remote services, performing monitoring and analytics,
authentication, validation etc.

Here's an example of a middleware that logs the type of messages being sent:
```php
class LoggerMessageBusMiddleware implements MessageBusMiddlewareInterface
{
    /** @var LoggerInterface */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function handle(MessageInterface $message, MessageHeaders $headers, callable $next): MessageBusResponseInterface
    {
        $messageId = $headers->get(MessageHeaders::MESSAGE_ID);

        $this->logger->info('[Message Bus] Sending message "{messageType}" ...', [
            'messageId' => $messageId,
            'messageType' => $message::getTypeName()
        ]);

        $response = $next($message, $headers);

        $this->logger->info('[Message Bus] Message Sent "{messageType}".', [
            'messageId' => $messageId,
            'messageType' => $message::getTypeName()
        ]);

        return $response;
    }
}
```

### Message Routing
The Routing of messages is done through a service responsible for keeping a registry of the routes
where a given message can be sent. It is used by the Message Bus for it to determine where to send a given message.
This is the role of the `MessageRouterInterface` which also has a companion middleware the `RouteMessageMiddleware`
responsible for actually resolving the routes when messages are sent through the bus.

TODO: Examples registering routes.

### Message Interception: MessageHandlerInterceptors.
Sometimes depending on the needs of the application, it might be required to intercept, filter or simply eavesdrop on a message.
Such cases can be useful for *authentication*, *authorization*, or *logging purposes* as a few examples.

As already explained, this can be accomplished with the use of Middleware.
However, there is also another way to accomplish the same goal using `MessageHandlerInterceptor`s.

Interceptors are services that can act right before a message is sent to a *specific* handler and right after a response was received by this handler.
This is another way where *validation*, *authentication*, *authorization*, or *tenant specific logic* can be effectively implemented.

Here's an example of a simple tenant specific interceptor:

```php
/**
 * Interceptor that logs before a message is sent to a handler and after a response is received from that handler.
 */
class CustomerXYZMessageHandlerInterceptor implements MessageHandlerInterceptorInterface
{
    /**
     * @var CustomerXYZRegisterUserAccountCommandHandler
     */
    private $registerUserAccountHandler;
 
    public function __construct(CustomerXYZRegisterUserAccountCommandHandler $registerUserAccountHandler) 
    {
        $this->registerUserAccountHandler = $registerUserAccountHandler;
    }
    public function beforeHandle(MessageHandlerInterceptionContext $context): void
    {
        $messageHeaders = $context->getMessageHeader();
        if ($messageHeaders->get(MessageHeaders::TENANT_ID) !== 'XYZ') {
            return;
        }
       
       // To replace the handler
       $handler = $context->getMessageHandlerMethodName(); 
       if ($handler instanceof RegisterUserAccountCommandHandler) {
            $context->replaceMessageHandler($this->registerUserAccountHandler, $context->getMessageHandlerMethodName());
            
            // to replace a method
            $context->replaceMessageHandler($context->getMessageHandler(), 'newMethodName');
            
            // if one wants to skip the processing of the handler completely, use the `skipMessageHandler` method
            $context->skipMessageHandler(null);
       }
    }

    public function afterHandle(MessageHandlerInterceptionContext $context): void
    {
        // to replace a response
        $context->replaceResponse(/* your new response here. */ );
    }
}
```

> Out of the box Orkestra provides a few useful interceptors:
> `LoggingMessageHandlerInterceptor` which log before and after each message handler.

> #### Message Handler Interceptor vs Message Bus Middleware
> Both interceptors and middleware are capable of altering the execution flow of the message bus.
> For example there is the `AuthorizeMessageMiddleware` for authorization or the `ValidateMessageMiddleware`.
> Which one to choose highly depends on your goals, but as a rule of thumb:
> - If you want to alter the behaviour of a specific handler or group of handlers -> `MessageHandlerInterceptor`. (E.g. tenant specific handlers.)
> - If you want to alter the behaviour based on the message itself with disregard to the resolved message handlers -> `MessageBusMiddleware`. (E.g. Validation.)
> 
> One other thing to take into account is the fact that middleware is further from the execution flow of the message handlers
> therefore they can allow to "fail fast", that is why it is advised to perform validation as middleware, whereas Transaction Management (SQL) could be performed
> as an interceptor before and after each handler.

#### Message Handling
Messages are handled by Handlers. Since we define by default three types of domain messages, there are three types of message handlers out of the box:
- Command Handlers -> Handle Command messages to perform changes in the domain and implement the `DomainCommandHandlerInterface`
- Event Handlers -> Handle Event messages to trigger side effects and implement the `DomainEventHandlerInterface`
- Query Handlers -> Handle Query messages to return information and implement the `DomainQueryHandlerInterface`

From an implementation point of view, creating a message handlkers only requries to implement the  MessageHandlerInterface`
and having a public method with a single argument being the typed message:

```php
class RegisterUserCommandHandler implements DomainCommandHandlerInterface
{
    public function __invoke(RegisterUserCommand $command): void
    {
        // Perform work here.
    }
}
```
> The name of the method is irrelevant for the the `RouteMessageMiddleware` and `HandleMessageMiddleware`. They only expect
> the method to be public and have a single argument being the typed message to be handled.

The `RouteMessageMiddleware` and `HandleMessageMiddleware` also support adding a second parameter to the signature of the handling method
in order to provide the `MessageHeaders`:

```php
class RegisterUserCommandHandler implements DomainCommandHandlerInterface
{
    public function __invoke(RegisterUserCommand $command, MessageHeaders $headers): void
    {
        // Perform work here.
    }
}
```

## Timeouts
In some occasions you might have business rules that need to be executed in the future after a certain time has elapsed.
For example, one could have a rule that says 5 days before an invoice is due, email the person that must pay it.

For these specific use cases the Messaging Component provides the concept of Timeouts. Timeouts represented by the `TimeoutInterface` are a specific type of message that represent 
actions or processes that should be triggered in the future at a specific date and time.
Such processes can be scheduled and handled by MessageHandlers or ProcessManagers.

In their structure timeouts must have a unique `Identifier` as well as an `endsAt` field that indicates when the timeout ends:

```php
class InvoiceDueReminderTimeout extends AbstractTimeout 
{
    /** @var string */
    public $invoiceId;
    
    public function __construct(string $invoiceId, DateTime $invoiceDueDate) 
    {
        // Invoice ID is used as the id of the timeout, as well as being saved as part
        // of the message payload.
        // The second parameter of the `AbstractTimeout::__construct` is the endsAt of the timeout which is the moment
        // at which it will be triggered, in this example 5 days before the due date of the invoice.
        parent::__construct($invoiceId, $invoiceDueDate->subDays(5));
        $this->invoiceId = $invoiceId;
    }
    
    public static function getTypeName()
    {
        return 'timeout.invoice.over_due_reminder';    
    }
}
```

Then using the same logic as any other type of message you implement, can be notified through the message bus of these messages:

```php
class InvoiceDueReminderProcessor implements TimeoutHandlerInterface
{
    public function onInvoiceDueTimeout(InvoiceDueReminderTimeout $timeout)
    {
        // Here you could
        // - send a command,
        // - update an event sourced aggregate to have a new event etc.
        //
    }
}
```

### Scheduling a Timeout
To schedule a timeout, one can use the `TimeoutManagerInterface`:

```php
public function handleCommand(Command $command): void
{
    // ...  
    $this->timeoutManager->schedule(new InvoiceDueReminderTimeout($invoice->getId(), $invoice->getDueDate()));
}
```

### Canceling a Timeout
There are cases where you might want to cancel a timeout, for example if the person paid our invoice before the reminder,
we shouldn't email them:

```php
public function onInvoicePaid(InvoicePaidEvent $event): void
{
    // ...  
    if ($this->clock->now()->isBefore($invoice->getDueDate()->subDays(5))) {
        // The timeout we had setup had the invoice id as its Timeout id.
        $this->timeoutManager->cancel($invoice->getId()); 
    }
}
```

### Asynchronous processing
From an infrastructural point of view, in order for a Timeout to be dispatched on the bus at the right time, you need to set up a process that asynchronously
checks the `TimeoutStorageInterface` which is responsible for persisting timeouts until they are triggered. 
This is done through two services:
- `TimeoutProcessInterface` which is responsible for finding the timeouts that are ready to be triggered at the current time and delegating the work of actually publishing
  the timeouts to a `TimeoutPublisherInterface`
- `TimeoutPublisherInterface` which is responsible for sending the timeouts to the interested subscribed handlers.

> If you are familiar with the `EventSourcing` component this **processor + publisher** API works exactly 
> like the `EventProcessorInterface` and `EventPublisherInterface`.

To allow this, the messaging component provides a `PollingTimeoutProcessor` that allows to continuously poll the
`TimeoutStorageInterface` at a configurable interval as well as a `MessageBusTimeoutPublisher` that allows sending these timeouts
to the message bus as well as a configurable retry strategy in cases of errors.

All you need to do if using this component outside the Orkestra framework is to do the following:

```php
// Publisher (Retry Strategy is available in the morebec/orkestra-retry component.)

// This strategy attempts sending the timeout on the bus at most 5 times with a delay of 1 second between each attempt.
$retryStrategy = RetryStrategy::create()
                    ->maximumAttempts(5)
                    ->retryAfterDelay(1000)
;
$publisher = new MessageBusTimeoutPublisher($messageBus, $retryStrategy);

// Processor
$options = (new PollingTimeoutProcessor())
    ->withName('default_processor')
    ->withDelay(60 * 1000) // Will poll the storage every minute
;
$processor = new PollingTimeoutProcessor(new SystemClock(), $publisher, $storage, $options);

// Will continuously check poll the storage for new messages.
$processor->start();
```

> The messaging component does not provide any implementation of a Timeout Storage, as this is dependent on the technology
> used by your application. Fir a PostgreSQL implementation, see the `morebec/orkestra-postgresql-timeout-storage` composer package.

#### Handling Exceptions
By design the `Message Bus` is **expected not to throw exceptions related to the handling of a message**.
As part of their contract they must always return a `message bus response` indicating success or failure. 
The default implementation provides a set of Status Codes that allows the event producers to easily distinguish between 
Domain Exceptions (Business specific exceptions) and Technical Errors.
Allowing the Application Layer to better handle these failures with greater granularity.
It also provides the benefit of not disrupting the execution flow of the `Message Bus` when different message sending operations are nested. 
As an example, one Event Handler failing should not prevent other event handlers of doing their work if they are called as part of the same context/message.

As such, exceptions should always be swallowed by these components so that they can return a response indicating those exceptions.

This is achieved with middleware.

The `HandleMessageMiddleware` wraps the execution of message handlers catching any exception and building a response
describing the problem.

This allows to freely use exceptions in message handlers so that doing so will only interrupt the current handler's scope.