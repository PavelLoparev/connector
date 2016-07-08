<?php

/**
 * @file
 * Contains definition of Sender class.
 */

namespace Connector\Tests;

use Fluffy\Connector\Signal\SignalInterface;
use Fluffy\Connector\Signal\SignalTrait;

/**
 * Class Sender.
 */
class Sender implements SignalInterface {
  use SignalTrait;
}
