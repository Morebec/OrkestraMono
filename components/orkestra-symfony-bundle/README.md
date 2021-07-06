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
2. Inside this directory, create a class implementing the `OrkestraModuleConfiguratorInterface`.
This class will be used by the bundle to register the service dependencies of the module with Symfony's service container
as well as the controller routes with the Symfony Router *(not to be confused with `MessageRoutes`)*.

```php
class ShippingModuleConfigurator implements OrkestraModuleConfiguratorInterface
{
    public function configureContainer(OrkestraConfiguration $conf): void
    {
        $conf->useSystemClock();
        
        // Configure the message bus
        $conf->configureMessageBus(
            (new DefaultMessageBusConfiguration())
                ->withMiddleware(YourCustomMiddleware::class)
        );
        
        // Configure the event store
        $conf->configureEventStore(
            (new EventStoreConfiguration())
                    ->usingImplementation(PostgreSqlEventStore::class)
                    ->decoratedBy(UpcastingEventStoreDecorator::class)
                    ->decoratedBy(MessageBusContextEventStoreDecorator::class)
                    ->withUpcaster(YourEventUpcaster::classs)
        );
        
        // Configure Event Processing.
        $conf->configureEventProcessing(
            (new EventProcessingConfiguration())
                ->usingEventStorePositionStorageImplementation(PostgreSqlEventStorePositionStorage::class)
                
                // Configure Projection Processing
                ->configureProjectionProcessing(
                    (new ProjectionProcessingConfiguration())
                        ->configureProjectorGroup(
                        (new ProjectorGroupConfiguration())
                            ->withName('api')
                            ->withProjector(YourProjector::class)
                        )
                )
        );

        $conf->commandHandler(ShippingMessageHandler::class)
            ->autoroute()
            ->disableMethodRoute('__invoke')
        ;

        $conf->consoleCommand(ShippingConsoleCommand::class);
        
        
        // Configure a service using Symfony's container as per usual.
        $conf->service(LoggerInterface::class, YourLogger::class)->args('%env.logDir%)');

    }

    public function configureRoutes(RoutingConfigurator $routes): void
    {
    }
}
```
> Note: The `OrkestraConfiguration` class provides utility methods that allows to fluently define services
> with a language closer to the technical requirements of Orkestra with methods such as:
> - `$config->eventHandler(/* ... */)`
> - `$config->commandHandler(/* ... */)`
> - `$config->queryHandler,(/* ... */)`
> - `$config->processManager(/* ... */)`
> - `$config->upcaster(/* ... */)`
> - `$config->repository(/* ... */)`
> - etc.
> 
> These methods are shorthands for the longer versions that require using the Configuration classes.

#### Step 2: Enable the Module
Then, enable the module by adding its Configurator to the list of registered Module Configurators in the `config/modules.php` file of your project:
```php
return [
    // ...
    ShippingModuleConfiguratorConfigurator::class => ['all' => true],
];
``` 
> Module Configurations are registered just like Symfony Bundles allowing you to provide the environment in which they should exist.
> If you need a different configurator on a per-environment basis, you can simply check for the environment using `$_ENV['APP_ENV]` in the configurators code
> or define different `ModuleConfigurator` classes that are environment specific.


### Configuring the Message Bus
The configuration of the message bus can be done using Symfony's dependency injection by providing services
and wiring them. However, this can be tedious to perform on every project.
This bundle provides an easy way to define the middleware, message interceptors and dependencies of the message
bus through a fluent API using the `MessageBusConfiguration` class:

```php
/** @var OrkestraConfiguration $configuration */
$configuration->configureMessageBus(
    (new MessageBusConfiguration())
        ->withMiddleware(YourCustomMiddleware::class)
);        
```

Alternatively, there is also an implementation of this MessageBusConfiguration
that setups up all the default middleware of Orkestra, the `DefaultMessageBusConfiguration`:

```php
/** @var OrkestraConfiguration $configuration */
$configuration->configureMessageBus(
    (MessageBusConfiguration::defaultConfiguration())
        ->withMiddleware(YourCustomMiddleware::class)
);

// Or 
$configuration->configureMessageBus(
    (DefaultMessageBusConfiguration::defaultConfiguration())
        ->withMiddleware(YourCustomMiddleware::class)
        // Command Handlers
        ->commandHandler(YourCommandHandler::class)
        // Query Handlers
        ->commandHandler(YourQueryHandler::class)
        // Event Handlers
        ->eventHandlers(YourEventHandler::class)
        // Timeout Handlers
        ->timeoutHandler(YourTimeoutHandler::class) 
        // Generic Message Handlers
        ->eventHandlers(YourEventHandler::class)
        // Message Handler Interceptors
        ->messageHandlerInterceptor(YourInterceptor::class)
        // Validators
        ->messageValidator(YourValidator::class)
        // Authorizers
        ->messageAuthorizer(YourAuthorizer::class)
        // Transformers
        ->messageTransformer(YourTransformer::class)
);
```

