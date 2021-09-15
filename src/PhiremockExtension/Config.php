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

namespace Mcustiel\Phiremock\Codeception\Extension;

use Codeception\Configuration;
use Codeception\Exception\ConfigurationException;

class Config
{
    public const DEFAULT_INTERFACE = '0.0.0.0';
    public const DEFAULT_PORT = 8086;
    public const DEFAULT_PHIREMOCK_PATH = 'vendor/bin/phiremock';
    public const DEFAULT_DELAY = 0;
    public const DEFAULT_DEBUG_MODE = false;
    public const DEFAULT_EXPECTATIONS_PATH = null;
    public const DEFAULT_CERTIFICATE = null;
    public const DEFAULT_CERTIFICATE_KEY = null;
    public const DEFAULT_CERTIFICATE_PASSPHRASE = null;
    public const DEFAULT_SERVER_FACTORY = 'default';
    public const DEFAULT_EXTRA_INSTANCES = [];
    public const DEFAULT_SUITES = [];
    public const DEFAULT_WAIT_UNTIL_READY = false;
    public const DEFAULT_WAIT_UNTIL_READY_TIMEOUT = 30;
    public const DEFAULT_WAIT_UNTIL_READY_INTERVAL_MICROS = 50000;

    public const DEFAULT_CONFIG = [
        'listen'                    => self::DEFAULT_INTERFACE . ':' . self::DEFAULT_PORT,
        'debug'                     => self::DEFAULT_DEBUG_MODE,
        'start_delay'               => self::DEFAULT_DELAY,
        'bin_path'                  => self::DEFAULT_PHIREMOCK_PATH,
        'expectations_path'         => self::DEFAULT_EXPECTATIONS_PATH,
        'server_factory'            => self::DEFAULT_SERVER_FACTORY,
        'certificate'               => self::DEFAULT_CERTIFICATE,
        'certificate_key'           => self::DEFAULT_CERTIFICATE_KEY,
        'cert_passphrase'           => self::DEFAULT_CERTIFICATE_PASSPHRASE,
        'extra_instances'           => self::DEFAULT_EXTRA_INSTANCES,
        'suites'                    => self::DEFAULT_SUITES,
        'wait_until_ready'          => self::DEFAULT_WAIT_UNTIL_READY,
        'wait_until_ready_timeout'  => self::DEFAULT_WAIT_UNTIL_READY_TIMEOUT,
        'wait_until_ready_interval' => self::DEFAULT_WAIT_UNTIL_READY_INTERVAL_MICROS,
    ];

    /** @var string */
    private $interface;
    /** @var int */
    private $port;
    /** @var int */
    private $delay;
    /** @var bool */
    private $debug;
    /** @var Path */
    private $phiremockPath;
    /** @var Path */
    private $expectationsPath;
    /** @var Path */
    private $logsPath;
    /** @var string */
    private $serverFactory;
    /** @var Path */
    private $certificate;
    /** @var Path */
    private $certificateKey;
    /** @var string */
    private $certificatePassphrase;
    /** @var Config[] */
    private $extraInstances;
    /** @var string[] */
    private $suites;
    /** @var callable */
    private $output;
    /** @var bool */
    private $waitUntilReady;
    /** @var int */
    private $waitUntilReadyTimeout;
    /** @var int */
    private $waitUntilReadyCheckIntervalMicros;

    /** @throws ConfigurationException */
    public function __construct(array $config, callable $output)
    {
        $this->output = $output;
        $this->initInterfaceAndPort($config);
        $this->initExpectationsPath($config);
        $this->initServerFactory($config);
        $this->initDelay($config);
        $this->phiremockPath = new Path($config['bin_path']);
        $this->logsPath = new Path($config['logs_path']);
        $this->debug = (bool) $config['debug'];
        $this->initCertificatePath($config);
        $this->initCertificateKeyPath($config);
        $this->certificatePassphrase = $config['cert_passphrase'];
        $this->initExtraInstances($config);
        $this->suites = $config['suites'];
        $this->waitUntilReady = (bool) $config['wait_until_ready'];
        $this->waitUntilReadyTimeout = (int) $config['wait_until_ready_timeout'];
        $this->waitUntilReadyCheckIntervalMicros = (int) $config['wait_until_ready_interval'];
    }

