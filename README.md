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
    "fluffy/connector": "^1.2"
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

Since you called `ConnectionManager::connect($sender, $signalName, $receiver, $slotName);` method signal and slot are connected. It means that after call `$logger->log()` the `somethingIsLogged` signal will be emitted and the `slotReactOnSignal` slot will be called. Result will be `"Received data: Some useful data"`. You can connect as many slots to signals as you want. Actually you can create connections like:

* One signal to one slot
* One signal to many slots
* Many signals to many slots
* Many signals to one slot

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

# Tests
`$ composer install`

`$ phpunit`

# What for?
I just like Qt's signal and slot system and want to bring it into PHP world.

# Any advantages?
It's lightweight and doesn't have any dependencies.

# License
GPLv3. See LICENSE file.
