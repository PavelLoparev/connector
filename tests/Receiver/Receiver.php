<?php

/**
 * @file
 * Contains definition of Receiver class.
 */

namespace Fluffy\Connector\Tests\Receiver;

/**
 * Class Receiver.
 */
class Receiver {

  public function slotOne($data) {
    echo $data . PHP_EOL;
  }

  public function slotTwo($data) {
    echo $data . PHP_EOL;
  }

}
