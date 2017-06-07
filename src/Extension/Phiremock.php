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

use Codeception\Extension as CodeceptionExtension;
use Codeception\Configuration as Config;

/**
 * Codeception Extension for Phiremock
 */
class Phiremock extends CodeceptionExtension
{
    /**
     * @var array
     */
    public static $events = [
        'suite.before' => 'startProcess',
        'suite.after'  => 'stopProcess',
    ];

    /**
     * @var array
     */
    protected $config = [
        'listen' => '0.0.0.0:8086',
        'debug'  => false,
        'startDelay' => 0
    ];

    /**
     * @var PhiremockProcess
     */
    private $process;

    /**
     * Class constructor.
     *
     * @param array            $config
     * @param array            $options
     * @param PhireMockProcess $process optional PhiremockProcess object
     */
    public function __construct(
        array $config,
        array $options,
        PhiremockProcess $process = null
    ) {
        $this->config['bin_path']    = Config::projectDir() . '../vendor/bin/phiremock';
        $this->config['logs_path']   = Config::logDir();
        $this->config['expect_path'] = null;

        parent::__construct($config, $options);

        $this->initProcess($process);
    }

    public function startProcess()
    {
        list($ip, $port) = explode(':', $this->config['listen']);

        $this->process->start(
            $ip,
            $port,
            realpath($this->config['bin_path']),
            realpath($this->config['logs_path']),
            $this->config['debug'],
            realpath($this->config['expect_path'])
        );
        if ($this->config['startDelay']) {
            sleep($this->config['startDelay']);
        }
    }

    public function stopProcess()
    {
        $this->process->stop();
    }

    private function initProcess($process)
    {
        $this->process = $process === null ? new PhiremockProcess() : $process;
    }
}
