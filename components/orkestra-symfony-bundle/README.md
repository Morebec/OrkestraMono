# Orkestra Symfony Bundle
This bundle integrates the Orkestra Framework with Symfony 5.

[![Build Status](https://travis-ci.com/Morebec/OrkestraSymfonyBundle.svg?branch=v1.x)](https://travis-ci.com/Morebec/OrkestraSymfonyBundle)

## Installation

### Applications that use Symfony Flex

Open a command console, enter your project directory and execute:

```console
$ composer require morebec/orkestra-symfony-bundle
```

### Applications that don't use Symfony Flex

#### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require morebec/orkestra-symfony-bundle
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

#### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `config/bundles.php` file of your project:

```php
return [
    // ...
    OrkestraSymfonyBundle::class => ['all' => true]
];

```

#### Step 3: Add an Adapter
For persistence and infrastructure concerns, Orkestra requires adapters.

Install one of the adapters and register the classes of the adapter as services in a Module Configurator 
(see below for more information).
 
## Usage

### Creating a Module for a Bounded Context
A Module is a logical separation of the source code. This is usually linked to the separations of DDD Bounded Contexts 
according to the context map.
Although Symfony provides a Bundle System, This bundle's Module System is tailored for the dependency injection needs of Orkestra
based application. It provides ways to configure services using pure PHP with a fluent API which simplifies greatly this process
while still allowing all the power of Symfony.

#### Step 1: Create a configuration class for the Module
1. Create a directory under `src` with the name of your Module. E.g. `Shipping'.
2. Inside this directory, create a class implementing the `SymfonyOrkestraModuleConfiguratorInterface`.
This class will be used by the bundle to register the service dependencies of the module with Symfony's service container
as well as the controller routes with the Symfony Router *(not to be confused with `MessageRoutes`)*.
```php
class SandboxModuleConfiguratorConfigurator implements SymfonyOrkestraModuleConfiguratorInterface
{
    public function configureContainer(ContainerConfigurator $container): void
    {
        $conf = new SymfonyOrkestraModuleContainerConfigurator($container);

        $conf->services()
            ->defaults()
            ->autowire()
            ->autoconfigure()
        ;

        $conf->commandHandler(SandBoxMessageHandler::class)
            ->autoroute()
            ->disableMethodRoute('__invoke')
        ;

        $conf->consoleCommand(SandboxConsoleCommand::class);

    }

    public function configureRoutes(RoutingConfigurator $routes): void
    {
    }
}
```
> Note: The bundle provides a class `SymfonyOrkestraModuleContainerConfigurator` that allows to fluently define services
> with a language closer to the technical requirements of Orkestra with methods such as:
> - `$config->eventHandler(/* ... */)`
> - `$config->commandHandler(/* ... */)`
> - `$config->queryHandler,(/* ... */)`
> - `$config->processManager(/* ... */)`
> - `$config->messageValidator(/* ... */)`
> - `$config->messageAuthorizer(/* ... */)`
> - `$config->messageHandlerInterceptor(/* ... */)`
> - `$config->upcaster(/* ... */)`
> - `$config->repository(/* ... */)`
> - etc.
> 
> It can be instantiated easily by doing the following:
> ```php
> $config = new SymfonyOrkestraModuleContainerConfigurator($container); 
> ```

#### Step 2: Enable the Module
Then, enable the module by adding its Configurator to the list of registered Module Configurators in the `config/modules.php` file of your project:
```php
return [
    // ...
    SandboxModuleConfiguratorConfigurator::class => ['all' => true],
];
``` 
> Module Configurations are registered just like Symfony Bundles allowing you to provide the environment in which they should exist.
> If you need a different configurator on a per-environment basis, you can simply check for the environment using `$_ENV['APP_ENV]` in the configurators code
> or define different `ModuleConfigurator` classes that are environment specific.

### Adding Compiler Passes
The module configurator do not currently have a specific method to add compiler passes (as per Symfony,s limitations), instead, you can rely on the `ContainerConfigurator`
to register custom `Container Extensions`. 

For more information on this please refer to the Official Symfony Documentation.

### Configuring the Message Bus
The `MessageBusInterface` can be configured to receive more Middleware.
By default, it receives the out of the box Orkestra middleware in the following order:

- `LoggerMiddleware`
- `BuildMessageBusContextMiddleware`
- `ValidateMessageMiddleware`
- `AuthorizeMessageMiddleware`
- `RouteMessageMiddleware`
- `HandleMessageMiddleware`

To specify the middleware in a custom fashion, you can do *one* the following in your module configurators
:
```php
// Specify middleware as constructor arguments:
$services->get(MessageBusInterface::class)->args([/* custom middleware */]);

// To append a specific middleware
$services->get(MessageBusInterface::class)->call('appendMiddleware', [/* custom middleware */]);

// To prepend  a specific middleware
$services->get(MessageBusInterface::class)->call('prependMiddleware', [/* custom middleware */]);

// To completely replace middleware (this would be similar to the constructor arguments, although less performant).
$services->get(MessageBusInterface::class)->call('replaceMiddleware', [/* custom middleware */]);
```

#### Configuring the Message Router
By default, this bundle does not load any routes, unless you either tag your message handler services with both `orkestra.messaging.message_handler` and `orkestra.messaging.routing.autoroute` 
or using if you are using the `SymfonyOrkestraModuleContainerConfigurator` utility with a call to the `autoroute` method:

```php
// The autoroute method call is what autoregisters the route.
$config->commandHandler(YourHandler::class)->autoroute();
```

When doing this, the bundle will inspect the methods of these services, and infer routes from them.

The message bus implementation provided by this bundle will also cache these automatically resolved routes
in Symfony's cache for every subsequent requests, once the container is built.

If you want to instead provide the routes manually, simply create a configurator class as per [Symfony's documentation on Service configurators](https://www.google.com/url?sa=t&rct=j&q=&esrc=s&source=web&cd=&ved=2ahUKEwjj3bGpo-_vAhUGpZ4KHXq6CgoQFjABegQIAhAD&url=https%3A%2F%2Fsymfony.com%2Fdoc%2Fcurrent%2Fservice_container%2Fconfigurators.html&usg=AOvVaw38HZe2zCFZ6WrboNk28cVu). 


#### Configuring the Message Normalizer
The `MessageNormalizerInterface` can be configured to receive more `NormalizerInterface` and `DenormalizerInterface` as per your needs.
Simply create a configurator class as per [Symfony's documentation on Service configurators](https://www.google.com/url?sa=t&rct=j&q=&esrc=s&source=web&cd=&ved=2ahUKEwjj3bGpo-_vAhUGpZ4KHXq6CgoQFjABegQIAhAD&url=https%3A%2F%2Fsymfony.com%2Fdoc%2Fcurrent%2Fservice_container%2Fconfigurators.html&usg=AOvVaw38HZe2zCFZ6WrboNk28cVu). 

### Adding Validators
The `SymfonyOrkestraModuleContainerConfigurator` provides a method to automatically register validators: 
`SymfonyOrkestraModuleContainerConfigurator::messageValidator`.

This is done behind the scene through tags and autowiring of tagged services.

Orkestra does not provide a default implementation of a Validation library simply a set of interfaces.
Here's an example of a validator using Symfony's Validator:

```php
class SandboxCommandValidator implements MessageValidatorInterface
{
    /**
     * @var ValidatorInterface
     */
    private $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    public function validate(essageInterface $message, MessageHeaders $headers): MessageValidationErrorList
    {
        /** @var SandboxCommand $command */
        $command = $message;


        $metadata = $this->validator->getMetadataFor(SandboxCommand::class);
        $metadata->addPropertyConstraint('userId', new Assert\NotBlank());

        $errors = $this->validator->validate($command);

        $c = (new Collection($errors))
            ->map(static function (ConstraintViolation $v) {
                return new MessageValidationError($v->getMessage(), $v->getPropertyPath(), $v->getInvalidValue());
            })
            ->flatten()
        ;

        return new MessageValidationErrorList($c);
    }

    public function supports(MessageInterface $message, MessageHeaders $headers): bool
    {
        return $message instanceof SandboxCommand;
    }
}
```

## Adding Authorizers
To add authorizers, you can follow the exact same process as the Validators.
