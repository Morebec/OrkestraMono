<?php

namespace Morebec\Orkestra\SymfonyBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Abstract implementation of a Console Command that supports interruptions using the "pcntl" extension.
 *
 * It requires the implementor calling `pcntl_dispatch`. In the case of looping commands, it requires
 * calling this function in every loop cycle.
 */
abstract class AbstractInterruptibleConsoleCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!\extension_loaded('pcntl')) {
            throw new \RuntimeException('Extension not loaded: "pcntl"');
        }

        $interruptionCallback = function (int $signo, $siginfo) use ($input, $output): void {
            $this->onInterruption($input, $output);
        };
        pcntl_signal(\SIGINT, $interruptionCallback);
        pcntl_signal(\SIGHUP, $interruptionCallback);
        pcntl_signal(\SIGTERM, $interruptionCallback);
    }

    /**
     * Handler called when a pcntl_signal is received.
     *
     * @param $input
     * @param $output
     */
    abstract protected function onInterruption($input, $output): void;
}
