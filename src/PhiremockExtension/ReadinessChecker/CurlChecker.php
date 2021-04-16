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

namespace Mcustiel\Phiremock\Codeception\Extension\ReadinessChecker;

use Mcustiel\Phiremock\Codeception\Extension\ReadinessCheckerInterface;

class CurlChecker implements ReadinessCheckerInterface
{
    private $url;

    public function __construct(string $url)
    {
        $this->url = rtrim($url, '/');
    }

    public function isReady(): bool
    {
        $ch = \curl_init();

        \curl_setopt($ch, CURLOPT_URL,$this->url . '/__phiremock/reset');
        \curl_setopt($ch, CURLOPT_POST, 1);
        \curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $output = \curl_exec($ch);
        \curl_close($ch);

        if ($output === false) {
            return false;
        }

        return true;
    }
}