    public function getSuites(): array
    {
        return $this->suites;
    }

    public function getInterface(): string
    {
        return $this->interface;
    }

    public function getPort(): string
    {
        return $this->port;
    }

    public function isDebugMode(): bool
    {
        return $this->debug;
    }

    public function getStartDelay(): string
    {
        return $this->delay;
    }

    public function getPhiremockPath(): string
    {
        return $this->phiremockPath->absoluteOrRelativeToCodeceptionDir();
    }

    public function getExpectationsPath(): ?string
    {
        return $this->expectationsPath ? $this->expectationsPath->absoluteOrRelativeToCodeceptionDir() : null;
    }

    public function getCertificatePath(): ?string
    {
        return $this->certificate ? $this->certificate->absoluteOrRelativeToCodeceptionDir() : null;
    }

    public function getCertificateKeyPath(): ?string
    {
        return $this->certificateKey ? $this->certificateKey->absoluteOrRelativeToCodeceptionDir() : null;
    }

    public function getCertificatePassphrase(): ?string
    {
        return $this->certificatePassphrase;
    }

    public function getLogsPath(): string
    {
        return $this->logsPath->absoluteOrRelativeToCodeceptionDir();
    }

    public function getServerFactory(): ?string
    {
        return $this->serverFactory;
    }

    public function getDelay(): int
    {
        return $this->delay;
    }

    public function getExtraInstances(): array
    {
        return $this->extraInstances;
    }

    public function isSecure(): bool
    {
        return $this->getCertificatePath() !== null
            && $this->getCertificateKeyPath() !== null;
    }

    public function waitUntilReady(): bool
    {
        return $this->waitUntilReady;
    }

    public function getWaitUntilReadyTimeout(): int
    {
        return $this->waitUntilReadyTimeout;
    }

    public function getWaitUntilReadyIntervalMicros(): int
    {
        return $this->waitUntilReadyCheckIntervalMicros;
    }

    /** @throws ConfigurationException */
    public static function getDefaultLogsPath(): string
    {
        return Configuration::logDir();
    }

    private function initInterfaceAndPort(array $config): void
    {
        if (isset($config['listen'])) {
            $parts = explode(':', $config['listen']);
            $this->interface = $parts[0];
            $this->port = (int) (isset($parts[1]) ? $parts[1] : self::DEFAULT_PORT);
        }
    }

    private function initExpectationsPath(array $config): void
    {
        $this->expectationsPath = isset($config['expectations_path']) ? new Path($config['expectations_path']) : null;
    }

    private function initServerFactory(array $config): void
    {
        $factory = null;
        if (isset($config['server_factory'])) {
            $factoryClassConfig = $config['server_factory'];
            if ($factoryClassConfig !== 'default') {
                $factory = $config['server_factory'];
            }
        }
        $this->serverFactory = $factory;
    }

    private function initDelay(array $config): void
    {
        if (isset($config['startDelay'])) {
            call_user_func($this->output, 'PHIREMOCK/DEPRECATION: startDelay option is deprecated and will be removed. Please use start_delay');
            $this->delay = (int) $config['startDelay'];
            return;
        }

        if (is_int($config['start_delay']) && $config['start_delay'] >= 0) {
            $this->delay = (int) $config['start_delay'];
        }
    }

    /** @throws ConfigurationException */
    private function initExtraInstances(array $config): void
    {
        $this->extraInstances = self::DEFAULT_EXTRA_INSTANCES;
        if (isset($config['extra_instances']) && is_array($config['extra_instances'])) {
            foreach ($config['extra_instances'] as $extraInstance) {
                $instanceConfig = $extraInstance + self::DEFAULT_CONFIG + ['logs_path' => Config::getDefaultLogsPath()];
                unset($instanceConfig['extra_instances']);
                $this->extraInstances[] = new self($instanceConfig, $this->output);
            }
        }
    }

    private function initCertificateKeyPath($config): void
    {
        $this->certificateKey = $config['certificate_key'] ? new Path($config['certificate_key']) : null;
    }

    private function initCertificatePath($config): void
    {
        $this->certificate = $config['certificate'] ? new Path($config['certificate']) : null;
    }
}
