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
use Codeception\TestInterface;
use Codeception\Util\ExpectationAnnotationParser;
use GuzzleHttp\Client;
use Mcustiel\Phiremock\Client\Phiremock as PhiremockClient;
use Mcustiel\Phiremock\Client\Utils\RequestBuilder;
use Mcustiel\Phiremock\Domain\Expectation;

class Phiremock extends CodeceptionModule
{
    /**
     * @var array
     */
    protected $config = [
        'host'                => 'localhost',
        'port'                => '8086',
        'resetBeforeEachTest' => false,
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

    public function _before(TestInterface $test)
    {
        if ($this->config['resetBeforeEachTest']) {
            $this->haveACleanSetupInRemoteService();
        }
        $expectations = (new ExpectationAnnotationParser())->getExpectations($test);
        if(!empty($expectations)){
            $client = new Client([
                'base_uri' => "{$this->config['host']}:{$this->config['port']}",
            ]);
            foreach ($expectations as $expectation){

                $client->post(PhiremockClient::API_EXPECTATIONS_URL, [
                    'headers' => [
                        'Content-Type' => 'application/json'
                    ],
                    'body'    => file_get_contents($expectation),
                ]);
            }
        }
        parent::_before($test);
    }

    public function expectARequestToRemoteServiceWithAResponse(Expectation $expectation)
    {
        $this->phiremock->createExpectation($expectation);
    }

    public function haveACleanSetupInRemoteService()
    {
        $this->phiremock->reset();
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

    public function didNotReceiveRequestsInRemoteService()
    {
        $this->phiremock->resetRequestsCounter();
    }

    /**
     * @param int            $times
     * @param RequestBuilder $builder
     *
     * @throws \Exception
     */
    public function seeRemoteServiceReceived($times, RequestBuilder $builder)
    {
        $requests = $this->phiremock->countExecutions($builder);
        if ($times !== $requests) {
            throw new \Exception(
                "Request expected to be executed $times times, called $requests times instead"
            );
        }
    }
    
    /**
     * @param RequestBuilder $builder
     *
     * @return array
     */
    public function grabRequestsMadeToRemoteService(RequestBuilder $builder)
    {
        return $this->phiremock->listExecutions($builder);
    }
}
