# phiremock-codeception-extension
Codeception extension to make working with [Phiremock Server](https://github.com/mcustiel/phiremock-server) even easier. It allows to start a Phiremock Server before a suite is executed and stop it when the suite ends.

[![Latest Stable Version](https://poser.pugx.org/mcustiel/phiremock-codeception-extension/v/stable)](https://packagist.org/packages/mcustiel/phiremock-codeception-extension)
[![Build Status](https://scrutinizer-ci.com/g/mcustiel/phiremock-codeception-extension/badges/build.png?b=master)](https://scrutinizer-ci.com/g/mcustiel/phiremock-codeception-extension/build-status/master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/mcustiel/phiremock-codeception-extension/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/mcustiel/phiremock-codeception-extension/?branch=master)
[![Monthly Downloads](https://poser.pugx.org/mcustiel/phiremock-codeception-extension/d/monthly)](https://packagist.org/packages/mcustiel/phiremock-codeception-extension)

## Installation

### Composer:

```json
    "require-dev": {
        "mcustiel/phiremock-codeception-extension": "^3.0"
    }
```

Optionally, you can install Phiremock Server in case you want to have it between your dependencies. If not, you need to specify the path to phiremock in the configuration.

```json
"require-dev": {
    "mcustiel/phiremock-codeception-extension": "^2.0",
    "mcustiel/phiremock-server": "^1.0",
    "guzzlehttp/guzzle": "^6.0"
}
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
            wait_until_ready: true # defaults to false
            wait_until_ready_timeout: 15 # (seconds) defaults to 30
            wait_until_ready_interval: 100 # (microseconds) defaults to 50000
            expectations_path: /my/expectations/path # defaults to tests/_expectations
            server_factory: \My\FactoryClass # defaults to 'default'
            extra_instances: [] # deaults to an empty array
            suites: [] # defaults to an empty array
            certificate: /path/to/cert # defaults to null
            certificate_key: /path/to/cert-key # defaults to null
            cert_passphrase: 'my-pass' # defaults to null
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

#### wait_until_ready
This is more robust alternative to start_delay. It will check if Phiremock Server is actually running before running the tests.
Note: it depends on Phiremeock Client to be installed via composer (it is used to check the status of Phiremock Server).

**Default:** false

#### wait_until_ready_timeout
This will be used only if wait_until_ready is set to true. You can specify after how many seconds it will stop checking if Phiremock Server is running.

**Default:** 30

#### expectations_path
Specifies a directory to search for json files defining expectations to load by default.

**Default:** codecption_dir/_expectations

#### certificate
Path to a certificate file to allow phiremock-server to listen for secure https connections. 

**Default:** null. Meaning phiremock will only listen on unsecured http connections.

#### certificate-key
Path to the certificate key file. 

**Default:** null. 

#### cert-passphrase
Path to the certificate passphrase used to encrypt the certificate (only needed if encrypted). 

**Default:** null. Meaning no decryption based in passphrase will be performed.

#### suites
Specifies a list of suites for which the phiremock-server must be executed.

**Default:** [] Empty array, meaning that phiremock will be executed for each suite.

#### extra_instances
Allows to specify more instances of phiremock-server to run. This is useful if you want, for instance, run one instance listening for http and one listening for https connections. Each instance has its own configuration, and can separately run for different suites.

**Default:** [] Empty array, meaning that no extra phiremock-server instances are configured.

**Example:**
```yaml
extensions:
    enabled:
        - \Codeception\Extension\Phiremock
    config:
        \Codeception\Extension\Phiremock:
            listen: 127.0.0.1:18080  
            debug: true 
            expectations_path: /my/expectations/path-1 
            suites: 
                - acceptance
            extra_instances: 
                - 
                    listen: 127.0.0.1:18081
                    debug: true
                    start_delay: 1
                    expectations_path: /my/expectations/path-2
                    suites:
                        - acceptance
                        - api
                    certificate: /path/to/cert
                    certificate_key: /path/to/cert-key 
                    cert_passphrase: 'my-pass' 
```

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
