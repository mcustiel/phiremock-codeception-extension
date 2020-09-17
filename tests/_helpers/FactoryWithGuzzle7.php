<?php

namespace Mcustiel\Phiremock\Codeception\Module\Tests\Helpers;

use GuzzleHttp;
use Mcustiel\Phiremock\Server\Factory\Factory;
use Psr\Http\Client\ClientInterface;

class FactoryWithGuzzle7 extends Factory
{
    public function createHttpClient(): ClientInterface
    {
        return new GuzzleHttp\Client(['allow_redirects' => false]);
    }
}
