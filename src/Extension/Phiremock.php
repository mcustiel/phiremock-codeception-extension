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

use Codeception\Configuration as Config;
use Codeception\Extension as CodeceptionExtension;

/**
 * Codeception Extension for Phiremock.
 */
class Phiremock extends CodeceptionExtension
{
    private const DEFAULT_PATH = '/../vendor/bin/phiremock';
    private const DEFAULT_PORT = 8086;

    /** @var array */
    public static $events = [
        'suite.before' => 'startProcess',
        'suite.after'  => 'stopProcess',
    ];

    /** @var array */
    protected $config = [
        'listen'     => '0.0.0.0:' . self::DEFAULT_PORT,
        'debug'      => false,
        'startDelay' => 0,
    ];

    /** @var PhiremockProcess */
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
        $this->setDefaults();
        parent::__construct($config, $options);

        $this->initProcess($process);
    }

    public function startProcess(): void
    {
        list($ip, $port) = explode(':', $this->config['listen']);

        $this->process->start(
            $ip,
            empty($port) ? self::DEFAULT_PORT: (int) $port,
            $this->getBinPath($this->config['bin_path']),
            realpath($this->config['logs_path']),
            $this->config['debug'],
            $this->config['expectations_path'] ?: null
        );
        $this->executeDelay();
    }

    public function stopProcess(): void
    {
        $this->process->stop();
    }

    private function executeDelay(): void
    {
        if ($this->config['startDelay']) {
            sleep($this->config['startDelay']);
        }
    }

    private function initProcess(?PhiremockProcess $process): void
    {
        $this->process = $process ?? new PhiremockProcess();
    }

    private function setDefaults(): void
    {
        $this->config['bin_path'] = rtrim(Config::projectDir(), DIRECTORY_SEPARATOR) . self::DEFAULT_PATH;
        $this->config['logs_path'] = Config::logDir();
        $this->config['expectations_path'] = null;
    }

    private function getBinPath($path): string
    {
        $currentDirectory = getcwd();
        chdir(Config::projectDir());
        $binPath = realpath($path);
        chdir($currentDirectory);

        return $binPath;
    }
}
