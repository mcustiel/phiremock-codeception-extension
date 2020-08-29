<?php

class BasicTestCest
{
    // tests
    public function phiremockIsRunning(AcceptanceTester $I)
    {
        $I->sendGET('/__phiremock/expectations');
        $I->seeResponseCodeIs(200);
        $I->seeResponseEquals('[]');
    }
}
