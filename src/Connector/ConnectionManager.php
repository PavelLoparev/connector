<?php

/**
 * @file
 * Contains definition of ConnectionManager class.
 */

namespace Fluffy\Connector;

use Symfony\Component\Config\Exception\FileLocatorFileNotFoundException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Yaml\Yaml;

/**
 * Class ConnectionManager
 * @package Fluffy\Connector
 */
final class ConnectionManager {

  const CONNECTION_PERMANENT = 0;
  const CONNECTION_ONE_TIME = 1;

  private static $connections = NULL;

  public static function init($path) {
    $container = new ContainerBuilder();
    $serviceLoader = new YamlFileLoader($container, new FileLocator($path));
    $connections = Yaml::parse(file_get_contents($path . '/connections.yml'));

    try {
      $serviceLoader->load('services.yml');
      $test = $container->get('test.service');
    }
    catch (FileLocatorFileNotFoundException $e) {
      // TODO: implement exception handling.
    }
  }

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
   *   CONNECTION_ONE_TIME will work only once.
   */
  public static function connect($sender, $signal, $receiver, $slot, $connection_type = self::CONNECTION_PERMANENT) {
    $sender_hash = spl_object_hash($sender);
    $connection_item = [
      'receiver' => $receiver,
      'slot' => $slot,
      'type' => $connection_type,
    ];

    // Add new connection.
    if (empty(self::$connections[$sender_hash]) || empty(self::$connections[$sender_hash][$signal])) {
      self::$connections[$sender_hash][$signal][] = $connection_item;
    }
    else {
      // Add new connection for same signal and receiver.
      if (!empty(self::$connections[$sender_hash][$signal])) {
        foreach (self::$connections[$sender_hash][$signal] as $connection) {
          if ($connection['slot'] != $slot) {
            self::$connections[$sender_hash][$signal][] = $connection_item;
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

  /**
   * Returns all defined connections.
   *
   * @return array
   *   Defined connections keyed by sender object hash.
   */
  public static function getConnections() {
    return self::$connections;
  }

  /**
   * Resets all defined connections.
   */
  public static function resetAllConnections() {
    self::$connections = [];
  }
}
