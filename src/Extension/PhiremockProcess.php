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
        $path = realpath($path);
        $phiremockPath = is_file($path) ? $path : $path . DIRECTORY_SEPARATOR . 'phiremock';
      
        if ($debug) {
            echo 'Running ' . $this->getCommandPrefix()
                . "{$phiremockPath} -i {$ip} -p {$port}"
                . ($debug? ' -d' : '') . PHP_EOL;
        }
        $this->process = new Process(
            $this->getCommandPrefix()
            . "{$phiremockPath} -i {$ip} -p {$port}"
            . ($debug ? ' -d' : '')
        );
        $logFile = $logsPath . DIRECTORY_SEPARATOR . self::LOG_FILE_NAME;
        $this->process->start(function ($type, $buffer) use ($logFile) {
            file_put_contents($logFile, $buffer, FILE_APPEND);
        });
        $this->process->setEnhanceSigchildCompatibility(true);
        if ($this->isWindows()) {
            $this->process->setEnhanceWindowsCompatibility(true);
        }
    }

    /**
     * Stops the process.
     */
    public function stop()
    {
        if (!$this->isWindows()) {
            $this->process->signal(SIGTERM);
            $this->process->stop(3, SIGKILL);
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
     * @return boolean
     */
    private function isWindows()
    {
        return PHP_OS == 'WIN32' || PHP_OS == 'WINNT' || PHP_OS == 'Windows';
    }
}
