<?php

namespace Morebec\Orkestra\SymfonyBundle\Command;

use Morebec\Orkestra\Messaging\Normalization\MessageClassMapInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Console Command used to debug message routes.
 */
class DebugMessageClassMap extends Command
{
    protected static $defaultName = 'orkestra:messaging:debug-classmap';

    /**
     * @var MessageClassMapInterface
     */
    private $classMap;

    public function __construct(MessageClassMapInterface $classMap)
    {
        $this->classMap = $classMap;
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

        $mapping = $this->classMap->toArray();

        if ($mapping) {
            foreach ($mapping as $messageTypeName => $className) {
                if ($messageTypeNameFilter && !str_contains($messageTypeName, $messageTypeNameFilter)) {
                    continue;
                }
                $io->text("<info>$messageTypeName</info> => $className");
            }
        } else {
            if ($messageTypeNameFilter) {
                $warningMessage = sprintf('No mapping matching message "%s".', $messageTypeNameFilter);
            } else {
                $warningMessage = 'No mappings are defined';
            }

            $io->warning($warningMessage);
        }

        return self::SUCCESS;
    }
}
