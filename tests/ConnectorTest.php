<?php

/**
 * @file
 * Contains definition of ConnectorTest class.
 */

/**
 * Class ConnectorTest.
 */

use PHPUnit\Framework\TestCase;
use Fluffy\Connector\ConnectionManager;

class ConnectorTest extends TestCase
{

  public function __construct()
  {
    require_once "TestClasses/Sender.php";
    require_once "TestClasses/Receiver.php";
  }

  /**
   * Test connection from one signal to one slot.
   *
   * One sender emits signal. One receiver reacts on one signal.
   */
  public function testOneToOneConnection()
  {
    ConnectionManager::resetAllConnections();

    $sender = new Sender();
    $receiver = new Receiver();

    ConnectionManager::connect($sender, 'emitTestSignal', $receiver, 'slotOne');

    $this->expectOutputString('Received data (slot 1): Signal data' . PHP_EOL);
    $sender->emitTestSignal('Signal data');
  }

  /**
   * Test connection from one signal to many slots.
   *
   * One sender emits signal. Two receivers react on one signal.
   */
  public function testOneToManyConnection()
  {
    ConnectionManager::resetAllConnections();

    $sender = new Sender();
    $receiver1 = new Receiver();
    $receiver2 = new Receiver();

    ConnectionManager::connect($sender, 'emitTestSignal', $receiver1, 'slotOne');
    ConnectionManager::connect($sender, 'emitTestSignal', $receiver2, 'slotTwo');

    $this->expectOutputString('Received data (slot 1): Signal data' . PHP_EOL . 'Received data (slot 2): Signal data' . PHP_EOL);
    $sender->emitTestSignal('Signal data');
  }

  /**
   * Test connection from many signals to many slots.
   *
   * Two senders emit signals. Two receivers react on signals.
   */
  public function testManyToManyConnection()
  {
    ConnectionManager::resetAllConnections();

    $sender1 = new Sender();
    $sender2 = new Sender();
    $receiver1 = new Receiver();
    $receiver2 = new Receiver();

    ConnectionManager::connect($sender1, 'emitTestSignalOne', $receiver1, 'slotOne');
    ConnectionManager::connect($sender2, 'emitTestSignalTwo', $receiver2, 'slotTwo');

    $this->expectOutputString('Received data (slot 1): Signal data 1' . PHP_EOL . 'Received data (slot 2): Signal data 2' . PHP_EOL);
    $sender1->emitTestSignalOne('Signal data 1');
    $sender2->emitTestSignalTwo('Signal data 2');
  }

  /**
   * Test connection from many signals to many slots.
   *
   * Two senders emit signals. One receiver reacts on signals.
   */
  public function testManyToOneConnection()
  {
    ConnectionManager::resetAllConnections();

    $sender1 = new Sender();
    $sender2 = new Sender();
    $receiver = new Receiver();

    ConnectionManager::connect($sender1, 'emitTestSignalOne', $receiver, 'slotOne');
    ConnectionManager::connect($sender2, 'emitTestSignalTwo', $receiver, 'slotOne');

    $this->expectOutputString('Received data (slot 1): Signal data 1' . PHP_EOL . 'Received data (slot 1): Signal data 2' . PHP_EOL);
    $sender1->emitTestSignalOne('Signal data 1');
    $sender2->emitTestSignalTwo('Signal data 2');
  }

  /**
   * Test connection type: permanent.
   *
   * Connection with type "CONNECTION_PERMANENT" will not be disconnected after
   * first signal emission.
   */
  public function testPermanentConnection()
  {
    ConnectionManager::resetAllConnections();

    $sender = new Sender();
    $receiver = new Receiver();

    ConnectionManager::connect($sender, 'emitTestSignal', $receiver, 'slotOne');

    $this->expectOutputString('Received data (slot 1): Signal data' . PHP_EOL . 'Received data (slot 1): Signal data' . PHP_EOL);
    $sender->emitTestSignal('Signal data');
    $sender->emitTestSignal('Signal data');
  }

  /**
   * Test connection type: once.
   *
   * Connection with type "CONNECTION_ONCE" will be disconnected after first
   * signal emission.
   */
  public function testOnceConnection()
  {
    ConnectionManager::resetAllConnections();

    $sender = new Sender();
    $receiver = new Receiver();

    ConnectionManager::connect($sender, 'emitTestSignal', $receiver, 'slotOne', ConnectionManager::CONNECTION_ONCE);

    $this->expectOutputString('Received data (slot 1): Signal data' . PHP_EOL);
    $sender->emitTestSignal('Signal data');
    $sender->emitTestSignal('Signal data');
  }
}