> The benefit of using the `DefaultMessageBusConfiguration` is that it allows to quickly set up a working
> message bus as well as simplifying the way to define message handlers so that they can be routed automatically.
> The same autoconfiguration applies for message validators, authorizers, and messaging transformers.

### Defining message bus dependencies in modules
Normally the configuration of the message bus is defined in a `Core` module that serves as a cross-cutting 
dependency-builder module, however, most message handlers are usually defined in their appropriate modules.

In the case on way to define register them with the message bus is to do the following:

```php
/** @var OrkestraConfiguration $configuration */
$configuration->getMessageBusConfiguration()
        ->withMessageHandler(YourMessageHandler::class)
;

// Alternatively using the helper methods of the OrkestraConfiguration class
// This will behind the scene find the message bus configuration and attach
// the handler to it.
$configuration
    ->messageHandler(YourMessageHandler::class)
;
```


#### Configuring the Message Normalizer
The `MessageNormalizerInterface` can be configured to receive more `NormalizerInterface` and `DenormalizerInterface` as per your needs:

```php
/** @var OrkestraConfiguration $configuration */
$configuration->getMessageBusConfiguration()
        ->configureMessageNormalizer(
            (new MessageNormalizerConfiguration())
                ->usingDefaultImplementation()
                ->withNormalizationPair(
                    YourNormalizer::class,
                    YourDenormalizer::class
                )
                // Or
                ->withNormalizer(YourNormalizer::class)
                ->withDenormalizer(YourDenormalizer::class)
        )
;

// Alternatively using the helper methods of the OrkestraConfiguration class
// This will behind the scene find the message bus configuration and attach
// the handler to it.
$configuration
    ->messageHandler(YourMessageHandler::class)
;
```


### Configuring Timeout Processing
Timeout Handlers being message handlers have to be registered with the message bus. However, they have dependencies for
infrastructure concerns such as processing that need to be defined in a separate configuration:

```php
/** @var OrkestraConfiguration $configuration */
$configuration
    ->configureTimeoutProcessing(
        (new TimeoutProcessingConfiguration())
            ->usingManagerImplementation(TimeoutManager::class)
            // Alternatively for the manager you can use the default implementation (TimeoutManager):
            ->usingDefaultManagerImplementation()
            
            // Storage
            ->usingStorageImplementation(PostgreSqlTimeoutStorage::class)
    );
```

### Configuring the Event Store
The configuration of the event store follows the same configuration principles as the message bus:

```php
/** @var OrkestraConfiguration $configuration */
$configuration->configureEventStore(
    (new EventStoreConfiguration())
            ->usingImplementation(PostgreSqlEventStore::class)
            
            // Decorators priority ordered by order of declaration.
            ->decoratedBy(UpcastingEventStoreDecorator::class)
            ->decoratedBy(MessageBusContextEventStoreDecorator::class)
            
            // The chain is done in order of declaration.
            // No need to define the UpcasterChain, it is automatically registered.
            ->withUpcaster(YourEventUpcaster::classs)
);
```
### Configuring Event Processing
```php
/** @var OrkestraConfiguration $configuration */
$configuration->cconfigureEventProcessing(
    (new EventProcessingConfiguration())
        
        // Position storage for Tracking Event Processors.
        ->usingEventStorePositionStorageImplementation(PostgreSqlEventStorePositionStorage::class)
        
        // Configure Projection Processing
        ->configureProjectionProcessing(
            (new ProjectionProcessingConfiguration())
                ->configureProjectorGroup(
                (new ProjectorGroupConfiguration())
                    ->withName('api')
                    ->withProjector(YourProjector::class)
                )
        )
);
```

#### Configuring Projectors
Projecting events is part of the event processing configuration and benefits from a tailored configuration class.
This configuration class is used in order to easily define projectors that need to be grouped together as a single processing unit.

When using the `ProjectionProcessingConfiguration` class, the groups will automatically be registered in a registry that can then be queried to dynamically resolve these groups.

One of the benefit is to be able to create a command like: `orkestra:projection-processor` console command that allows to
control the projector groups by name.

To configure the projector groups outside a core module you can do the following:
```php
// Adding a new projector group

/** @var OrkestraConfiguration $configuration */
$configuration->getProjectionProcessingConfiguration()
    ->configureProjectorGroup(
    (new ProjectorGroupConfiguration())
        ->withName('api')
        ->withProjector(YourProjector::class)
);
```

### Adding Compiler Passes
To add compiler passes, one can simply use the `OrkestraConfiguration` class:

```php
/** @var OrkestraConfiguration $configuration */
$configuration->compilerPass(new YourCompilerPass());
```
Alternatively, you can rely on Symfony's `ContainerConfigurator` to register custom `Container Extensions`. 

For more information on this please refer to the Official Symfony Documentation.

 
