<?php

namespace Morebec\Orkestra\SymfonyBundle;

use Morebec\Orkestra\SymfonyBundle\Module\SymfonyOrkestraModuleConfiguratorInterface;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

/**
 * Orkestra's implementation of the symfony kernel to allow a custom
 * Module and configuration system.
 */
class OrkestraKernel extends BaseKernel
{
    use MicroKernelTrait;

    /**
     * List of the Module Configurators.
     *
     * @var SymfonyOrkestraModuleConfiguratorInterface[]
     */
    private $moduleConfigurators;

    /**
     * Indicates if the module configurators were loaded or not.
     *
     * @var bool
     */
    private $moduleConfiguratorsLoaded;

    public function __construct(string $environment, bool $debug)
    {
        parent::__construct($environment, $debug);
        $this->moduleConfigurators = [];
        $this->moduleConfiguratorsLoaded = false;
    }

    /**
     * Returns the list of module configurators.
     *
     * @return SymfonyOrkestraModuleConfiguratorInterface[]
     */
    public function getModuleConfigurators(): array
    {
        if (!$this->moduleConfiguratorsLoaded) {
            $this->loadModuleConfigurators();
        }

        return $this->moduleConfigurators;
    }

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $projectDir = $this->getProjectDir();
        $container->import($projectDir.'/config/{packages}/*.yaml');
        $container->import($projectDir.'/config/{packages}/'.$this->environment.'/*.yaml');

        if (is_file(\dirname(__DIR__).'/config/services.yaml')) {
            $container->import($projectDir.'/config/services.yaml');
            $container->import($projectDir.'/config/{services}_'.$this->environment.'.yaml');
        } elseif (is_file($path = \dirname(__DIR__).'/config/services.php')) {
            (require $path)($container->withPath($path), $this);
        }

        $this->configureModuleContainer($container);
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $projectDir = $this->getProjectDir();
        $routes->import($projectDir.'/config/{routes}/'.$this->environment.'/*.yaml');
        $routes->import($projectDir.'/config/{routes}/*.yaml');

        if (is_file(\dirname(__DIR__).'/config/routes.yaml')) {
            $routes->import($projectDir.'/config/routes.yaml');
        } elseif (is_file($path = \dirname(__DIR__).'/config/routes.php')) {
            (require $path)($routes->withPath($path), $this);
        }

        $this->configureModuleRoutes($routes);
    }

    /**
     * Configures the container using the Orkestra Module Configurators.
     */
    protected function configureModuleContainer(ContainerConfigurator $container): void
    {
        foreach ($this->getModuleConfigurators() as $moduleConfigurator) {
            $moduleConfigurator->configureContainer($container);
        }
    }

    /**
     * Configures the routes using the Orkestra Module Configurators.
     */
    protected function configureModuleRoutes(RoutingConfigurator $routes)
    {
        foreach ($this->getModuleConfigurators() as $moduleConfigurator) {
            $moduleConfigurator->configureRoutes($routes);
        }
    }

    /**
     * Loads the module configurators and saves them in memory.
     */
    protected function loadModuleConfigurators(): void
    {
        $modulesFile = $this->getProjectDir().'/config/modules.php';

        if (!file_exists($modulesFile)) {
            throw new \LogicException(sprintf('The modules configuration file was not found at "%s"', $modulesFile));
        }

        $contents = require $modulesFile;
        foreach ($contents as $class => $envs) {
            if ($envs[$this->environment] ?? $envs['all'] ?? false) {
                $configuratorClassName = "{$class}";

                if (!class_exists($configuratorClassName)) {
                    throw new \RuntimeException(sprintf('Configurator "%s" could not be loaded. Did you correctly registered it with the autoloader?', $class));
                }

                if (isset($this->moduleConfigurators[$configuratorClassName])) {
                    throw new \LogicException(sprintf('Trying to load two module configurators with the same name: "%s"', $configuratorClassName));
                }

                /** @var SymfonyOrkestraModuleConfiguratorInterface $configurator */
                $configurator = new $configuratorClassName();
                $this->moduleConfigurators[$configuratorClassName] = $configurator;
            }
        }

        $this->moduleConfiguratorsLoaded = true;
    }
}
