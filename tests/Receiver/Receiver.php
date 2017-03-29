<?php

/**
 * @file
 * Contains definition of Receiver class.
 */

namespace Fluffy\Tests\Receiver;

/**
 * Class Receiver.
 */
class Receiver {

  public function slotOne($data) {
    echo "Received data (slot 1): $data" . PHP_EOL;
  }

  public function slotTwo($data) {
    echo "Received data (slot 2): $data" . PHP_EOL;
  }

}
