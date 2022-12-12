<?php declare(strict_types=1);

namespace Mcx\EventList\Util;

use Symfony\Component\Process\Process;

/**
 * 12/2022 created.
 */
class UtilCmd
{
    /**
     * 12/2022 created
     *
     * @param array $cmd
     */
    public static function exec(array $cmd)
    {
        try {
            $process = new Process($cmd);
            $process->setTimeout(null);
            $process->run();
            if ($process->getExitCode() !== 0) {
                throw  new \Exception('exec failed with code ' . $process->getExitCode() . ': ' . implode(" ", $cmd) . ' - ' . $process->getOutput() . '/' . $process->getErrorOutput());
            }
        } catch (\Exception $exception) {
            throw $exception;
        }

        return $process->getOutput();
    }
}
