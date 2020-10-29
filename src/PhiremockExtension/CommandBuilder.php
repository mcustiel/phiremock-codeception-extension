<?php

namespace Mcustiel\Phiremock\Codeception\Extension;

class CommandBuilder
{
    private const LOG_FILE_NAME = 'phiremock.log';

    /** @var Config */
    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function build(): array
    {
        $path = $this->config->getPhiremockPath();
        $phiremockPath = is_file($path) ? $path : $path . DIRECTORY_SEPARATOR . 'phiremock';

        $commandLine = [
            $this->getCommandPrefix() . $phiremockPath,
            '-i',
            $this->config->getInterface(),
            '-p',
            $this->config->getPort(),
        ];

        $this->addDebugMode($commandLine);
        $this->addExpectationsPath($commandLine);
        $this->addServerFactory($commandLine);
        $this->addCertificate($commandLine);

        $commandLine[] = '>';
        $this->addLogFile($commandLine);
        $commandLine[] = '2>&1';

        return $commandLine;
    }

    private function getCommandPrefix(): string
    {
        return $this->isWindows() ? '' : 'exec ';
    }

    private function isWindows(): bool
    {
        return DIRECTORY_SEPARATOR === '\\';
    }

    private function addLogFile(array &$commandline): void
    {
        $path = $this->config->getLogsPath();
        $logFile = is_dir($path) ? $path . DIRECTORY_SEPARATOR . self::LOG_FILE_NAME : $path;
        $commandline[] = $logFile;
    }

    private function addCertificate(array &$commandline): void
    {
        if ($this->config->getCertificatePath()) {
            $commandline[] = '-t';
            $commandline[] = $this->config->getCertificatePath();
            $commandline[] = '-k';
            $commandline[] = $this->config->getCertificateKeyPath();
            if ($this->config->getCertificatePassphrase()) {
                $commandline[] = '-s';
                $commandline[] = $this->config->getCertificatePassphrase();
            }
        }
    }

    private function addServerFactory(array &$commandline): void
    {
        if ($this->config->getServerFactory()) {
            $commandline[] = '-f';
            $commandline[] = escapeshellarg($this->config->getServerFactory());
        }
    }

    private function addExpectationsPath(array &$commandline): void
    {
        $path = $this->config->getExpectationsPath();
        $expectationsPath = is_dir($path) ? $path : '';
        if ($expectationsPath) {
            $commandline[] = '-e';
            $commandline[] = $expectationsPath;
        }
    }

    private function addDebugMode(array &$commandline): void
    {
        if ($this->config->isDebugMode()) {
            $commandline[] = '-d';
        }
    }
}
