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

    ConnectionManager::connect($sender, 'testSignal', $receiver, 'slotOne');

    $sender->emit('testSignal', 'Signal data');
    $this->expectOutputString('Received data (slot 1): Signal data' . PHP_EOL);
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

    ConnectionManager::connect($sender, 'testSignal', $receiver1, 'slotOne');
    ConnectionManager::connect($sender, 'testSignal', $receiver2, 'slotTwo');

    $sender->emit('testSignal', 'Signal data');
    $this->expectOutputString('Received data (slot 1): Signal data' . PHP_EOL . 'Received data (slot 2): Signal data' . PHP_EOL);
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

    ConnectionManager::connect($sender1, 'testSignalOne', $receiver1, 'slotOne');
    ConnectionManager::connect($sender2, 'testSignalTwo', $receiver2, 'slotTwo');

    $sender1->emit('testSignalOne', 'Signal data 1');
    $sender2->emit('testSignalTwo', 'Signal data 2');
    $this->expectOutputString('Received data (slot 1): Signal data 1' . PHP_EOL . 'Received data (slot 2): Signal data 2' . PHP_EOL);
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

    ConnectionManager::connect($sender1, 'testSignalOne', $receiver, 'slotOne');
    ConnectionManager::connect($sender2, 'testSignalTwo', $receiver, 'slotOne');

    $sender1->emit('testSignalOne', 'Signal data 1');
    $sender2->emit('testSignalTwo', 'Signal data 2');
    $this->expectOutputString('Received data (slot 1): Signal data 1' . PHP_EOL . 'Received data (slot 1): Signal data 2' . PHP_EOL);
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

    ConnectionManager::connect($sender, 'testSignal', $receiver, 'slotOne');

    $sender->emit('testSignal', 'Signal data');
    $sender->emit('testSignal', 'Signal data');
    $this->expectOutputString('Received data (slot 1): Signal data' . PHP_EOL . 'Received data (slot 1): Signal data' . PHP_EOL);
  }

  /**
   * Test connection type: once.
   *
   * Connection with type "CONNECTION_ONE_TIME" will be disconnected after first
   * signal emission.
   */
  public function testOnceConnection()
  {
    ConnectionManager::resetAllConnections();

    $sender = new Sender();
    $receiver = new Receiver();

    ConnectionManager::connect($sender, 'testSignal', $receiver, 'slotOne', ConnectionManager::CONNECTION_ONE_TIME);

    $sender->emit('testSignal', 'Signal data');
    $sender->emit('testSignal', 'Signal data');
    $this->expectOutputString('Received data (slot 1): Signal data' . PHP_EOL);
  }

  /**
   * Test disconnect.
   *
   * Connect, emit signal, disconnect and emit once again.
   */
  public function testDisconnect()
  {
    ConnectionManager::resetAllConnections();

    $sender = new Sender();
    $receiver = new Receiver();

    ConnectionManager::connect($sender, 'testSignal', $receiver, 'slotOne');

    $sender->emit('testSignal', 'Signal data');

    ConnectionManager::disconnect($sender, 'testSignal', $receiver, 'slotOne');

    $sender->emit('testSignal', 'Signal data');
    $this->expectOutputString('Received data (slot 1): Signal data' . PHP_EOL);
  }

  /**
   * Test reset all connections.
   *
   * Connect two slots to two signals, emit signals, reset all connections and
   * emit once again.
   */
  public function testResetAllConnections()
  {
    ConnectionManager::resetAllConnections();

    $sender1 = new Sender();
    $sender2 = new Sender();
    $receiver = new Receiver();

    ConnectionManager::connect($sender1, 'testSignalOne', $receiver, 'slotOne');
    ConnectionManager::connect($sender2, 'testSignalTwo', $receiver, 'slotTwo');

    $sender1->emit('testSignalOne', 'Signal data 1');
    $sender2->emit('testSignalTwo', 'Signal data 2');

    ConnectionManager::resetAllConnections();

    $sender1->emit('testSignalOne', 'Signal data 1');
    $sender2->emit('testSignalTwo', 'Signal data 2');
    $this->expectOutputString('Received data (slot 1): Signal data 1' . PHP_EOL . 'Received data (slot 2): Signal data 2' . PHP_EOL);
  }

}
