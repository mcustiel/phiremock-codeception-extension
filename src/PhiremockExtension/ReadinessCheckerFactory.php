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

use Mcustiel\Phiremock\Client\Factory;
use Mcustiel\Phiremock\Client\Connection\Host;
use Mcustiel\Phiremock\Client\Connection\Port;
use Mcustiel\Phiremock\Client\Connection\Scheme;
use Mcustiel\Phiremock\Codeception\Extension\ReadinessChecker\CurlChecker;
use Mcustiel\Phiremock\Codeception\Extension\ReadinessChecker\PhiremockClientChecker;

class ReadinessCheckerFactory
{
    public static function create(string $host, string $port, bool $isSecure): ReadinessCheckerInterface
    {
        if (class_exists(Factory::class)) {
            $phiremockClient = Factory::createDefault()
                ->createPhiremockClient(
                    new Host($host),
                    new Port($port),
                    $isSecure ? Scheme::createHttps() : Scheme::createHttp()
                );

            return new PhiremockClientChecker(
                $phiremockClient
            );
        } elseif (extension_loaded('curl')) {
            $url = 'http' . ($isSecure ? 's' : '')
                . '://' . $host
                . ($port !== '' ? ':' . $port : '');

            return new CurlChecker($url);
        }

        throw new \RuntimeException(
            'Config wait_until_ready is enabled but no readiness checker can be run. Check if you have Phiremock Client installed or curl extension enabled.'
        );
    }
}