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
