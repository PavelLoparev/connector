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
   * Implements magic method __call.
   *
   * @param string $signal
   *   Signal name.
   * @param array $arguments
   *   Passed into slot arguments.
   */
  public function __call($signal, array $arguments)
  {
    // Signal methods must start from "emit" keyword.
    if (strpos($signal, 'emit') === 0) {
      $connections = ConnectionManager::getConnections();
      $sender_hash = spl_object_hash($this);

      if (!empty($connections[$sender_hash][$signal])) {
        // Run all connected slots.
        foreach ($connections[$sender_hash][$signal] as $connection_index => $connection) {
          call_user_func(array($connection['receiver'], $connection['slot']), $arguments);

          // Remove connection if type is "once".
          if ($connection['type'] == ConnectionManager::CONNECTION_ONCE) {
            ConnectionManager::disconnect($this, $signal, $connection['receiver'], $connection['slot']);
          }
        }
      }
    }
  }

}
