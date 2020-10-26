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

namespace Mcustiel\Phiremock\Codeception\Extension;

use Symfony\Component\Process\Process;

/**
 * Manages the current running Phiremock process.
 */
class PhiremockProcessManager
{
    const LOG_FILE_NAME = 'phiremock.log';

    /** @var \Symfony\Component\Process\Process[] */
    private $processes;

    public function __construct()
    {
        $this->processes = [];
    }

    public function start(Config $config): void
    {
        $commandBuilder = new CommandBuilder($config);
        $process = $this->initProcess($commandBuilder);
        $this->logPhiremockCommand($config->isDebugMode(), $process);
        $process->start();
        $this->processes[$process->getPid()] = $process;
    }

    public function stop(): void
    {
        foreach ($this->processes as $pid => $process) {
            echo "Stopping phiremock process with pid: " . $pid . PHP_EOL;
            $process->stop(3);
        }
    }

    private function initProcess(CommandBuilder $builder): Process
    {
        $commandline = $builder->build();

        if (method_exists(Process::class, 'fromShellCommandline')) {
            return Process::fromShellCommandline(implode(' ', $commandline));
        }
        return new Process(implode(' ', $commandline));
    }

    private function logPhiremockCommand(bool $debug, Process $process): void
    {
        if ($debug) {
            echo 'Running ' . $process->getCommandLine() . PHP_EOL;
        }
    }
}
