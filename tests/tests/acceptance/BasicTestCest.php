<?php


use Mcustiel\Phiremock\Client\Phiremock;
use Mcustiel\Phiremock\Client\Utils\A;
use Mcustiel\Phiremock\Client\Utils\Is;
use Mcustiel\Phiremock\Client\Utils\Respond;

class BasicTestCest
{
    public function _before(AcceptanceTester $I)
    {
        $I->haveACleanSetupInRemoteService();
    }

    public function _after(AcceptanceTester $I)
    {
    }

    // tests
    public function tryToTest(AcceptanceTester $I)
    {
        $I->expectARequestToRemoteServiceWithAResponse(
            Phiremock::on(
                A::getRequest()->andUrl(Is::equalTo('/some/url'))
            )->then(
                Respond::withStatusCode(203)->andBody('I am a response')
            )
        );
        $response = file_get_contents('http://localhost:18080/some/url');
        $I->assertEquals('I am a response', $response);
        $I->seeRemoteServiceReceived(1, A::getRequest()->andUrl(Is::equalTo('/some/url')));
        $I->didNotReceiveRequestsInRemoteService();
        $I->seeRemoteServiceReceived(0, A::getRequest()->andUrl(Is::equalTo('/some/url')));
    }

    public function severalExceptatationsInOneTest(AcceptanceTester $I)
    {
        $I->expectARequestToRemoteServiceWithAResponse(
            Phiremock::on(
                A::getRequest()->andUrl(Is::equalTo('/potato'))
            )->then(
                Respond::withStatusCode(203)->andBody('I am a potato')
            )
        );
        $I->expectARequestToRemoteServiceWithAResponse(
            Phiremock::on(
                A::getRequest()->andUrl(Is::equalTo('/tomato'))
            )->then(
                Respond::withStatusCode(203)->andBody('I am a tomato')
            )
        );
        $I->expectARequestToRemoteServiceWithAResponse(
            Phiremock::on(
                A::getRequest()->andUrl(Is::equalTo('/coconut'))
            )->then(
                Respond::withStatusCode(203)->andBody('I am a coconut')
            )
        );
        $I->expectARequestToRemoteServiceWithAResponse(
            Phiremock::on(
                A::getRequest()->andUrl(Is::equalTo('/banana'))
            )->then(
                Respond::withStatusCode(203)->andBody('I am a banana')
            )
        );
        foreach (['potato', 'tomato', 'banana', 'coconut'] as $item) {
            $response = file_get_contents('http://localhost:18080/' . $item);
            $I->assertEquals('I am a ' . $item, $response);
        }
        $I->seeRemoteServiceReceived(4, A::getRequest());
        $I->seeRemoteServiceReceived(1, A::getRequest()->andUrl(Is::equalTo('/potato')));
        $I->seeRemoteServiceReceived(1, A::getRequest()->andUrl(Is::equalTo('/tomato')));
        $I->seeRemoteServiceReceived(1, A::getRequest()->andUrl(Is::equalTo('/banana')));
        $I->seeRemoteServiceReceived(1, A::getRequest()->andUrl(Is::equalTo('/coconut')));
        $I->didNotReceiveRequestsInRemoteService();
        $I->seeRemoteServiceReceived(0, A::getRequest());
    }
}
