[![Build Status](https://travis-ci.org/PavelLoparev/connector.svg?branch=master)](https://travis-ci.org/PavelLoparev/connector)

# Connector - "Signals and slots" mechanism for PHP.
This library is inspired by Qt "Signals and slots" mechanism so it's pretty similar.

# Signals and slots
Signal is something that tell outer world about the internal state of an object. For example: phones can ring, cats can meow etc. Rings and meows are signals. They tell us about changing in their internal states.

You can react on that signals in some way. For example: answer the call or feed the cat. Your reactions are slots.

There are tha same situations in programming: sometimes we need to react on some changes in object's state. And that's what this library for: it makes communication between objects easier. Even easier than it could be with "Observer" pattern.

# Installation
Run
```
$ composer require fluffy/connector
```

or add dependency to your composer.json file

```javascript
"require": {
    ...
    "fluffy/connector": "^1.3"
}
```

# Usage

**1. Signals**

If you want your object will be able to emit signals you need to implement `SignalInterface` and use `SignalTrait`. For example you have some logger class and you want to emit signal `somethingIsLogged` when logger finished work:
```php
<?php

/**
 * @file
 * Contains definition of Logger class.
 */

use Fluffy\Connector\Signal\SignalInterface;
use Fluffy\Connector\Signal\SignalTrait;

/**
 * Class Logger.
 */
class Logger implements SignalInterface {
    use SignalTrait;

    public function log() 
    {
        // Do logging stuff.
        ...

        // Emit signal about successfull logging.
        $this->emit('somethingIsLogged', 'Some useful data');
    }
}
```

To emit signal you need to call `emit` method and pass signal name and data. You can pass whatever you want: array, string, object or number. That's all. Now your logger emits signal to outer world. But nobody is connected to this signal yet. Let do this stuff.

**2. Slots**

Slot it's a usual class method. Let's define a class with a slot.
```php
<?php

/**
 * @file
 * Contains definition of Receiver class.
 */

/**
 * Class Receiver.
 */
class Receiver
{

  public function slotReactOnSignal($dataFromSignal) {
    echo "Received data: $dataFromSignal";
  }

}

```

**3. Connections**
For now we have `Logger` class that emits signal and `Receiver` class with a slot. To react on signal with slot you need to connect them to each other. Let's do it.
```php
use Fluffy\Connector\ConnectionManager;

$logger = new Logger();
$receiver = new Receiver();

ConnectionManager::connect($logger, 'somethingIsLogged', $receiver, 'slotReactOnSignal');

$logger->log();
```

Since you called `ConnectionManager::connect(SignalInterface $sender, $signalName, $receiver, $slotName);` method signal and slot are connected. It means that after call `$logger->log()` the `somethingIsLogged` signal will be emitted and the `slotReactOnSignal` slot will be called. Result will be `"Received data: Some useful data"`. You can connect as many slots to signals as you want. Actually you can create connections like:

* One signal to one slot
* One signal to many slots
* Many signals to many slots
* Many signals to one slot

You can also establish multiple connections by calling `ConnectionManager::initConnections(array $connections);` method:
```php
ConnectionManager::initConnections([
  ...
  [
    'sender' => new Logger(),
    'signal' => 'somethingIsLogged',
    'receiver' => new Receiver(),
    'slot' => 'slotReactOnSignal',
    'type' => ConnectionManager::CONNECTION_PERMANENT,
  ]
  ...
]);
```

**4. Connection types**

By default `ConnectionManager::connect()` method creates permanent connections. It means that slot will not be disconnected from signal after first emission. But you can make one-time connection. Just pass 5th parameter to `ConnectionManager::connect()` method as `ConnectionManager::CONNECTION_ONE_TIME`. For example:
```php
use Fluffy\Connector\ConnectionManager;

$logger = new Logger();
$receiver = new Receiver();

ConnectionManager::connect($logger, 'somethingIsLogged', $receiver, 'slotReactOnSignal', ConnectionManager::CONNECTION_ONE_TIME);

$logger->log();

// Log once again.
$logger->log();
```

After second call of `Logger::log()` nothing will happen because slot will be disconnected from signal after first emission.

**5. Disconnect**

If you don't want to listen signal anymore just disconnect from it.
```php
ConnectionManager::disconnect($logger, 'somethingIsLogged', $receiver, 'slotReactOnSignal');
```
If you want to reset all existing connections call
```php
ConnectionManager::resetAllConnections()
```

**6. Services connections**

If you are using Symfony Dependency Injection component you might don't want to create objects manually but retrieve them from service container instead. For such cases you can connect your services defined in `services.yml` file without any manual object creation. That's how you can achieve this:

1. Let's say you have `services.yml` file with next services:
```php
services:
  service.logger:
    class: \Logger
    arguments: [...]
  service.receiver:
    class: \Receiver
    arguments: [...]
```

2. In order to connect a `\Receiver` slot `slotReactOnSignal` to a `\Logger` signal `somethingIsLogged` create a yaml file somewhere (let's say `services.connections.yml`) in you project with next content:
```yml
# Connection name. Can be any string.
test_connection_one:
  # Sender service id from "services.yml" file.
  sender: service.logger
  # Sender's signal.
  signal: somethingIsLogged
  # Receiver service id from "services.yml" file.
  receiver: service.receiver
  # Receiver's slot.
  slot: slotReactOnSignal
  # Connection type. 0 - "permanent". 1 - "one time".
  # You can ommit "type" parameter and it will be
  # "permanent" by default.
  type: 0

  # You can define as many connections as you want.
  ...
```

3. Initialize service connections:
```php
// Here you need to pass a yaml string from file and a service container.
// This should be done once somewhere in a front controller of your
// application.
$serviceConnections = ConnectionManager::parseServicesConnections(file_get_contents('services.connections.yml'), $container);
ConnectionManager::initConnections($serviceConnections);
```

4. Now yor `\Logger` service is ready to emit signals and `\Receiver` service is ready to react on signals:
```php
// Receiver will respond to signal "somethingIsLogged" with a slot defined in "services.connections.yml".
$container->get('service.logger')->emit('somethingIsLogged', 'Signal data');
```

# Tests
Please [see tests](https://github.com/PavelLoparev/connector/tree/master/tests) for more information and use-cases.

in order to run tests type:
`$ composer install`

`$ ./vendor/bin/phpunit `

# What for?
I just like Qt's signal and slot system and want to bring it into PHP world.

# Any advantages?
* It's lightweight.
* Depends only on one third party library: symfony/yaml
# License
GPLv3. See LICENSE file.
