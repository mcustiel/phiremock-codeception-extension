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

use GuzzleHttp\Exception\ConnectException;
use Mcustiel\Phiremock\Codeception\Extension\ReadinessCheckerInterface;
use Mcustiel\Phiremock\Client\Phiremock;
use Psr\Http\Client\ClientExceptionInterface;

class PhiremockClientChecker implements ReadinessCheckerInterface
{
    private $client;

    public function __construct(Phiremock $client)
    {
        $this->client = $client;
    }

    public function isReady(): bool
    {
        try {
            $this->client->reset();
            return true;
        } catch (ConnectException $e) {}

        return false;
    }
}