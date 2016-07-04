<?php
/**
 * This file is part of codeception-phiremock-extension.
 *
 * codeception-wiremock-extension is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * codeception-wiremock-extension is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with codeception-wiremock-extension.  If not, see <http://www.gnu.org/licenses/>.
 */
namespace Codeception\Extension;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;

/**
 * Manages the current running WireMock process.
 */
class PhiremockProcess
{
    /**
     * WireMock server log.
     *
     * @var string
     */
    const LOG_FILE_NAME = 'phiremock.log';

    /**
     * @var \Symfony\Component\Process\Process
     */
    private $process;

    /**
     * @var resource[]
     */
    private $pipes;

    /**
     * Starts a wiremock process.
     *
     * @param string $jarPath
     * @param string $logsPath
     * @param string $arguments
     *
     * @throws \Exception
     */
    public function start($ip, $port, $path, $logsPath, $debug)
    {
        $builder = new ProcessBuilder(['-i', $ip, '-p', $port]);
        if ($debug) {
            $builder->add('-d');
        }
        $builder->setPrefix("{$path}/phiremock");
        $builder->enableOutput();
        $builder->setOption('bypass_shell', true);

        $this->process = $builder->getProcess();
        $logFile = $logsPath . DIRECTORY_SEPARATOR . self::LOG_FILE_NAME;
        $this->process->start(function ($type, $buffer) use ($logFile) {
            file_put_contents($logFile, $buffer, FILE_APPEND);
        });
    }

    /**
     * Stops the process.
     */
    public function stop()
    {
        $this->process->stop(3, SIGTERM);
    }

    /**
     * @return string
     */
    private function getCommandPrefix()
    {
        if (PHP_OS == 'WIN32' || PHP_OS == 'WINNT' || PHP_OS == 'Windows') {
            return '';
        }
        return 'exec ';
    }
}
