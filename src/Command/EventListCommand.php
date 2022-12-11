<?php declare(strict_types=1);

namespace Mcx\EventList\Command;

use Shopware\Core\Framework\Adapter\Console\ShopwareStyle;
use Shopware\Core\Kernel;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


class EventListCommand extends Command
{


    protected static $defaultName = 'mcx:event:list';

    private Kernel $kernel; // used for getting project dir
    private ShopwareStyle $io;

    public function __construct(Kernel $kernel, string $name = null)
    {
        parent::__construct($name);
        $this->kernel = $kernel;
    }


    public function configure(): void
    {
        $this->addOption('info');
        $this->addOption('batch-size', 'b', InputOption::VALUE_REQUIRED, 'Number of entities per iteration', '50');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new ShopwareStyle($input, $output);
        $this->io->info("TODO...");

        return Command::SUCCESS;
    }

}