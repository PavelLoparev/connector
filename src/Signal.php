<?php

/**
 * @file
 * Contains definition of signal class.
 */

namespace Fluffy\Connector;

/**
 * Class Signal.
 */
abstract class Signal
{

  /**
   * Emit signal with parameters.
   *
   * @param string $signal
   *   Signal name.
   * @param mixed $data
   *   Passed into slot arguments.
   */
  public function emit($signal, $data)
  {
    $connections = ConnectionManager::getConnections();
    $sender_hash = spl_object_hash($this);

    if (!empty($connections[$sender_hash][$signal])) {
      // Run all connected slots.
      foreach ($connections[$sender_hash][$signal] as $connection_index => $connection) {
        call_user_func(array($connection['receiver'], $connection['slot']), $data);

        // Remove connection if type is "one-time".
        if ($connection['type'] == ConnectionManager::CONNECTION_ONE_TIME) {
          ConnectionManager::disconnect($this, $signal, $connection['receiver'], $connection['slot']);
        }
      }
    }
  }

}
