<?php

namespace Morebec\Orkestra\SymfonyBundle\Module;

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\DefaultsConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ParametersConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ServiceConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ServicesConfigurator;

/**
 * Helper class containing methods to easily configure services that are orkestra flavored.
 */
class SymfonyOrkestraModuleContainerConfigurator
{
    /** @var string */
    public const UPCASTER_TAG = 'orkestra.upcaster';

    /** @var string */
    public const MESSAGE_VALIDATOR = 'orkestra.message_validator';

    /** @var string */
    public const MESSAGE_AUTHORIZER = 'orkestra.message_authorizer';

    /** @var string */
    public const MESSAGE_HANDLER_INTERCEPTORS = 'orkestra.message_handler_interceptor';

    /**
     * @var ContainerConfigurator
     */
    private $container;

    /**
     * @var ServicesConfigurator
     */
    private $services;

    public function __construct(ContainerConfigurator $container)
    {
        $this->container = $container;
        $this->services = $container->services();
    }

    public function defaults(): DefaultsConfigurator
    {
        return $this->services->defaults();
    }

    /**
     * Adds a command handler service definition.
     * Configured as autowired, autoconfigured, public and lazy.
     */
    public function commandHandler(string $serviceId, string $className = null): MessageHandlerServiceConfigurator
    {
        return $this->messageHandler($serviceId, $className);
    }

    /**
     * Adds an event handler definition.
     * Configured as autowired, autoconfigured, public and lazy.
     */
    public function eventHandler(string $serviceId, ?string $serviceClass = null): MessageHandlerServiceConfigurator
    {
        return $this->messageHandler($serviceId, $serviceClass);
    }

    /**
     * Adds a timer handler definition.
     * Configured as autowired, autoconfigured, public and lazy.
     */
    public function timerHandler(string $serviceId, ?string $serviceClass = null): MessageHandlerServiceConfigurator
    {
        return $this->messageHandler($serviceId, $serviceClass);
    }

    /**
     * Adds a Process Manager service definition.
     * Configured as autowired, autoconfigured, public and lazy.
     */
    public function processManager(string $serviceId, ?string $serviceClass = null): MessageHandlerServiceConfigurator
    {
        return $this->messageHandler($serviceId, $serviceClass);
    }

    /**
     * Adds a repository service definition.
     * Configured as autowired, autoconfigured, public and lazy.
     */
    public function repository(string $serviceId, ?string $serviceClass = null): ServiceConfigurator
    {
        return $this->service($serviceId, $serviceClass);
    }

    /**
     * Adds an query handler definition.
     * Configured as autowired, autoconfigured, public and lazy.
     */
    public function queryHandler(string $serviceId, ?string $serviceClass = null): MessageHandlerServiceConfigurator
    {
        return $this->messageHandler($serviceId, $serviceClass);
    }

    /**
     * Configures a Message Handler.
     */
    public function messageHandler(string $serviceId, ?string $serviceClass = null): MessageHandlerServiceConfigurator
    {
        $conf = new MessageHandlerServiceConfigurator(
            $this->container,
            $this->services->set($serviceId, $serviceClass),
            $serviceId
        );
        $conf->public()->lazy()->autoconfigure()->autowire();

        return $conf;
    }

    /**
     * Configures a Message Validator.
     */
    public function messageValidator(string $serviceId, ?string $serviceClass = null): ServiceConfigurator
    {
        return $this->service($serviceId, $serviceClass)->tag(self::MESSAGE_VALIDATOR);
    }

    /**
     * Configures a Message Authorizer.
     */
    public function messageAuthorizer(string $serviceId, ?string $serviceClass = null): ServiceConfigurator
    {
        return $this->service($serviceId, $serviceClass)->tag(self::MESSAGE_AUTHORIZER);
    }

    public function messageHandlerInterceptor(string $serviceId, ?string $serviceClass = null): ServiceConfigurator
    {
        return $this->service($serviceId, $serviceClass)->tag(self::MESSAGE_HANDLER_INTERCEPTORS);
    }

    /**
     * Registers an upcaster.
     */
    public function upcaster(string $serviceId, ?string $serviceClass = null): ServiceConfigurator
    {
        return $this->services->set($serviceId, $serviceClass)->tag(self::UPCASTER_TAG);
    }

    /**
     * Configures a console command.
     */
    public function consoleCommand(string $className): ServiceConfigurator
    {
        return $this->services
            ->set($className)
            ->tag('console.command')
            ;
    }

    /**
     * Registers a controller.
     */
    public function controller(string $serviceId, ?string $serviceClass = null): ServiceConfigurator
    {
        return $this->services
            ->set($serviceId, $serviceClass)
            ->tag('controller.service_arguments');
    }

    /**
     * Registers a service.
     */
    public function service(string $serviceId, ?string $serviceClass = null): ServiceConfigurator
    {
        return $this->services->set($serviceId, $serviceClass);
    }

    public function services(): ServicesConfigurator
    {
        return $this->services;
    }

    public function parameters(): ParametersConfigurator
    {
        return $this->container->parameters();
    }
}
