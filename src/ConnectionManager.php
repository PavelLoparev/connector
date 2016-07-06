<?php

/**
 * @file
 * Contains definition of ConnectionManager class.
 */

namespace Fluffy\Connector;

/**
 * Class ConnectionManager.
 */
final class ConnectionManager
{

  const CONNECTION_PERMANENT = 0;
  const CONNECTION_ONCE = 1;

  private static $connections = NULL;

  /**
   * Connect sender's signal to receiver's slot.
   *
   * @param object $sender
   *   Object that defines a $signal.
   * @param string $signal
   *   Signal name.
   * @param object $receiver
   *   Object that defines a $slot.
   * @param string $slot
   *   Slot name.
   * @param int $connection_type
   *   Connection type. CONNECTION_PERMANENT will work until it is disconnected.
   *   CONNECTION_ONCE will work only once.
   */
  public static function connect($sender, $signal, $receiver, $slot, $connection_type = self::CONNECTION_PERMANENT)
  {
    $sender_hash = spl_object_hash($sender);

    // Add new connection.
    if (empty(self::$connections[$sender_hash]) || empty(self::$connections[$sender_hash][$signal])) {
      self::$connections[$sender_hash][$signal][] = array(
        'receiver' => $receiver,
        'slot' => $slot,
        'type' => $connection_type,
      );
    }
    else {
      // Add new connection for same signal and receiver.
      if (!empty(self::$connections[$sender_hash][$signal])) {
        foreach (self::$connections[$sender_hash][$signal] as $connection_index => $connection) {
          if ($connection['slot'] != $slot) {
            self::$connections[$sender_hash][$signal][] = array(
              'receiver' => $receiver,
              'slot' => $slot,
              'type' => $connection_type,
            );
          }
        }
      }
    }
  }

  /**
   * Disconnect receiver's slot from sender's signal.
   *
   * @param object $sender
   *   Object that defines a $signal.
   * @param string $signal
   *   Signal name.
   * @param object $receiver
   *   Object that defines a $slot.
   * @param string $slot
   *   Slot name.
   */
  public static function disconnect($sender, $signal, $receiver, $slot) {
    $sender_hash = spl_object_hash($sender);

    // Find and remove connection.
    if (!empty(self::$connections[$sender_hash]) && !empty(self::$connections[$sender_hash][$signal])) {
      foreach (self::$connections[$sender_hash][$signal] as $connection_index => $connection) {
        $receiver_hash = spl_object_hash($receiver);

        if (spl_object_hash($connection['receiver']) == $receiver_hash && $connection['slot'] == $slot) {
          unset(self::$connections[$sender_hash][$signal][$connection_index]);
        }

        if (empty(self::$connections[$sender_hash][$signal])) {
          unset(self::$connections[$sender_hash]);
        }
      }
    }
  }

  public static function getConnections()
  {
    return self::$connections;
  }

}