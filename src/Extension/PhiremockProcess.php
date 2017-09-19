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

        $this->logPhiremockCommand($ip, $port, $debug, $expectationsPath, $phiremockPath);
        $this->initProcess($ip, $port, $debug, $expectationsPath, $phiremockPath);
        $logFile = $logsPath . DIRECTORY_SEPARATOR . self::LOG_FILE_NAME;
        $this->process->start(function ($type, $buffer) use ($logFile) {
            file_put_contents($logFile, $buffer, FILE_APPEND);
        });
        $this->setUpProcessCompatibility();
    }

    /**
     * Stops the process.
     */
    public function stop()
    {
        if ($this->isPcntlEnabled()) {
            $this->process->signal(SIGTERM);
            $this->process->stop(3, SIGKILL);
        }
    }

    private function setUpProcessCompatibility()
    {
        $this->process->setEnhanceSigchildCompatibility(true);
        if ($this->isWindows()) {
            $this->process->setEnhanceWindowsCompatibility(true);
        }
    }

    /**
     * @param string $ip
     * @param int    $port
     * @param bool   $debug
     * @param string $expectationsPath
     * @param string $phiremockPath
     */
    private function initProcess($ip, $port, $debug, $expectationsPath, $phiremockPath)
    {
        $this->process = new Process(
            $this->getCommandPrefix()
            . "{$phiremockPath} -i {$ip} -p {$port}"
            . ($debug ? ' -d' : '')
            . ($expectationsPath ? " -e {$expectationsPath}" : '')
        );
    }

    /**
     * @param string $ip
     * @param int    $port
     * @param bool   $debug
     * @param string $expectationsPath
     * @param string $phiremockPath
     */
    private function logPhiremockCommand($ip, $port, $debug, $expectationsPath, $phiremockPath)
    {
        if ($debug) {
            echo 'Running ' . $this->getCommandPrefix()
                . "{$phiremockPath} -i {$ip} -p {$port}"
                . ($debug ? ' -d' : '')
                . ($expectationsPath ? " -e {$expectationsPath}" : '') . PHP_EOL;
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
        return PHP_OS === 'WIN32' || PHP_OS === 'WINNT' || PHP_OS === 'Windows';
    }

    /**
     * @return bool
     */
    private function isPcntlEnabled()
    {
        return !$this->isWindows() && defined('SIGTERM');
    }
}
