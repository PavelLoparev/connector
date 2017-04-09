<?php

/**
 * @file
 * Contains definition of ConnectionManager class.
 */

namespace Fluffy\Connector;

use Fluffy\Connector\Signal\SignalInterface;
use RuntimeException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * Class ConnectionManager
 * @package Fluffy\Connector
 */
final class ConnectionManager {

  /**
   * Constant defines permanent connection type.
   */
  const CONNECTION_PERMANENT = 0;

  /**
   * Constant defines one time connection type.
   */
  const CONNECTION_ONE_TIME = 1;

  /**
   * Constant defines granularity 'all'.
   */
  const GRANULARITY_ALL = 0;

  /**
   * Constant defines granularity 'sender'.
   */
  const GRANULARITY_SENDER = 1;

  /**
   * Constant defines granularity 'sender and signal'.
   */
  const GRANULARITY_SENDER_SIGNAL = 2;

  /**
   * Constant defines granularity 'sender, signal and receiver'.
   */
  const GRANULARITY_SENDER_SIGNAL_RECEIVER = 3;

  /**
   * Constant defines granularity 'sender, signal, receiver and slot'.
   */
  const GRANULARITY_SENDER_SIGNAL_RECEIVER_SLOT = 4;

  /**
   * @var array
   */
  private static $connections = [];

  /**
   * Connect sender's signal to receiver's slot.
   *
   * @param \Fluffy\Connector\Signal\SignalInterface|object $sender
   *   Object that defines a $signal.
   * @param string $signal
   *   Signal name.
   * @param object $receiver
   *   Object that defines a $slot.
   * @param string $slot
   *   Slot name.
   * @param int $connectionType
   *   Connection type. CONNECTION_PERMANENT will work until it is disconnected.
   *   CONNECTION_ONE_TIME will work only once.
   * @param int $weight
   *   Connection weight.
   */
  public static function connect(SignalInterface $sender, $signal, $receiver, $slot, $connectionType = ConnectionManager::CONNECTION_PERMANENT, $weight = 0) {
    $senderHash = spl_object_hash($sender);
    $connectionItem = [
      'receiver' => $receiver,
      'slot' => $slot,
      'type' => $connectionType,
      'weight' => $weight,
    ];

    // Add new connection.
    if (empty(self::$connections[$senderHash]) || empty(self::$connections[$senderHash][$signal])) {
      self::$connections[$senderHash][$signal][] = $connectionItem + ['key' => 0];
    }
    else {
      // Add new connection for existing signal and receiver.
      if (!empty(self::$connections[$senderHash][$signal])) {
        $addConnection = TRUE;

        // Find out if connection for this receiver and slot is already
        // exists.
        foreach (self::$connections[$senderHash][$signal] as $connection) {
          if ($connection['receiver'] === $receiver && $connection['slot'] == $slot) {
            $addConnection = FALSE;
            break;
          }
        }

        // Add connection for existing signal.
        if (!empty($addConnection)) {
          end(self::$connections[$senderHash][$signal]);
          $key = key(self::$connections[$senderHash][$signal]);
          self::$connections[$senderHash][$signal][] = $connectionItem + ['key' => $key + 1];
        }
      }
    }

    // Perform slots stable sorting depends on connection weight.
    usort(self::$connections[$senderHash][$signal], function($a, $b) {
      if ($a['weight'] == $b['weight']) {
        $result = $a['key'] < $b['key'] ? -1 : 1;
      }
      else {
        $result = $a['weight'] < $b['weight'] ? -1 : 1;
      }

      return $result;
    });
  }

