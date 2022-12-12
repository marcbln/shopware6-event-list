<?php declare(strict_types=1);

namespace Mcx\EventList\Command;

use Error;
use Mcx\EventList\Util\MyNodeVisitor;
use Mcx\EventList\Util\UtilCmd;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use Shopware\Core\Framework\Adapter\Console\ShopwareStyle;
use Shopware\Core\Kernel;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use WhyooOs\Util\Arr\UtilStringArray;
use WhyooOs\Util\UtilDebug;

/**
 * 12/2022 created
 */
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
        $files = $this->_findAllFilesWithEvents([__FILE__]);
        $traverser = new NodeTraverser();
        $myVisitor = new MyNodeVisitor();
        $traverser->addVisitor($myVisitor);

        foreach ($files as $pathFile) {
            $ast = $this->_parseFile($pathFile);
            $myVisitor->reset($pathFile);
            $traverser->traverse($ast);
        }

        UtilDebug::dd($myVisitor->found);

        return Command::SUCCESS;
    }

    /**
     * 12/2022 created
     *
     * @return string[]
     * @throws \Exception
     */
    private function _findAllFilesWithEvents(array $excludes = []): array
    {
        $ret = UtilCmd::exec([
            'grep',
            '-lIrF',
            '--include', '*.php',
            '@Event',
            $this->kernel->getProjectDir(),
        ]);

        $files = self::_trimExplode("\n", $ret);

        return array_diff($files, $excludes);
    }

    /**
     * private helper
     * 12/2022 created
     *
     * @param string $pathFile
     */
    private function _parseFile(string $pathFile)
    {
        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        $code = file_get_contents($pathFile);
        try {
            return $parser->parse($code);
        } catch (Error $error) {
            $this->io->warning("error parsing $pathFile: " . $error->getMessage());
            return null;
        }
    }


    /**
     * explodes and trims results .. excludes empty items ...
     * example:
     * "a, b, c, ,d" returns [a,b,c,d]
     *
     * @param string $delimiter
     * @param string $string
     * @param int|null $limit
     * @return array
     */
    private static function _trimExplode(string $delimiter, string $string, $limit = null, $bKeepEmpty = false)
    {
        if(is_null($limit)) {
            $chunksArr = explode($delimiter, $string);
        } else {
            $chunksArr = explode($delimiter, $string, $limit);
        }

        $newChunksArr = [];
        foreach ($chunksArr as $value) {
            if (strcmp('', trim($value)) || $bKeepEmpty) {
                $newChunksArr[] = trim($value);
            }
        }
        reset($newChunksArr);

        return $newChunksArr;
    }


}