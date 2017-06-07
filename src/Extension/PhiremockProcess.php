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
     */
    public function start($ip, $port, $path, $logsPath, $debug, $expectPath)
    {
        $phiremockPath = is_file($path) ? $path : $path . DIRECTORY_SEPARATOR . 'phiremock';
        $expectPath    = is_dir($expectPath) ? $expectPath : null;

        if ($debug) {
            echo 'Running ' . $this->getCommandPrefix()
                . "{$phiremockPath} -i {$ip} -p {$port}"
                . ($debug? ' -d' : '')
                . ($expectPath ? " -e {$expectPath }" : '' ) . PHP_EOL;
        }
        $this->process = new Process(
            $this->getCommandPrefix()
            . "{$phiremockPath} -i {$ip} -p {$port}"
            . ($debug ? ' -d' : '')
            . ($expectPath ? " -e {$expectPath}" : '' )
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
