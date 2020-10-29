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
    /** @var Process[] */
    private $processes;

    /** @var callable */
    private $output;

    public function __construct(callable $output)
    {
        $this->processes = [];
        $this->output = $output;
    }

    public function start(Config $config): void
    {
        $commandBuilder = new CommandBuilder($config);
        $process = $this->initProcess($commandBuilder);
        call_user_func($this->output, 'Running ' . $process->getCommandLine());
        $process->start();
        $this->processes[$process->getPid()] = $process;
    }

    public function stop(): void
    {
        foreach ($this->processes as $pid => $process) {
            call_user_func($this->output, "Stopping phiremock process with pid: " . $pid);
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
}
