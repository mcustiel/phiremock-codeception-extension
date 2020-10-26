<?php

namespace Mcustiel\Phiremock\Codeception\Extension;

use Codeception\Configuration;

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

    public const DEFAULT_CONFIG = [
        'listen'            => self::DEFAULT_INTERFACE . ':' . self::DEFAULT_PORT,
        'debug'             => self::DEFAULT_DEBUG_MODE,
        'start_delay'       => self::DEFAULT_DELAY,
        'bin_path'          => self::DEFAULT_PHIREMOCK_PATH,
        'expectations_path' => self::DEFAULT_EXPECTATIONS_PATH,
        'server_factory'    => self::DEFAULT_SERVER_FACTORY,
        'certificate'       => self::DEFAULT_CERTIFICATE,
        'certificate_key'   => self::DEFAULT_CERTIFICATE_KEY,
        'cert_passphrase'   => self::DEFAULT_CERTIFICATE_PASSPHRASE,
        'extra_instances'   => self::DEFAULT_EXTRA_INSTANCES,
        'suites'            => self::DEFAULT_SUITES,
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

    public function __construct(array $config)
    {
        $this->initInterfaceAndPort($config);
        $this->initExpectationsPath($config);
        $this->initServerFactory($config);
        $this->delay = (int) $config['start_delay'];
        $this->phiremockPath = new Path($config['bin_path']);
        $this->logsPath = new Path($config['logs_path']);
        $this->debug = (bool) $config['debug'];
        $this->initCertificatePath($config);
        $this->initCertificateKeyPath($config);
        $this->certificatePassphrase = $config['cert_passphrase'];
        $this->initExtraInstances($config);
        $this->suites = $config['suites'];
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

    public static function getDefaultLogsPath(): string
    {
        return Configuration::logDir();
    }

    private function initInterfaceAndPort(array $config): void
    {
        if (isset($config['listen'])) {
            $parts = explode(':', $config['listen']);
            $this->interface = $parts[0];
            $this->port = (int) isset($parts[1]) ? $parts[1] : self::DEFAULT_PORT;
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
            $this->writeln('PHIREMOCK/DEPRECATION: startDelay option is deprecated and will be removed. Please use start_delay');
            $this->delay = $config['startDelay'];
            return;
        }

        if ($config['start_delay']) {
            $this->delay = $config['start_delay'];
        }
    }

    private function initExtraInstances(array $config): void
    {
        $this->extraInstances = self::DEFAULT_EXTRA_INSTANCES;
        if (isset($config['extra_instances']) && is_array($config['extra_instances'])) {
            foreach ($config['extra_instances'] as $extraInstance) {
                $instanceConfig = $extraInstance + self::DEFAULT_CONFIG + ['logs_path' => Config::getDefaultLogsPath()];
                unset($instanceConfig['extra_instances']);
                $this->extraInstances[] = new self($instanceConfig);
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
