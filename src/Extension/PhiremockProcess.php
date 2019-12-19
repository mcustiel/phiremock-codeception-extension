<?php
/**
 * This file is part of codeception-phiremock-extension.
 *
 * phiremock-codeception-extension is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * phiremock-codeception-extension is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with phiremock-codeception-extension.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Codeception\Extension;

use Symfony\Component\Process\Process;

/**
 * Manages the current running Phiremock process.
 */
class PhiremockProcess
{
    /**
     * Phiremock server log.
     *
     * @var string
     */
    const LOG_FILE_NAME = 'phiremock.log';

    /**
     * @var \Symfony\Component\Process\Process
     */
    private $process;

    /**
     * Starts Phiremock.
     *
     * @param string $ip
     * @param int    $port
     * @param string $path
     * @param string $logsPath
     * @param bool   $debug
     * @param mixed  $expectationsPath
     */
    public function start($ip, $port, $path, $logsPath, $debug, $expectationsPath)
    {
        $phiremockPath = is_file($path) ? $path : $path . DIRECTORY_SEPARATOR . 'phiremock';
        $expectationsPath = is_dir($expectationsPath) ? $expectationsPath : '';
        $logFile = $logsPath . DIRECTORY_SEPARATOR . self::LOG_FILE_NAME;
        $this->initProcess($ip, $port, $debug, $expectationsPath, $phiremockPath, $logFile);
        $this->logPhiremockCommand($debug);
        $this->process->start();
    }

    /**
     * Stops the process.
     */
    public function stop()
    {
        $this->process->stop(3);
    }

    /**
     * @param string $ip
     * @param int    $port
     * @param bool   $debug
     * @param string $expectationsPath
     * @param string $phiremockPath
     * @param string $logFile
     */
    private function initProcess($ip, $port, $debug, $expectationsPath, $phiremockPath, $logFile)
    {
        $commandline = [
            $this->getCommandPrefix() . $phiremockPath,
            '-i',
            $ip,
            '-p',
            $port,
        ];
        if ($debug) {
            $commandline[] = '-d';
        }
        if ($expectationsPath) {
            $commandline[] = '-e';
            $commandline[] = $expectationsPath;
        }
        $commandline[] = '>';
        $commandline[] = $logFile;
        $commandline[] = '2>&1';

        if (method_exists(Process::class, 'fromShellCommandline')) {
            $this->process = Process::fromShellCommandline(implode(' ', $commandline));
        } else {
            $this->process = new Process(implode(' ', $commandline));
        }
    }

    /**
     * @param bool $debug
     */
    private function logPhiremockCommand($debug)
    {
        if ($debug) {
            echo 'Running ' . $this->process->getCommandLine() . PHP_EOL;
        }
    }

    /**
     * @return string
     */
    private function getCommandPrefix()
    {
        return $this->isWindows() ? '' : 'exec ';
    }

    /**
     * @return bool
     */
    private function isWindows()
    {
        return DIRECTORY_SEPARATOR === '\\';
    }
}
