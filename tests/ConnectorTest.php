<?php

/**
 * @file
 * Contains definition of ConnectorTest class.
 */

namespace Fluffy\Tests;

use PHPUnit\Framework\TestCase;
use Fluffy\Connector\ConnectionManager;
use Fluffy\Tests\Sender\Sender;
use Fluffy\Tests\Receiver\Receiver;
use RuntimeException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * Class ConnectorTest.
 */
class ConnectorTest extends TestCase {

  private $container;

  public function setUp() {
    $container = new ContainerBuilder();
    $serviceLoader = new YamlFileLoader($container, new FileLocator(__DIR__));
    $serviceLoader->load('services.yml');
    $this->container = $container;

    ConnectionManager::resetAllConnections();
  }

  /**
   * Test connection from one signal to one slot.
   *
   * One sender emits signal. One receiver reacts on one signal.
   */
  public function testOneToOneConnection() {
    $sender = new Sender();
    $receiver = $this->getMockBuilder(Receiver::class)
      ->setMethods(['slotOne'])
      ->getMock();

    $receiver->expects($this->once())
      ->method('slotOne')
      ->with('Signal data');

    ConnectionManager::connect($sender, 'testSignal', $receiver, 'slotOne');

    $sender->emit('testSignal', 'Signal data');
  }

  /**
   * Test connection from one signal to many slots.
   *
   * One sender emits signal. Two receivers react on one signal.
   */
  public function testOneToManyConnection() {
    $sender = new Sender();
    $receiver1 = $this->getMockBuilder(Receiver::class)
      ->setMethods(['slotOne'])
      ->getMock();

    $receiver1->expects($this->once())
      ->method('slotOne')
      ->with('Signal data');

    $receiver2 = $this->getMockBuilder(Receiver::class)
      ->setMethods(['slotTwo'])
      ->getMock();

    $receiver2->expects($this->once())
      ->method('slotTwo')
      ->with('Signal data');

    ConnectionManager::connect($sender, 'testSignal', $receiver1, 'slotOne');
    ConnectionManager::connect($sender, 'testSignal', $receiver2, 'slotTwo');

    $sender->emit('testSignal', 'Signal data');
  }

  /**
   * Test connection from many signals to many slots.
   *
   * Two senders emit signals. Two receivers react on signals.
   */
  public function testManyToManyConnection() {
    $sender1 = new Sender();
    $sender2 = new Sender();
    $receiver1 = $this->getMockBuilder(Receiver::class)
      ->setMethods(['slotOne'])
      ->getMock();

    $receiver1->expects($this->once())
      ->method('slotOne')
      ->with('Signal data 1');

    $receiver2 = $this->getMockBuilder(Receiver::class)
      ->setMethods(['slotTwo'])
      ->getMock();

    $receiver2->expects($this->once())
      ->method('slotTwo')
      ->with('Signal data 2');

    ConnectionManager::connect($sender1, 'testSignalOne', $receiver1, 'slotOne');
    ConnectionManager::connect($sender2, 'testSignalTwo', $receiver2, 'slotTwo');

    $sender1->emit('testSignalOne', 'Signal data 1');
    $sender2->emit('testSignalTwo', 'Signal data 2');
  }

  /**
   * Test connection from many signals to one slot.
   *
   * Two senders emit signals. One receiver reacts on signals.
   */
  public function testManyToOneConnection() {
    $sender1 = new Sender();
    $sender2 = new Sender();
    $receiver = $this->getMockBuilder(Receiver::class)
      ->setMethods(['slotOne'])
      ->getMock();

    $receiver->expects($this->exactly(2))
      ->method('slotOne')
      ->withConsecutive(['Signal data 1'], ['Signal data 2']);

    ConnectionManager::connect($sender1, 'testSignalOne', $receiver, 'slotOne');
    ConnectionManager::connect($sender2, 'testSignalTwo', $receiver, 'slotOne');

    $sender1->emit('testSignalOne', 'Signal data 1');
    $sender2->emit('testSignalTwo', 'Signal data 2');
  }

