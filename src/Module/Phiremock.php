<?php
namespace Codeception\Module;

use Codeception\Module as CodeceptionModule;
use Mcustiel\Phiremock\Client\Phiremock as PhiremockClient;
use Mcustiel\Phiremock\Domain\Expectation;
use Mcustiel\Phiremock\Client\Utils\RequestBuilder;
use Mcustiel\Phiremock\Client\Utils\Respond;

class Phiremock extends CodeceptionModule
{
    protected $config = [
        'host' => 'localhost',
        'port' => '8086'
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

    public function seeRemoteServiceReceived($times, RequestBuilder $builder)
    {
        $requests = $this->phiremock->countExecutions($builder);
        if ($times != $requests) {
            throw new \Exception(
                "Request expected to be executed $times times, called $requests times instead"
            );
        }
    }
}
