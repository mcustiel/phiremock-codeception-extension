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

namespace Codeception\Module;

use Codeception\Module as CodeceptionModule;
use Mcustiel\Phiremock\Client\Phiremock as PhiremockClient;
use Mcustiel\Phiremock\Client\Utils\RequestBuilder;
use Mcustiel\Phiremock\Domain\Expectation;

class Phiremock extends CodeceptionModule
{
    /**
     * @var array
     */
    protected $config = [
        'host' => 'localhost',
        'port' => '8086',
    ];

    /**
     * @var \Mcustiel\Phiremock\Client\Phiremock
     */
    private $phiremock;

    public function _beforeSuite($settings = [])
    {
        $this->config = array_merge($this->config, $settings);
        $this->phiremock = new PhiremockClient($this->config['host'], $this->config['port']);
    }

    public function expectARequestToRemoteServiceWithAResponse(Expectation $expectation)
    {
        $this->phiremock->createExpectation($expectation);
    }

    public function haveACleanSetupInRemoteService()
    {
        $this->phiremock->clearExpectations();
        $this->phiremock->resetRequestsCounter();
        $this->phiremock->resetScenarios();
    }

    public function dontExpectRequestsInRemoteService()
    {
        $this->phiremock->clearExpectations();
        $this->phiremock->resetRequestsCounter();
    }

    public function haveCleanScenariosInRemoteService()
    {
        $this->phiremock->resetScenarios();
    }

    public function haveCleanRequestsCounterInRemoteService()
    {
        $this->phiremock->resetRequestsCounter();
    }

    public function seeRemoteServiceReceived($times, RequestBuilder $builder)
    {
        $requests = $this->phiremock->countExecutions($builder);
        if ($times !== $requests) {
            throw new \Exception(
                "Request expected to be executed $times times, called $requests times instead"
            );
        }
    }
}
