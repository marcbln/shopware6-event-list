<?php

namespace Mcx\EventList\Util;

use PhpParser\Error;
use PhpParser\Node\Stmt;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * 12/2022 created.
 */
class EventFinder
{

    /**
     * for printing error if problem with parsing
     */
    private SymfonyStyle $io;

    public function __construct(SymfonyStyle $io)
    {
        $this->io = $io;
    }

    /**
     * this is the main function.
     * it parses php files and returns list of found events
     *
     * 12/2022 created
     *
     * @param string $pathProjectDir root directory where it searches for php files with events
     * @return array list of found events
     * @throws \Exception
     */
    public function findAllEvents(string $pathProjectDir): array
    {
        $files = $this->_findAllFilesWithEvents($pathProjectDir, [__FILE__]);
        $traverser = new NodeTraverser();
        $myVisitor = new MyNodeVisitor();
        $traverser->addVisitor($myVisitor);

        foreach ($files as $pathFile) {
            try {
                $ast = $this->_parseFile($pathFile);
            } catch (Error $error) {
                $this->io->warning("error parsing $pathFile: " . $error->getMessage());
            }
            $myVisitor->reset($pathFile);
            $traverser->traverse($ast);
        }

        return $myVisitor->found;
    }


    /**
     * 12/2022 created
     *
     * @return string[]
     * @throws \Exception
     */
    private function _findAllFilesWithEvents(string $pathProjectDir, array $excludes = []): array
    {
        $ret = UtilCmd::exec([
            'grep',
            '-lIrF',
            '--include', '*.php',
            '@Event',
            $pathProjectDir,
        ]);

        $files = UtilStringArray::trimExplode("\n", $ret);

        return array_diff($files, $excludes);
    }

    /**
     * parses a php file, builds AST
     * private helper
     * 12/2022 created
     *
     * @param string $pathFile
     * @return mixed the AST
     */
    private function _parseFile(string $pathFile)
    {
        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        $code = file_get_contents($pathFile);

        return $parser->parse($code);
    }

}