  /**
   * Test connection type: permanent.
   *
   * Connection with type "CONNECTION_PERMANENT" will not be disconnected after
   * first signal emission.
   */
  public function testPermanentConnection() {
    $sender = new Sender();
    $receiver = $this->getMockBuilder(Receiver::class)
      ->setMethods(['slotOne'])
      ->getMock();

    $receiver->expects($this->exactly(2))
      ->method('slotOne')
      ->with('Signal data');

    ConnectionManager::connect($sender, 'testSignal', $receiver, 'slotOne');

    $sender->emit('testSignal', 'Signal data');
    $sender->emit('testSignal', 'Signal data');
  }

  /**
   * Test connection type: once.
   *
   * Connection with type "CONNECTION_ONE_TIME" will be disconnected after first
   * signal emission.
   */
  public function testOnceConnection() {
    $sender = new Sender();
    $receiver = $this->getMockBuilder(Receiver::class)
      ->setMethods(['slotOne'])
      ->getMock();

    $receiver->expects($this->once())
      ->method('slotOne')
      ->with('Signal data');

    ConnectionManager::connect($sender, 'testSignal', $receiver, 'slotOne', ConnectionManager::CONNECTION_ONE_TIME);

    $sender->emit('testSignal', 'Signal data');
    $sender->emit('testSignal', 'Signal data');
  }

  /**
   * Test disconnect.
   *
   * Connect, emit signal, disconnect and emit once again.
   */
  public function testDisconnect() {
    $sender = new Sender();
    $receiver = $this->getMockBuilder(Receiver::class)
      ->setMethods(['slotOne'])
      ->getMock();

    $receiver->expects($this->once())
      ->method('slotOne')
      ->with('Signal data');

    ConnectionManager::connect($sender, 'testSignal', $receiver, 'slotOne');

    $sender->emit('testSignal', 'Signal data');

    ConnectionManager::disconnect($sender, 'testSignal', $receiver, 'slotOne');

    $sender->emit('testSignal', 'Signal data');
  }

  /**
   * Test reset all connections.
   *
   * Connect two slots to two signals, emit signals, reset all connections and
   * emit once again.
   */
  public function testResetAllConnections() {
    $sender1 = new Sender();
    $sender2 = new Sender();
    $receiver = $this->getMockBuilder(Receiver::class)
      ->setMethods(['slotOne', 'slotTwo'])
      ->getMock();

    $receiver->expects($this->exactly(1))
      ->method('slotOne')
      ->with('Signal data 1');

    $receiver->expects($this->exactly(1))
      ->method('slotTwo')
      ->with('Signal data 2');

    ConnectionManager::connect($sender1, 'testSignalOne', $receiver, 'slotOne');
    ConnectionManager::connect($sender2, 'testSignalTwo', $receiver, 'slotTwo');

    $sender1->emit('testSignalOne', 'Signal data 1');
    $sender2->emit('testSignalTwo', 'Signal data 2');

    ConnectionManager::resetAllConnections();

    $sender1->emit('testSignalOne', 'Signal data 1');
    $sender2->emit('testSignalTwo', 'Signal data 2');
  }

  /**
   * Test get connections.
   */
  public function testGetConnections() {
    $sender1 = new Sender();
    $sender2 = new Sender();
    $receiver1 = new Receiver();
    $receiver2 = new Receiver();

    ConnectionManager::connect($sender1, 'testSignalOne', $receiver1, 'slotOne');
    ConnectionManager::connect($sender2, 'testSignalTwo', $receiver2, 'slotTwo');

    $connections = ConnectionManager::getConnections();
    $this->assertEquals(2, count($connections));
  }

