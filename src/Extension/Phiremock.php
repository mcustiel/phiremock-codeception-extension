<?php
/**
 * This file is part of phiremock-codeception-extension.
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

use Codeception\Event\SuiteEvent;
use Codeception\Extension as CodeceptionExtension;
use Codeception\Suite;
use Mcustiel\Phiremock\Codeception\Extension\Config;
use Mcustiel\Phiremock\Codeception\Extension\PhiremockProcessManager;
use Symfony\Component\EventDispatcher\EventDispatcher;

class Phiremock extends CodeceptionExtension
{
    /** @var array */
    public static $events = [
        'suite.before' => 'startProcess',
        'suite.after'  => 'stopProcess',
    ];

    /** @var array */
    protected $config = Config::DEFAULT_CONFIG;

    /** @var PhiremockProcessManager */
    private $process;

    /** @var Config */
    private $extensionConfig;

    public function __construct(
        array $config,
        array $options,
        PhiremockProcessManager $process = null
    ) {
        $this->setDefaultLogsPath();
        parent::__construct($config, $options);
        $this->extensionConfig = new Config($this->config, $this->getOutputCallable());
        $this->initProcess($process);
    }

    public function startProcess(SuiteEvent $event, string $eventName, EventDispatcher $eventDispatcher): void
    {
        $this->writeln('Starting default phiremock instance...');
        $suite = $event->getSuite();
        if ($this->mustRunForSuite($suite, $this->extensionConfig->getSuites())) {
            $this->process->start($this->extensionConfig);
        }
        foreach ($this->extensionConfig->getExtraInstances() as $configInstance) {
            if ($this->mustRunForSuite($suite, $configInstance->getSuites())) {
                $this->writeln('Starting extra phiremock instance...');
                $this->process->start($configInstance);
            }
        }
        $this->executeDelay();
    }

    public function stopProcess(): void
    {
        $this->writeln('Stopping phiremock...');
        $this->process->stop();
    }

    public function getOutputCallable(): callable
    {
        return function (string $message) {
            $this->writeln($message);
        };
    }

    private function mustRunForSuite(Suite $suite, array $allowedSuites): bool
    {
        return empty($allowedSuites) || in_array($suite->getBaseName(), $allowedSuites, true);
    }

    private function executeDelay(): void
    {
        $delay = $this->extensionConfig->getDelay();
        if ($delay > 0) {
            sleep($delay);
        }
    }

    private function initProcess(?PhiremockProcessManager $process): void
    {
        $this->process = $process ?? new PhiremockProcessManager($this->getOutputCallable());
    }

    private function setDefaultLogsPath(): void
    {
        if (!isset($this->config['logs_path'])) {
            $this->config['logs_path'] = Config::getDefaultLogsPath();
        }
    }
}