  /**
   * Disconnect receiver's slot from sender's signal.
   *
   * @param \Fluffy\Connector\Signal\SignalInterface|object $sender
   *   Object that defines a $signal.
   * @param string $signal
   *   Signal name.
   * @param object $receiver
   *   Object that defines a $slot.
   * @param string $slot
   *   Slot name.
   */
  public static function disconnect(SignalInterface $sender, $signal = NULL, $receiver = NULL, $slot = NULL) {
    $senderHash = spl_object_hash($sender);

    switch (self::getGranularity($sender, $signal, $receiver, $slot)) {
      // Disconnect all receivers from all signals for a given sender.
      case self::GRANULARITY_SENDER:
        unset(self::$connections[$senderHash]);

        break;

      // Disconnect all receivers from a given signal.
      case self::GRANULARITY_SENDER_SIGNAL:
        unset(self::$connections[$senderHash][$signal]);

        break;

      // Disconnect a given receiver's all slots from a given signal.
      case self::GRANULARITY_SENDER_SIGNAL_RECEIVER:
        if (!empty(self::$connections[$senderHash][$signal])) {
          foreach (self::$connections[$senderHash][$signal] as $connectionIndex => $connection) {
            if ($connection['receiver'] === $receiver) {
              unset(self::$connections[$senderHash][$signal][$connectionIndex]);
            }

            if (empty(self::$connections[$senderHash][$signal])) {
              unset(self::$connections[$senderHash]);
            }
          }
        }

        break;

      // Disconnect a given receiver's slot from a given signal.
      case self::GRANULARITY_SENDER_SIGNAL_RECEIVER_SLOT:
        // Find and remove connection.
        if (!empty(self::$connections[$senderHash][$signal])) {
          foreach (self::$connections[$senderHash][$signal] as $connectionIndex => $connection) {
            if ($connection['receiver'] === $receiver && $connection['slot'] == $slot) {
              unset(self::$connections[$senderHash][$signal][$connectionIndex]);
            }

            if (empty(self::$connections[$senderHash][$signal])) {
              unset(self::$connections[$senderHash]);
            }
          }
        }

        break;
    }
  }

  /**
   * Initializes multiple connections.
   *
   * @param array $connections
   *   Array contains connection descriptions.
   */
  public static function initConnections(array $connections) {
    foreach ($connections as $connection) {
      if (
        empty($connection['sender']) ||
        empty($connection['signal']) ||
        empty($connection['receiver']) ||
        empty($connection['slot'])
      ) {
        throw new RuntimeException('Malformed connection.');
      }

      if (!empty($connection['type'])) {
        if (!in_array($connection['type'], [
            ConnectionManager::CONNECTION_PERMANENT,
            ConnectionManager::CONNECTION_ONE_TIME,
          ])
        ) {
          throw new RuntimeException('Unknown connection type.');
        }
      }
      else {
        $connection['type'] = ConnectionManager::CONNECTION_PERMANENT;
      }

      if (empty($connection['weight'])) {
        $connection['weight'] = 0;
      }

      ConnectionManager::connect($connection['sender'],
        $connection['signal'],
        $connection['receiver'],
        $connection['slot'],
        $connection['type'],
        $connection['weight']
      );
    }
  }

  /**
   * Parses service connections from yaml file into array.
   *
   * @param string $yaml
   *   Yaml string to parse.
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   Symfony service container.
   *
   * @return array
   */
  public static function parseServicesConnections($yaml, ContainerInterface $container) {
    try {
      $connections = Yaml::parse($yaml);

      foreach ($connections as &$connection) {
        if (!empty($connection['sender'])) {
          $connection['sender'] = $container->get($connection['sender']);
        }

        if (!empty($connection['receiver'])) {
          $connection['receiver'] = $container->get($connection['receiver']);
        }
      }
    }
    catch (ParseException $e) {
      $connections = [];
    }

    return $connections;
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

  /**
   * Returns granularity for given parameters.
   *
   * Uses in ConnectionManager::disconnect() function.
   *
   * @param \Fluffy\Connector\Signal\SignalInterface|NULL $sender
   * @param null $signal
   * @param null $receiver
   * @param null $slot
   *
   * @return int
   */
  private function getGranularity(SignalInterface $sender = NULL, $signal = NULL, $receiver = NULL, $slot = NULL) {
    $granularity = self::GRANULARITY_ALL;

    if (
      !empty($sender) &&
      empty($signal) &&
      empty($receiver) &&
      empty($slot)
    ) {
      $granularity = self::GRANULARITY_SENDER;
    }
    elseif (
      !empty($sender) &&
      !empty($signal) &&
      empty($receiver) &&
      empty($slot)
    ) {
      $granularity = self::GRANULARITY_SENDER_SIGNAL;
    }
    elseif (
      !empty($sender) &&
      !empty($signal) &&
      !empty($receiver) &&
      empty($slot)
    ) {
      $granularity = self::GRANULARITY_SENDER_SIGNAL_RECEIVER;
    }
    elseif (
      !empty($sender) &&
      !empty($signal) &&
      !empty($receiver) &&
      !empty($slot)
    ) {
      $granularity = self::GRANULARITY_SENDER_SIGNAL_RECEIVER_SLOT;
    }

    return $granularity;
  }

}
