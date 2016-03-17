# phiremock-codeception-extension
Codeception extension and module to make working with [Phiremock](https://github.com/mcustiel/phiremock) even easier. It allows to start a Phiremock server  specifically for the acceptance tests to run or to connect to an already running Phiremock server.

# Installation

### Composer:

This project is published in packagist, so you just need to add it as a dependency in your composer.json:

```json
    "require": {
        "mcustiel/phiremock-codeception-extension": "*"
    }
```

## How to use

### Extension
The extension provides an easy way to start a Phiremock server with configured host, port, debug mode and logs path.

#### Configuration
In codeception.yml you will need to enable Phiremock extension and configure it in a proper way:

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
```

### Module
The module allows you to connect to a Phiremock server and to interact with it in a semantic way through the codeception actor in your tests.

#### Configuration
You need to enable Phiremock module in your suite's configuration file:

```yaml
modules:
    enabled:
        - Phiremock:
            host: 127.0.0.1
            port: 18080
```

#### Use
The module provides the following handy methods to communicate with Phiremock server:

#### expectARequestToRemoteServiceWithAResponse
Allows you to setup an expectation in Phiremock, specifying the expected request and the response the server should give for it:

```php
    $I->expectARequestToRemoteServiceWithAResponse(
        Phiremock::on(
            A::getRequest()->andUrl(Is::equalTo('/some/url'))
        )->then(
            Respond::withStatusCode(203)->andBody('I am a response')
        )
    );
```

#### haveACleanSetupInRemoteService
Cleans the server of all configured expectations, scenarios and requests history.

```php
    $I->haveACleanSetupInRemoteService();
```

#### dontExpectRequestsInRemoteService
Cleans all previously configured expectations and requests history.

```php
    $I->dontExpectRequestsInRemoteService();
```

#### haveCleanScenariosInRemoteService
Cleans the state of all scenarios (sets all of them to inital state).

```php
    $I->haveCleanScenariosInRemoteService();
```

#### seeRemoteServiceReceived
Allows you to verify that the server received a request a given amount of times. This request could or not be previously set up as an expectation.

```php
    $I->seeRemoteServiceReceived(1, A::getRequest()->andUrl(Is::equalTo('/some/url')));
```
