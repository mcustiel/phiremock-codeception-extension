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
    const LOG_FILE_NAME = 'phiremock.out';

    /**
     * @var resource
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
    public function start($ip, $port, $path)
    {
        $this->checkIfProcessIsRunning();

        $this->process = proc_open(
            $this->getCommandPrefix() . "php {$path}/phiremock -i {$host} -p {$port}",
            $this->createProcessDescriptors($logsPath),
            $this->pipes,
            null,
            null,
            ['bypass_shell' => true]
        );
        $this->checkProcessIsRunning();
    }

    /**
     * @param string $logsPath
     *
     * @return array[]
     */
    private function createProcessDescriptors($logsPath)
    {
        $logFile = $logsPath . DIRECTORY_SEPARATOR . self::LOG_FILE_NAME;
        $descriptors = [
            ['pipe', 'r'],
            ['file', $logFile, 'w'],
            ['file', $logFile, 'a'],
        ];
        return $descriptors;
    }

    /**
     * @throws \Exception
     */
    private function checkIfProcessIsRunning()
    {
        if ($this->process !== null) {
            throw new \Exception('The server is already running');
        }
    }

    /**
     * @return boolean
     */
    public function isRunning()
    {
        return isset($this->process) && is_resource($this->process);
    }

    /**
     * @throws \Exception
     */
    private function checkProcessIsRunning()
    {
        if (!$this->isRunning()) {
            throw new \Exception('Could not start local phiremock server');
        }
    }

    /**
     * Stops the process.
     */
    public function stop()
    {
        if (is_resource($this->process)) {
            foreach ($this->pipes as $pipe) {
                if (is_resource($pipe)) {
                    fflush($pipe);
                    fclose($pipe);
                }
            }
            proc_close($this->process);
            unset($this->process);
        }
    }

    /**
     * @return string
     */
    private function getCommandPrefix()
    {
        if (PHP_OS == 'WIN32' || PHP_OS == 'WINNT' || PHP_OS == 'Windows') {
            return 'exec ';
        }
        return '';
    }
}
