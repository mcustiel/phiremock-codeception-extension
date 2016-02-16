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

    public function _beforeSuite($settings = [])
    {
        $this->phiremock = new PhiremockClient($this->config['host'], $this->config['port']);
    }

    public function expectRequest(Expectation $expectation)
    {

    }

    public function verifyExecutions(RequestBuilder $builder)
    {
        $expectation = PhiremockClient::on($builder)->then(Respond::withStatusCode(200));
    }
}
