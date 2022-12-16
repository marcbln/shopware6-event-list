<?php declare(strict_types=1);

namespace Mcx\EventList\Command;

use Mcx\EventList\Util\EventFinder;
use Mcx\EventList\Util\UtilStringArray;
use Shopware\Core\Framework\Adapter\Console\ShopwareStyle;
use Shopware\Core\Kernel;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * 12/2022 created
 */
class EventListCommand extends Command
{
    protected static $defaultName = 'mcx:event:list';


    const OUTPUT_FORMAT_JSON = 'json';
    const OUTPUT_FORMAT_TABLE = 'table';

    const ALLOWED_FORMATS = [
        self::OUTPUT_FORMAT_TABLE,
        self::OUTPUT_FORMAT_JSON,
    ];

    const ALLOWED_OUTPUTS = [
        'eventClass',
        'eventName',
        'file',
        'constName',
    ];
    const DEFAULT_OUTPUTS = [
        'eventClass',
        'eventName',
    ];

    private Kernel $kernel; // used for getting project dir
    private ShopwareStyle $io;

    public function __construct(Kernel $kernel, string $name = null)
    {
        parent::__construct($name);
        $this->kernel = $kernel;
    }


    public function configure(): void
    {
        $this->addOption('format', 'f', InputOption::VALUE_REQUIRED, 'which format to output', self::OUTPUT_FORMAT_TABLE);
        $this->addOption('output', 'o', InputOption::VALUE_REQUIRED, 'which fields to output (comma-separated)', implode(',', self::DEFAULT_OUTPUTS));
    }

    
    /**
     * MAIN
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws \Exception
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new ShopwareStyle($input, $output);

        // ---- input validation: --output
        $fields = UtilStringArray::trimExplode(',', $input->getOption('output'));
        if(count(array_diff($fields, self::ALLOWED_OUTPUTS)) > 0) {
            $invalidFields = implode(', ', array_diff($fields, self::ALLOWED_OUTPUTS));
            $allowedFields = implode(', ', self::ALLOWED_OUTPUTS);
            $this->io->error("Invalid output field/s: {$invalidFields}. Allowed fields: {$allowedFields}");

            return Command::FAILURE;
        }


        // ---- input validation: --format
        $format = strtolower($input->getOption('format'));
        if(!in_array($format, self::ALLOWED_FORMATS)) {
            $allowedFormats = implode(', ', self::ALLOWED_FORMATS);
            $this->io->error("Invalid format: {$format}. Allowed formats: {$allowedFormats}");

            return Command::FAILURE;
        }

        $eventFinder = new EventFinder($this->io);
        $list = $eventFinder->findAllEvents($this->kernel->getProjectDir(),);

        // ---- output
        if($format === self::OUTPUT_FORMAT_JSON) {
            $this->_outputAsJson($fields, $list);
        } elseif($format === self::OUTPUT_FORMAT_TABLE) {
            $this->_outputAsTable($fields, $list);
        } else {
            throw new \LogicException("format fail: $format");
        }

        return Command::SUCCESS;
    }

    /**
     * outputs the list as a table
     *
     * @param array $fields
     * @param array $list
     * @return void
     */
    private function _outputAsTable(array $fields, array $list)
    {
        $table = $this->io->createTable();
        $table->setHeaders($fields);
        foreach($list as $item) {
            $row = [];
            foreach($fields as $field) {
                $row[] = $item[$field];
            }
            $table->addRow($row);
        }
        $table->render();
    }

    /**
     * outputs the list as json
     *
     * @param array $fields
     * @param array $list
     * @return void
     */
    private function _outputAsJson(array $fields, array $list)
    {
        $json = [];
        foreach($list as $item) {
            $row = [];
            foreach($fields as $field) {
                $row[$field] = $item[$field];
            }
            $json[] = $row;
        }
        $this->io->text(json_encode($json, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }


}