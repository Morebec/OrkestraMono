<?php

namespace Morebec\Orkestra\SymfonyBundle\Module;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ServiceConfigurator;

class AutoRoutedMessageHandlerServiceConfigurator
{
    public const MESSAGING_ROUTING_AUTOROUTE_TAG = 'orkestra.messaging.routing.autoroute';
    public const MESSAGING_ROUTING_DISABLED_METHOD_TAG = 'orkestra.messaging.routing.disabled_method';

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
    }

    /**
     * Disables a certain messaging method route.
     *
     * @return $this
     */
    public function disableMethodRoute(string $methodName): self
    {
        $this->delegate->tag(self::MESSAGING_ROUTING_DISABLED_METHOD_TAG, ['name' => $methodName]);

        return $this;
    }

    public function public(): self
    {
        $this->delegate->public();

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
