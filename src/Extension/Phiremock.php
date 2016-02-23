<?php
/**
 * This file is part of codeception-wiremock-extension.
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

use Codeception\Extension as CodeceptionExtension;
use Codeception\Configuration as Config;
/**
 * Codeception Extension for Phiremock
 */
class Phiremock extends CodeceptionExtension
{
    public static $events = [];

    protected $config = [
        'listen'   => '0.0.0.0:8086'
    ];

    /**
     *
     * @var PhiremockProcess
     */
    private $process;

    /**
     * Class constructor.
     *
     * @param array              $config
     * @param array              $options
     * @param PhireMockProcess   $process  optional PhiremockProcess object
     */
    public function __construct(
        array $config,
        array $options,
        PhiremockProcess $process = null
    ) {
        $this->config['bin_path'] = Config::projectDir() . '../vendor/bin';
        $this->config['logs_path'] = Config::logDir();
        parent::__construct($config, $options);

        $this->initProcess($process);

        list($ip, $port) = explode(':', $this->config['listen']);
        $executablePath = $this->config['bin_path'];
        $this->process->start($ip, $port, $executablePath, $this->config['logs_path']);
    }

    private function initProcess($process)
    {
        if ($process === null) {
            $this->process = new PhiremockProcess();
        } else {
            $this->process = $process;
        }
    }

    /**
     * Class destructor.
     */
    public function __destruct()
    {
        $this->process->stop();
    }
}
