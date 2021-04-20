<?php

namespace Morebec\Orkestra\SymfonyBundle\Module;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ServiceConfigurator;

/**
 * Service Configurator tailored for {@link MessageHandlerInterface}.
 */
class MessageHandlerServiceConfigurator
{
    public const MESSAGING_HANDLER_TAG = 'orkestra.messaging.message_handler';

    /**
     * @var ServiceConfigurator
     */
    protected $delegate;

    /**
     * @var ContainerConfigurator
     */
    protected $container;

    /**
     * @var string
     */
    protected $serviceClass;

    public function __construct(ContainerConfigurator $container, ServiceConfigurator $delegate, string $serviceClass)
    {
        // These parameters are fake we will just work with the delegate.
        $this->delegate = $delegate;
        $this->container = $container;
        $this->serviceClass = $serviceClass;
        $this->delegate->tag(self::MESSAGING_HANDLER_TAG);
    }

    /**
     * Allows to automatically register this message handler with the router.
     */
    public function autoroute(): AutoRoutedMessageHandlerServiceConfigurator
    {
        $this->delegate->tag(AutoRoutedMessageHandlerServiceConfigurator::MESSAGING_ROUTING_AUTOROUTE_TAG);

        return new AutoRoutedMessageHandlerServiceConfigurator($this->container, $this->delegate, $this->serviceClass);
    }

    public function public(): self
    {
        $this->delegate->public();

        return $this;
    }

    public function autowire(): self
    {
        $this->delegate->autowire();

        return $this;
    }

    public function autoconfigure(): self
    {
        $this->delegate->autoconfigure();

        return $this;
    }

    public function tag(string $name, array $attributes = []): self
    {
        $this->delegate->tag($name, $attributes);

        return $this;
    }

    public function decorate(?string $id, string $renamedId = null, int $priority = 0, int $invalidBehavior = ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE): self
    {
        $this->delegate->decorate($id, $renamedId, $priority, $invalidBehavior);

        return $this;
    }

    public function deprecate(): self
    {
        $this->delegate->deprecate();

        return $this;
    }

    public function args(array $arguments): self
    {
        $this->delegate->args($arguments);

        return $this;
    }

    public function alias(string $id, string $referenceId): self
    {
        $this->delegate->alias($id, $referenceId);

        return $this;
    }

    public function lazy(bool $lazy = true): self
    {
        $this->delegate->lazy($lazy);

        return $this;
    }

    public function share(bool $shared = true): self
    {
        $this->delegate->share($shared);

        return $this;
    }
}
