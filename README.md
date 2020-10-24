# phiremock-codeception-extension
Codeception extension to make working with [Phiremock Server](https://github.com/mcustiel/phiremock-server) even easier. It allows to start a Phiremock Server before each suite and stop it when the suite ends.

[![Latest Stable Version](https://poser.pugx.org/mcustiel/phiremock-codeception-extension/v/stable)](https://packagist.org/packages/mcustiel/phiremock-codeception-extension)
[![Build Status](https://scrutinizer-ci.com/g/mcustiel/phiremock-codeception-extension/badges/build.png?b=master)](https://scrutinizer-ci.com/g/mcustiel/phiremock-codeception-extension/build-status/master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/mcustiel/phiremock-codeception-extension/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/mcustiel/phiremock-codeception-extension/?branch=master)
[![Monthly Downloads](https://poser.pugx.org/mcustiel/phiremock-codeception-extension/d/monthly)](https://packagist.org/packages/mcustiel/phiremock-codeception-extension)

## Installation

### Composer:

```json
    "require-dev": {
        "mcustiel/phiremock-codeception-extension": "v2.0"
    }
```

Optionally, you can install Phiremock Server in case you want to have it between your dependencies. If not, you need to specify the path to phiremock in the configuration.

```json
"require-dev": {
    "mcustiel/phiremock-codeception-extension": "v2.0",
    "mcustiel/phiremock-server": "^1.0",
    "guzzlehttp/guzzle": "^6.0"
```

Phiremock server has been made an optional dependency in case you want to run it from a phar file, a global composer dependency or in a docker container, and not have it as a project dependency.

## Configuration

```yaml
extensions:
    enabled:
        - \Codeception\Extension\Phiremock
    config:
        \Codeception\Extension\Phiremock:
            listen: 127.0.0.1:18080 # defaults to 0.0.0.0:8086 
            bin_path: ../vendor/bin # defaults to codeception_dir/../vendor/bin 
            logs_path: /var/log/my_app/tests/logs # defaults to codeception's tests output dir
            debug: true # defaults to false
            start_delay: 1 # default to 0
            expectations_path: /my/expectations/path # defaults to tests/_expectations
            server_factory: \My\FactoryClass # defaults to 'default'
```
Note: Since Codeception version 2.2.7, extensions configuration can be added directly in the suite configuration file. That will avoid phiremock to be started for every suite. 

### Parameters

#### listen
Specifies the interface and port where phiremock must listen for requests.
**Default:** 0.0.0.0:8086

#### bin_path
Path where Phiremock Server's "binary" is located. You can, for instance, point to the location of the phar in your file system.
**Default:** codeception_dir/../vendor/bin/phiremock

#### logs_path
Path where to write the output.
**Default:** codeception's tests output dir

#### debug
Whether to write debug data to log file.
**Default:** false

#### start_delay
Time to wait after Phiremock Server is started before running the tests (used to give time to Phiremock Server to boot) 
**Default:** 0

#### expectations_path
Specifies a directory to search for json files defining expectations to load by default.
**Default:** codecption_dir/_expectations

#### server_factory
Specifies a Factory class extending `\Mcustiel\Phiremock\Server\Factory\Factory`. Useful if you want to provide your own PSR. This works only if you install phiremock as a local dependency required in your composer file.
**Default:** default

**Example:**
If this is in your composer.json:

```json
"require-dev": {
    "mcustiel/phiremock-codeception-extension": "v2.0",
    "mcustiel/phiremock-server": "^1.0",
    "guzzlehttp/guzzle": "^7.0"
```

The you can create a factory as follows:

```php
<?php
namespace My\Namespace;

use GuzzleHttp;
use Mcustiel\Phiremock\Server\Factory\Factory;
use Psr\Http\Client\ClientInterface;

class FactoryWithGuzzle7 extends Factory
{
    public function createHttpClient(): ClientInterface
    {
        return new GuzzleHttp\Client();
    }
}
```

and in the extension config provide the fully qualified namespace to that class:

```yaml
 enabled:
        - \Codeception\Extension\Phiremock
    config:
        \Codeception\Extension\Phiremock:
            server_factory: \My\Namespace\FactoryWithGuzzle7
```

## See also:

* Phiremock Server: https://github.com/mcustiel/phiremock-server
* Phiremock Codeception Module: https://github.com/mcustiel/phiremock-codeception-module
