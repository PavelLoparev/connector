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

  public function emit($signal, $data) {
    $connections = ConnectionManager::getConnections();
    $sender_hash = spl_object_hash($this);

    if (!empty($connections[$sender_hash][$signal])) {
      // Run all connected slots.
      foreach ($connections[$sender_hash][$signal] as $connection_index => $connection) {
        call_user_func([$connection['receiver'], $connection['slot']], $data);

        // Remove connection if type is "one-time".
        if ($connection['type'] == ConnectionManager::CONNECTION_ONE_TIME) {
          ConnectionManager::disconnect($this, $signal, $connection['receiver'], $connection['slot']);
        }
      }
    }
  }

}
