<?php

/**
 * @file
 * Contains definition of signal trait.
 */

namespace Fluffy\Connector\Signal;

use Fluffy\Connector\ConnectionManager;

/**
 * Trait SignalTrait
 * @package Fluffy\Connector\Signal
 */
trait SignalTrait {


  /**
   * Emits signal with a data.
   *
   * @param string $signal
   *   Signal name.
   * @param mixed $data
   *   Signal data.
   *
   * @return array
   */
  public function emit($signal, $data) {
    $errors = [];
    $connections = ConnectionManager::getConnections();
    $sender_hash = spl_object_hash($this);

    if (!empty($connections[$sender_hash][$signal])) {
      // Run all connected slots.
      foreach ($connections[$sender_hash][$signal] as $connection_index => $connection) {
        if (method_exists($connection['receiver'], $connection['slot'])) {
          call_user_func([$connection['receiver'], $connection['slot']], $data);

          // Remove connection if type is "one-time".
          if ($connection['type'] == ConnectionManager::CONNECTION_ONE_TIME) {
            ConnectionManager::disconnect($this, $signal, $connection['receiver'], $connection['slot']);
          }
        }
        else {
          $errors[] = 'Trying to call undefined slot "' . $connection['slot'] . '" in a "' . get_class($connection['receiver']) . '" class.';
        }
      }
    }

    return $errors;
  }

}
