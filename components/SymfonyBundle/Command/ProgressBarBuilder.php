<?php

namespace Morebec\OrkestraSymfonyBundle\Command;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

class ProgressBarBuilder
{
    private function __construct()
    {
    }

    public static function modern(OutputInterface $output, int $max = 0, float $minSecondsBetweenRedraws = 1 / 25): ProgressBar
    {
        $bar = new ProgressBar($output, $max, $minSecondsBetweenRedraws);
        $bar->setFormat("\n %current%/%max% %bar% %percent:3s%% \n%elapsed:6s%/%estimated:-6s% %memory:6s%");
        $bar->setBarCharacter('<info>█</info>');
        $bar->setEmptyBarCharacter('░');
        $bar->setProgressCharacter('<info>█</info>');

        return $bar;
    }

    public static function classic(OutputInterface $output, int $max = 0, float $minSecondsBetweenRedraws = 1 / 25): ProgressBar
    {
        $bar = new ProgressBar($output, $max, $minSecondsBetweenRedraws);
        $bar->setFormat('debug');
        $bar->setBarCharacter('<info>|</info>');
        $bar->setEmptyBarCharacter('-');
        $bar->setProgressCharacter('<info>|</info>');

        return $bar;
    }
}
