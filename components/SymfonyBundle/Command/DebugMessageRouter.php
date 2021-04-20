<?php

namespace Morebec\Orkestra\SymfonyBundle\Command;

use Morebec\Orkestra\Messaging\Routing\MessageRouteInterface;
use Morebec\Orkestra\Messaging\Routing\MessageRouterInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Console Command used to debug message routes.
 */
class DebugMessageRouter extends Command
{
    protected static $defaultName = 'orkestra:messaging:debug-router';

    /**
     * @var MessageRouterInterface
     */
    private $router;

    public function __construct(MessageRouterInterface $router)
    {
        $this->router = $router;
        parent::__construct();
    }

    protected function configure()
    {
        $this->addArgument('messageTypeName', InputArgument::OPTIONAL, '(Optional) filter the results by a certain messageTypeName');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $messageTypeNameFilter = $input->getArgument('messageTypeName');

        $routes = $this->router->getRoutes();

        // Organize routes per message.
        $messages = [];

        /** @var MessageRouteInterface $route */
        foreach ($routes as $route) {
            $messageTypeName = $route->getMessageTypeName();

            if ($messageTypeNameFilter && !str_contains($messageTypeName, $messageTypeNameFilter)) {
                continue;
            }

            if (!\array_key_exists($messageTypeName, $messages)) {
                $messages[$messageTypeName] = [];
            }
            $messages[$messageTypeName][] = $route->getMessageHandlerClassName().'::'.$route->getMessageHandlerMethodName();
        }

        if ($messages) {
            foreach ($messages as $message => $messageRoutes) {
                $io->text("<info>{$message}</info>");
                $io->listing($messageRoutes);
            }
        } else {
            if ($messageTypeNameFilter) {
                $warningMessage = sprintf('No routes matching message "%s".', $messageTypeNameFilter);
            } else {
                $warningMessage = 'No routes are defined';
            }

            $io->warning($warningMessage);
        }

        return self::SUCCESS;
    }
}
