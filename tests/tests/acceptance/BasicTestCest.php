<?php

use Codeception\Configuration;
use Mcustiel\Phiremock\Client\Phiremock;
use Mcustiel\Phiremock\Client\Utils\A;
use Mcustiel\Phiremock\Client\Utils\Is;
use Mcustiel\Phiremock\Client\Utils\Respond;

class BasicTestCest
{
    public function _before(AcceptanceTester $I)
    {
    }

    public function _after(AcceptanceTester $I)
    {
        // $I->haveACleanSetupInRemoteService();
    }

    // tests
    public function phiremockIsRunning(AcceptanceTester $I)
    {
        $I->sendGET('/__phiremock/expectations');
        $I->seeResponseCodeIs(200);
        $I->seeResponseEquals('[]');
    }
}