  /**
   * Test batch init connections.
   */
  public function testBatchInitConnections() {
    $sender1 = new Sender();
    $sender2 = new Sender();
    $receiver1 = $this->getMockBuilder(Receiver::class)
      ->setMethods(['slotOne'])
      ->getMock();

    $receiver1->expects($this->once())
      ->method('slotOne')
      ->with('Signal data 1');

    $receiver2 = $this->getMockBuilder(Receiver::class)
      ->setMethods(['slotTwo'])
      ->getMock();

    $receiver2->expects($this->once())
      ->method('slotTwo')
      ->with('Signal data 2');

    ConnectionManager::initConnections([
      [
        'sender' => $sender1,
        'signal' => 'testSignal',
        'receiver' => $receiver1,
        'slot' => 'slotOne',
        'type' => ConnectionManager::CONNECTION_PERMANENT,
      ],
      [
        'sender' => $sender2,
        'signal' => 'testSignal',
        'receiver' => $receiver2,
        'slot' => 'slotTwo',
        'type' => ConnectionManager::CONNECTION_ONE_TIME,
      ],
    ]);

    $sender1->emit('testSignal', 'Signal data 1');
    $sender2->emit('testSignal', 'Signal data 2');

    $connections = ConnectionManager::getConnections();
    $this->assertEquals(1, count($connections));
  }

  /**
   * Test batch init connections with unknown type.
   *
   * @expectedException RuntimeException
   * @expectedExceptionMessage Unknown connection type.
   */
  public function testBatchInitConnectionsWithUnknownType() {
    ConnectionManager::initConnections([
      [
        'sender' => new Sender(),
        'signal' => 'testSignal',
        'receiver' => new Receiver(),
        'slot' => 'slotOne',
        'type' => 2,
      ],
    ]);
  }

  /**
   * Test batch init malformed connections.
   *
   * @param array $connections
   *
   * @dataProvider malformedConnectionsProvider
   * @expectedException RuntimeException
   * @expectedExceptionMessage Malformed connection.
   */
  public function testBatchInitMalformedConnections(array $connections) {
    ConnectionManager::initConnections($connections);
  }

  /**
   * Data provider for testBatchInitConnections() test method.
   */
  public function malformedConnectionsProvider() {
    return [
      [
        [
          [
          ],
        ],
      ],
      [
        [
          [
            'sender' => new Sender(),
          ],
        ],
      ],
      [
        [
          [
            'signal' => 'testSignal',
          ],
        ],
      ],
      [
        [
          [
            'receiver' => new Receiver(),
          ],
        ],
      ],
      [
        [
          [
            'slot' => 'slotOne',
          ],
        ],
      ],
      [
        [
          [
            'sender' => new Sender(),
            'signal' => 'testSignal',
          ],
        ],
      ],
      [
        [
          [
            'sender' => new Sender(),
            'receiver' => new Receiver(),
          ],
        ],
      ],
      [
        [
          [
            'sender' => new Sender(),
            'slot' => 'slotOne',
          ],
        ],
      ],
      [
        [
          [
            'signal' => 'testSignal',
            'receiver' => new Receiver(),
          ],
        ],
      ],
      [
        [
          [
            'signal' => 'testSignal',
            'slot' => 'slotOne',
          ],
        ],
      ],
      [
        [
          [
            'receiver' => new Receiver(),
            'slot' => 'slotOne',
          ],
        ],
      ],
      [
        [
          [
            'sender' => new Sender(),
            'signal' => 'testSignal',
            'receiver' => new Receiver(),
          ],
        ],
      ],
      [
        [
          [
            'signal' => 'testSignal',
            'receiver' => new Receiver(),
            'slot' => 'slotOne',
          ],
        ],
      ],
      [
        [
          [
            'sender' => new Sender(),
            'receiver' => new Receiver(),
            'slot' => 'slotOne',
          ],
        ],
      ],
      [
        [
          [
            'sender' => new Sender(),
            'signal' => 'testSignal',
            'slot' => 'slotOne',
          ],
        ],
      ]
    ];
  }

}
