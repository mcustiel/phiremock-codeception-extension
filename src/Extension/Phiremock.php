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
use Codeception\Exception\ConfigurationException;
use Codeception\Extension as CodeceptionExtension;
use Codeception\Suite;
use Mcustiel\Phiremock\Codeception\Extension\Config;
use Mcustiel\Phiremock\Codeception\Extension\PhiremockProcessManager;
use Mcustiel\Phiremock\Codeception\Extension\ReadinessCheckerFactory;
use Mcustiel\Phiremock\Codeception\Extension\Phiremock72;
use Mcustiel\Phiremock\Codeception\Extension\Phiremock74p;

class Phiremock extends CodeceptionExtension
{
    /** @var array */
    public static $events = [
        'suite.before' => 'startProcess',
        'suite.after'  => 'stopProcess',
    ];

    /** Phiremock72|Phiremock74p */
    private $instance;

    /**  @throws ConfigurationException */
    public function __construct(
        array $config,
        array $options,
        PhiremockProcessManager $process = null
    ) {
        if (version_compare(PHP_VERSION, '7.4.0') >= 0) {
            $this->instance = new Phiremock74p($config, $options, $process);
        } else {
            $this->instance = new Phiremock72($config, $options, $process);
        }
    }

    public function startProcess(SuiteEvent $event): void
    {
        $this->instance->startProcess($event);
    }

    public function stopProcess(): void
    {
        $this->instance->stopProcess();
    }
}
