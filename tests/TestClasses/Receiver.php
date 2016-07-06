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

  public function slotOne($data) {
    echo "Received data (slot 1): $data[0]" . PHP_EOL;
  }

  public function slotTwo($data) {
    echo "Received data (slot 2): $data[0]" . PHP_EOL;
  }

}
