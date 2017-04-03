<?php

/**
 * @file
 * Contains definition of ConnectorTest class.
 */

namespace Fluffy\Connector\Tests;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Fluffy\Connector\ConnectionManager;
use Fluffy\Connector\Tests\Sender\Sender;
use Fluffy\Connector\Tests\Receiver\Receiver;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * Class ConnectorTest.
 */
class ConnectorTest extends TestCase {

  public function setUp() {
    ConnectionManager::resetAllConnections();
  }

  /**
   * Test correct connections structure.
   *
   * @param $connections
   * @param $expectations
   * @dataProvider ifConnectionsAreCorrectProvider
   */
  public function testIfConnectionsAreCorrect($connections, $expectations) {
    ConnectionManager::initConnections($connections);
    $actualConnections = ConnectionManager::getConnections();

    $sendersCount = count($actualConnections);
    $signalsCount = 0;
    $receiversCount = 0;

    foreach ($actualConnections as $signals) {
      $signalsCount += count($signals);

      foreach ($signals as $signal) {
        $receiversCount += count($signal);
      }
    }

    $this->assertEquals($expectations['senders'], $sendersCount, 'Amount of senders is ok');
    $this->assertEquals($expectations['signals'], $signalsCount, 'Amount of signals is ok');
    $this->assertEquals($expectations['receivers'], $receiversCount, 'Amount of receivers is ok');
  }

  /**
   * Data provider for testIfConnectionsAreCorrect() test.
   * @return array
   */
  public function ifConnectionsAreCorrectProvider() {
    $sender1 = new Sender();
    $sender2 = new Sender();
    $sender3 = new Sender();
    $signal1 = 'testSignal1';
    $signal2 = 'testSignal2';
    $signal3 = 'testSignal3';
    $receiver1 = new Receiver();
    $receiver2 = new Receiver();
    $receiver3 = new Receiver();
    $slot1 = 'testSlot1';
    $slot2 = 'testSlot2';
    $slot3 = 'testSlot3';

    return [
      // All keys are different.
      [
        [
          [
            'sender' => $sender1,
            'signal' => $signal1,
            'receiver' => $receiver1,
            'slot' => $slot1,
          ],
        ],
        [
          'senders' => 1,
          'signals' => 1,
          'receivers' => 1,
        ],
      ],
      [
        [
          [
            'sender' => $sender1,
            'signal' => $signal1,
            'receiver' => $receiver1,
            'slot' => $slot1,
          ],
          [
            'sender' => $sender2,
            'signal' => $signal2,
            'receiver' => $receiver2,
            'slot' => $slot2,
          ],
        ],
        [
          'senders' => 2,
          'signals' => 2,
          'receivers' => 2,
        ],
      ],
      [
        [
          [
            'sender' => $sender1,
            'signal' => $signal1,
            'receiver' => $receiver1,
            'slot' => $slot1,
          ],
          [
            'sender' => $sender2,
            'signal' => $signal2,
            'receiver' => $receiver2,
            'slot' => $slot2,
          ],
          [
            'sender' => $sender3,
            'signal' => $signal3,
            'receiver' => $receiver3,
            'slot' => $slot3,
          ],
        ],
        [
          'senders' => 3,
          'signals' => 3,
          'receivers' => 3,
        ],
      ],
      // Same sender. Different signal, receiver and slot.
      [
        [
          [
            'sender' => $sender1,
            'signal' => $signal1,
            'receiver' => $receiver1,
            'slot' => $slot1,
          ],
        ],
        [
          'senders' => 1,
          'signals' => 1,
          'receivers' => 1,
        ],
      ],
      [
        [
          [
            'sender' => $sender1,
            'signal' => $signal1,
            'receiver' => $receiver1,
            'slot' => $slot1,
          ],
          [
            'sender' => $sender1,
            'signal' => $signal2,
            'receiver' => $receiver2,
            'slot' => $slot2,
          ],
        ],
        [
          'senders' => 1,
          'signals' => 2,
          'receivers' => 2,
        ],
      ],
      [
        [
          [
            'sender' => $sender1,
            'signal' => 'testSignal1',
            'receiver' => new Receiver(),
            'slot' => 'slot1',
          ],
          [
            'sender' => $sender1,
            'signal' => $signal2,
            'receiver' => $receiver2,
            'slot' => $slot2,
          ],
          [
            'sender' => $sender1,
            'signal' => $signal3,
            'receiver' => $receiver3,
            'slot' => $slot3,
          ],
        ],
        [
          'senders' => 1,
          'signals' => 3,
          'receivers' => 3,
        ],
      ],
      // Same signal. Different sender, receiver and slot.
      [
        [
          [
            'sender' => $sender1,
            'signal' => $signal1,
            'receiver' => $receiver1,
            'slot' => $slot1,
          ],
        ],
        [
          'senders' => 1,
          'signals' => 1,
          'receivers' => 1,
        ],
      ],
      [
        [
          [
            'sender' => $sender1,
            'signal' => $signal1,
            'receiver' => $receiver1,
            'slot' => $slot1,
          ],
          [
            'sender' => $sender2,
            'signal' => $signal1,
            'receiver' => $receiver2,
            'slot' => $slot2,
          ],
        ],
        [
          'senders' => 2,
          'signals' => 2,
          'receivers' => 2,
        ],
      ],
      [
        [
          [
            'sender' => $sender1,
            'signal' => $signal1,
            'receiver' => $receiver1,
            'slot' => $slot1,
          ],
          [
            'sender' => $sender2,
            'signal' => $signal1,
            'receiver' => $receiver2,
            'slot' => $slot2,
          ],
          [
            'sender' => $sender3,
            'signal' => $signal1,
            'receiver' => $receiver3,
            'slot' => $slot3,
          ],
        ],
        [
          'senders' => 3,
          'signals' => 3,
          'receivers' => 3,
        ],
      ],
      // Same receiver. Different sender, signal and slot.
      [
        [
          [
            'sender' => $sender1,
            'signal' => $signal1,
            'receiver' => $receiver1,
            'slot' => $slot1,
          ],
        ],
        [
          'senders' => 1,
          'signals' => 1,
          'receivers' => 1,
        ],
      ],
      [
        [
          [
            'sender' => $sender1,
            'signal' => $signal1,
            'receiver' => $receiver1,
            'slot' => $slot1,
          ],
          [
            'sender' => $sender2,
            'signal' => $signal2,
            'receiver' => $receiver1,
            'slot' => $slot2,
          ],
        ],
        [
          'senders' => 2,
          'signals' => 2,
          'receivers' => 2,
        ],
      ],
      [
        [
          [
            'sender' => $sender1,
            'signal' => $signal1,
            'receiver' => $receiver1,
            'slot' => $slot1,
          ],
          [
            'sender' => $sender2,
            'signal' => $signal2,
            'receiver' => $receiver1,
            'slot' => $slot2,
          ],
          [
            'sender' => $sender3,
            'signal' => $signal3,
            'receiver' => $receiver1,
            'slot' => $slot3,
          ],
        ],
        [
          'senders' => 3,
          'signals' => 3,
          'receivers' => 3,
        ],
      ],
      // Same slot. Different sender, signal and receiver.
      [
        [
          [
            'sender' => $sender1,
            'signal' => $signal1,
            'receiver' => $receiver1,
            'slot' => $slot1,
          ],
        ],
        [
          'senders' => 1,
          'signals' => 1,
          'receivers' => 1,
        ],
      ],
      [
        [
          [
            'sender' => $sender1,
            'signal' => $signal1,
            'receiver' => $receiver1,
            'slot' => $slot1,
          ],
          [
            'sender' => $sender2,
            'signal' => $signal2,
            'receiver' => $receiver2,
            'slot' => $slot1,
          ],
        ],
        [
          'senders' => 2,
          'signals' => 2,
          'receivers' => 2,
        ],
      ],
      [
        [
          [
            'sender' => $sender1,
            'signal' => $signal1,
            'receiver' => $receiver1,
            'slot' => $slot1,
          ],
          [
            'sender' => $sender2,
            'signal' => $signal2,
            'receiver' => $receiver2,
            'slot' => $slot1,
          ],
          [
            'sender' => $sender3,
            'signal' => $signal3,
            'receiver' => $receiver3,
            'slot' => $slot1,
          ],
        ],
        [
          'senders' => 3,
          'signals' => 3,
          'receivers' => 3,
        ],
      ],
      // Same sender and signal. Different slot and receiver.
      [
        [
          [
            'sender' => $sender1,
            'signal' => $signal1,
            'receiver' => $receiver1,
            'slot' => $slot1,
          ],
        ],
        [
          'senders' => 1,
          'signals' => 1,
          'receivers' => 1,
        ],
      ],
      [
        [
          [
            'sender' => $sender1,
            'signal' => $signal1,
            'receiver' => $receiver1,
            'slot' => $slot1,
          ],
          [
            'sender' => $sender1,
            'signal' => $signal1,
            'receiver' => $receiver2,
            'slot' => $slot2,
          ],
        ],
        [
          'senders' => 1,
          'signals' => 1,
          'receivers' => 2,
        ],
      ],
      [
        [
          [
            'sender' => $sender1,
            'signal' => $signal1,
            'receiver' => $receiver1,
            'slot' => $slot1,
          ],
          [
            'sender' => $sender1,
            'signal' => $signal1,
            'receiver' => $receiver2,
            'slot' => $slot2,
          ],
          [
            'sender' => $sender1,
            'signal' => $signal1,
            'receiver' => $receiver3,
            'slot' => $slot3,
          ],
        ],
        [
          'senders' => 1,
          'signals' => 1,
          'receivers' => 3,
        ],
      ],
      // Same sender and receiver. Different slot and signal.
      [
        [
          [
            'sender' => $sender1,
            'signal' => $signal1,
            'receiver' => $receiver1,
            'slot' => $slot1,
          ],
        ],
        [
          'senders' => 1,
          'signals' => 1,
          'receivers' => 1,
        ],
      ],
      [
        [
          [
            'sender' => $sender1,
            'signal' => $signal1,
            'receiver' => $receiver1,
            'slot' => $slot1,
          ],
          [
            'sender' => $sender1,
            'signal' => $signal2,
            'receiver' => $receiver1,
            'slot' => $slot2,
          ],
        ],
        [
          'senders' => 1,
          'signals' => 2,
          'receivers' => 2,
        ],
      ],
      [
        [
          [
            'sender' => $sender1,
            'signal' => $signal1,
            'receiver' => $receiver1,
            'slot' => $slot1,
          ],
          [
            'sender' => $sender1,
            'signal' => $signal2,
            'receiver' => $receiver1,
            'slot' => $slot2,
          ],
          [
            'sender' => $sender1,
            'signal' => $signal3,
            'receiver' => $receiver1,
            'slot' => $slot3,
          ],
        ],
        [
          'senders' => 1,
          'signals' => 3,
          'receivers' => 3,
        ],
      ],
      // Same sender and slot. Different receiver and signal.
      [
        [
          [
            'sender' => $sender1,
            'signal' => $signal1,
            'receiver' => $receiver1,
            'slot' => $slot1,
          ],
        ],
        [
          'senders' => 1,
          'signals' => 1,
          'receivers' => 1,
        ],
      ],
      [
        [
          [
            'sender' => $sender1,
            'signal' => $signal1,
            'receiver' => $receiver1,
            'slot' => $slot1,
          ],
          [
            'sender' => $sender1,
            'signal' => $signal2,
            'receiver' => $receiver2,
            'slot' => $slot1,
          ],
        ],
        [
          'senders' => 1,
          'signals' => 2,
          'receivers' => 2,
        ],
      ],
      [
        [
          [
            'sender' => $sender1,
            'signal' => $signal1,
            'receiver' => $receiver1,
            'slot' => $slot1,
          ],
          [
            'sender' => $sender1,
            'signal' => $signal2,
            'receiver' => $receiver2,
            'slot' => $slot1,
          ],
          [
            'sender' => $sender1,
            'signal' => $signal3,
            'receiver' => $receiver3,
            'slot' => $slot1,
          ],
        ],
        [
          'senders' => 1,
          'signals' => 3,
          'receivers' => 3,
        ],
      ],
      // Same signal and receiver. Different sender and slot.
      [
        [
          [
            'sender' => $sender1,
            'signal' => $signal1,
            'receiver' => $receiver1,
            'slot' => $slot1,
          ],
        ],
        [
          'senders' => 1,
          'signals' => 1,
          'receivers' => 1,
        ],
      ],
      [
        [
          [
            'sender' => $sender1,
            'signal' => $signal1,
            'receiver' => $receiver1,
            'slot' => $slot1,
          ],
          [
            'sender' => $sender2,
            'signal' => $signal1,
            'receiver' => $receiver1,
            'slot' => $slot2,
          ],
        ],
        [
          'senders' => 2,
          'signals' => 2,
          'receivers' => 2,
        ],
      ],
      [
        [
          [
            'sender' => $sender1,
            'signal' => $signal1,
            'receiver' => $receiver1,
            'slot' => $slot1,
          ],
          [
            'sender' => $sender2,
            'signal' => $signal1,
            'receiver' => $receiver1,
            'slot' => $slot2,
          ],
          [
            'sender' => $sender3,
            'signal' => $signal1,
            'receiver' => $receiver1,
            'slot' => $slot3,
          ],
        ],
        [
          'senders' => 3,
          'signals' => 3,
          'receivers' => 3,
        ],
      ],
      // Same signal and slot. Different sender and receiver.
      [
        [
          [
            'sender' => $sender1,
            'signal' => $signal1,
            'receiver' => $receiver1,
            'slot' => $slot1,
          ],
        ],
        [
          'senders' => 1,
          'signals' => 1,
          'receivers' => 1,
        ],
      ],
      [
        [
          [
            'sender' => $sender1,
            'signal' => $signal1,
            'receiver' => $receiver1,
            'slot' => $slot1,
          ],
          [
            'sender' => $sender2,
            'signal' => $signal1,
            'receiver' => $receiver2,
            'slot' => $slot1,
          ],
        ],
        [
          'senders' => 2,
          'signals' => 2,
          'receivers' => 2,
        ],
      ],
      [
        [
          [
            'sender' => $sender1,
            'signal' => $signal1,
            'receiver' => $receiver1,
            'slot' => $slot1,
          ],
          [
            'sender' => $sender2,
            'signal' => $signal1,
            'receiver' => $receiver2,
            'slot' => $slot1,
          ],
          [
            'sender' => $sender3,
            'signal' => $signal1,
            'receiver' => $receiver3,
            'slot' => $slot1,
          ],
        ],
        [
          'senders' => 3,
          'signals' => 3,
          'receivers' => 3,
        ],
      ],
      // Same receiver and slot. Different sender and signal.
      [
        [
          [
            'sender' => $sender1,
            'signal' => $signal1,
            'receiver' => $receiver1,
            'slot' => $slot1,
          ],
        ],
        [
          'senders' => 1,
          'signals' => 1,
          'receivers' => 1,
        ],
      ],
      [
        [
          [
            'sender' => $sender1,
            'signal' => $signal1,
            'receiver' => $receiver1,
            'slot' => $slot1,
          ],
          [
            'sender' => $sender2,
            'signal' => $signal2,
            'receiver' => $receiver1,
            'slot' => $slot1,
          ],
        ],
        [
          'senders' => 2,
          'signals' => 2,
          'receivers' => 2,
        ],
      ],
      [
        [
          [
            'sender' => $sender1,
            'signal' => $signal1,
            'receiver' => $receiver1,
            'slot' => $slot1,
          ],
          [
            'sender' => $sender2,
            'signal' => $signal2,
            'receiver' => $receiver1,
            'slot' => $slot1,
          ],
          [
            'sender' => $sender3,
            'signal' => $signal3,
            'receiver' => $receiver1,
            'slot' => $slot1,
          ],
        ],
        [
          'senders' => 3,
          'signals' => 3,
          'receivers' => 3,
        ],
      ],
      // Same sender, signal and receiver. Different slot.
      [
        [
          [
            'sender' => $sender1,
            'signal' => $signal1,
            'receiver' => $receiver1,
            'slot' => $slot1,
          ],
        ],
        [
          'senders' => 1,
          'signals' => 1,
          'receivers' => 1,
        ],
      ],
      [
        [
          [
            'sender' => $sender1,
            'signal' => $signal1,
            'receiver' => $receiver1,
            'slot' => $slot1,
          ],
          [
            'sender' => $sender1,
            'signal' => $signal1,
            'receiver' => $receiver1,
            'slot' => $slot2,
          ],
        ],
        [
          'senders' => 1,
          'signals' => 1,
          'receivers' => 2,
        ],
      ],
      [
        [
          [
            'sender' => $sender1,
            'signal' => $signal1,
            'receiver' => $receiver1,
            'slot' => $slot1,
          ],
          [
            'sender' => $sender1,
            'signal' => $signal1,
            'receiver' => $receiver1,
            'slot' => $slot2,
          ],
          [
            'sender' => $sender1,
            'signal' => $signal1,
            'receiver' => $receiver1,
            'slot' => $slot3,
          ],
        ],
        [
          'senders' => 1,
          'signals' => 1,
          'receivers' => 3,
        ],
      ],
      // Same sender, receiver and slot. Different signal.
      [
        [
          [
            'sender' => $sender1,
            'signal' => $signal1,
            'receiver' => $receiver1,
            'slot' => $slot1,
          ],
        ],
        [
          'senders' => 1,
          'signals' => 1,
          'receivers' => 1,
        ],
      ],
      [
        [
          [
            'sender' => $sender1,
            'signal' => $signal1,
            'receiver' => $receiver1,
            'slot' => $slot1,
          ],
          [
            'sender' => $sender1,
            'signal' => $signal2,
            'receiver' => $receiver1,
            'slot' => $slot1,
          ],
        ],
        [
          'senders' => 1,
          'signals' => 2,
          'receivers' => 2,
        ],
      ],
      [
        [
          [
            'sender' => $sender1,
            'signal' => $signal1,
            'receiver' => $receiver1,
            'slot' => $slot1,
          ],
          [
            'sender' => $sender1,
            'signal' => $signal2,
            'receiver' => $receiver1,
            'slot' => $slot1,
          ],
          [
            'sender' => $sender1,
            'signal' => $signal3,
            'receiver' => $receiver1,
            'slot' => $slot1,
          ],
        ],
        [
          'senders' => 1,
          'signals' => 3,
          'receivers' => 3,
        ],
      ],
      // Same sender, signal and slot. Different receiver.
      [
        [
          [
            'sender' => $sender1,
            'signal' => $signal1,
            'receiver' => $receiver1,
            'slot' => $slot1,
          ],
        ],
        [
          'senders' => 1,
          'signals' => 1,
          'receivers' => 1,
        ],
      ],
      [
        [
          [
            'sender' => $sender1,
            'signal' => $signal1,
            'receiver' => $receiver1,
            'slot' => $slot1,
          ],
          [
            'sender' => $sender1,
            'signal' => $signal1,
            'receiver' => $receiver2,
            'slot' => $slot1,
          ],
        ],
        [
          'senders' => 1,
          'signals' => 1,
          'receivers' => 2,
        ],
      ],
      [
        [
          [
            'sender' => $sender1,
            'signal' => $signal1,
            'receiver' => $receiver1,
            'slot' => $slot1,
          ],
          [
            'sender' => $sender1,
            'signal' => $signal1,
            'receiver' => $receiver2,
            'slot' => $slot1,
          ],
          [
            'sender' => $sender1,
            'signal' => $signal1,
            'receiver' => $receiver3,
            'slot' => $slot1,
          ],
        ],
        [
          'senders' => 1,
          'signals' => 1,
          'receivers' => 3,
        ],
      ],
      // Same signal, receiver and slot. Different sender.
      [
        [
          [
            'sender' => $sender1,
            'signal' => $signal1,
            'receiver' => $receiver1,
            'slot' => $slot1,
          ],
        ],
        [
          'senders' => 1,
          'signals' => 1,
          'receivers' => 1,
        ],
      ],
      [
        [
          [
            'sender' => $sender1,
            'signal' => $signal1,
            'receiver' => $receiver1,
            'slot' => $slot1,
          ],
          [
            'sender' => $sender2,
            'signal' => $signal1,
            'receiver' => $receiver1,
            'slot' => $slot1,
          ],
        ],
        [
          'senders' => 2,
          'signals' => 2,
          'receivers' => 2,
        ],
      ],
      [
        [
          [
            'sender' => $sender1,
            'signal' => $signal1,
            'receiver' => $receiver1,
            'slot' => $slot1,
          ],
          [
            'sender' => $sender2,
            'signal' => $signal1,
            'receiver' => $receiver1,
            'slot' => $slot1,
          ],
          [
            'sender' => $sender3,
            'signal' => $signal1,
            'receiver' => $receiver1,
            'slot' => $slot1,
          ],
        ],
        [
          'senders' => 3,
          'signals' => 3,
          'receivers' => 3,
        ],
      ],
      // All keys are the same.
      [
        [
          [
            'sender' => $sender1,
            'signal' => $signal1,
            'receiver' => $receiver1,
            'slot' => $slot1,
          ],
        ],
        [
          'senders' => 1,
          'signals' => 1,
          'receivers' => 1,
        ],
      ],
      [
        [
          [
            'sender' => $sender1,
            'signal' => $signal1,
            'receiver' => $receiver1,
            'slot' => $slot1,
          ],
          [
            'sender' => $sender1,
            'signal' => $signal1,
            'receiver' => $receiver1,
            'slot' => $slot1,
          ],
        ],
        [
          'senders' => 1,
          'signals' => 1,
          'receivers' => 1,
        ],
      ],
      [
        [
          [
            'sender' => $sender1,
            'signal' => $signal1,
            'receiver' => $receiver1,
            'slot' => $slot1,
          ],
          [
            'sender' => $sender1,
            'signal' => $signal1,
            'receiver' => $receiver1,
            'slot' => $slot1,
          ],
          [
            'sender' => $sender1,
            'signal' => $signal1,
            'receiver' => $receiver1,
            'slot' => $slot1,
          ],
        ],
        [
          'senders' => 1,
          'signals' => 1,
          'receivers' => 1,
        ],
      ],
    ];
  }

  /**
   * Test connection: existing and unexisting slots.
   */
  public function testUnexistingAndExistingSlotCalling() {
    $sender = new Sender();
    $receiver = new Receiver();

    // Connect signal to unexisting slot.
    ConnectionManager::connect($sender, 'testSignal', $receiver, 'unexistingSlot');

    $errors = $sender->emit('testSignal', 'Signal data');
    $this->assertEquals(1, count($errors));
    $this->assertEquals('Trying to call undefined slot "unexistingSlot" in a "Fluffy\Connector\Tests\Receiver\Receiver" class.', $errors[0]);

    ConnectionManager::resetAllConnections();

    // Connect signal to existing slot.
    ConnectionManager::connect($sender, 'testSignal', $receiver, 'slotOne');

    $errors = $sender->emit('testSignal', 'Signal data');
    $this->assertEquals(0, count($errors));
  }

  /**
   * Test connection.
   *
   * One sender, one signal, one receiver, one slot.
   */
  public function testOneSenderOneSignalToOneReceiverOneSlot() {
    $slot = 'slotOne';
    $signal = 'testSignal';
    $data = 'Signal data';
    $sender = new Sender();
    $receiver = $this->getMockBuilder(Receiver::class)
      ->setMethods([$slot])
      ->getMock();

    $receiver->expects($this->once())
      ->method($slot)
      ->with($data);

    ConnectionManager::connect($sender, $signal, $receiver, $slot);

    $sender->emit($signal, $data);
  }

  /**
   * Test connection.
   *
   * Many senders, one signal, one receiver, one slot.
   */
  public function testManySendersOneSignalToOneReceiverOneSlot() {
    $slot = 'slotOne';
    $signal = 'testSignal';
    $data = 'Signal data';
    $sender1 = new Sender();
    $sender2 = new Sender();
    $receiver = $this->getMockBuilder(Receiver::class)
      ->setMethods([$slot])
      ->getMock();

    $receiver->expects($this->exactly(2))
      ->method($slot)
      ->with($data);

    ConnectionManager::connect($sender1, $signal, $receiver, $slot);
    ConnectionManager::connect($sender2, $signal, $receiver, $slot);

    $sender1->emit($signal, $data);
    $sender2->emit($signal, $data);
  }

  /**
   * Test connection.
   *
   * One sender, many signals, one receiver, one slot.
   */
  public function testOneSenderManySignalsToOneReceiverOneSlot() {
    $slot = 'slotOne';
    $signal1 = 'testSignal1';
    $signal2 = 'testSignal2';
    $data = 'Signal data';
    $sender = new Sender();
    $receiver = $this->getMockBuilder(Receiver::class)
      ->setMethods([$slot])
      ->getMock();

    $receiver->expects($this->exactly(2))
      ->method($slot)
      ->with($data);

    ConnectionManager::connect($sender, $signal1, $receiver, $slot);
    ConnectionManager::connect($sender, $signal2, $receiver, $slot);

    $sender->emit($signal1, $data);
    $sender->emit($signal2, $data);
  }

  /**
   * Test connection.
   *
   * One sender, one signal, many receivers, one slot.
   */
  public function testOneSenderOneSignalToManyReceiversOneSlot() {
    $slot = 'slotOne';
    $signal = 'testSignal';
    $data = 'Signal data';
    $sender = new Sender();
    $receiver1 = $this->getMockBuilder(Receiver::class)
      ->setMethods([$slot])
      ->getMock();

    $receiver1->expects($this->exactly(1))
      ->method($slot)
      ->with($data);

    $receiver2 = $this->getMockBuilder(Receiver::class)
      ->setMethods([$slot])
      ->getMock();

    $receiver2->expects($this->exactly(1))
      ->method($slot)
      ->with($data);

    ConnectionManager::connect($sender, $signal, $receiver1, $slot);
    ConnectionManager::connect($sender, $signal, $receiver2, $slot);

    $sender->emit($signal, $data);
  }

  /**
   * Test connection.
   *
   * One sender, one signal, one receiver, many slots.
   */
  public function testOneSenderOneSignalToOneReceiverManySlots() {
    $slot1 = 'slotOne';
    $slot2 = 'slotTwo';
    $signal = 'testSignal';
    $data = 'Signal data';
    $sender = new Sender();
    $receiver = $this->getMockBuilder(Receiver::class)
      ->setMethods([$slot1, $slot2])
      ->getMock();

    $receiver->expects($this->exactly(1))
      ->method($slot1)
      ->with($data);

    $receiver->expects($this->exactly(1))
      ->method($slot2)
      ->with($data);

    ConnectionManager::connect($sender, $signal, $receiver, $slot1);
    ConnectionManager::connect($sender, $signal, $receiver, $slot2);

    $sender->emit($signal, $data);
  }

  /**
   * Test connection.
   *
   * Many senders, many signals, one receiver, one slot.
   */
  public function testManySendersManySignalsToOneReceiverOneSlot() {
    $slot = 'slotOne';
    $signal1 = 'testSignal1';
    $signal2 = 'testSignal2';
    $data = 'Signal data';
    $sender1 = new Sender();
    $sender2 = new Sender();
    $receiver = $this->getMockBuilder(Receiver::class)
      ->setMethods([$slot])
      ->getMock();

    $receiver->expects($this->exactly(2))
      ->method($slot)
      ->with($data);

    ConnectionManager::connect($sender1, $signal1, $receiver, $slot);
    ConnectionManager::connect($sender2, $signal2, $receiver, $slot);

    $sender1->emit($signal1, $data);
    $sender2->emit($signal2, $data);
  }

  /**
   * Test connection.
   *
   * Many senders, one signal, many receivers, one slot.
   */
  public function testManySendersOneSignalToManyReceiversOneSlot() {
    $slot = 'slotOne';
    $signal = 'testSignal';
    $data = 'Signal data';
    $sender1 = new Sender();
    $sender2 = new Sender();
    $receiver1 = $this->getMockBuilder(Receiver::class)
      ->setMethods([$slot])
      ->getMock();

    $receiver1->expects($this->exactly(1))
      ->method($slot)
      ->with($data);

    $receiver2 = $this->getMockBuilder(Receiver::class)
      ->setMethods([$slot])
      ->getMock();

    $receiver2->expects($this->exactly(1))
      ->method($slot)
      ->with($data);

    ConnectionManager::connect($sender1, $signal, $receiver1, $slot);
    ConnectionManager::connect($sender2, $signal, $receiver2, $slot);

    $sender1->emit($signal, $data);
    $sender2->emit($signal, $data);
  }

  /**
   * Test connection.
   *
   * Many senders, one signal, one receiver, many slots.
   */
  public function testManySendersOneSignalToOneReceiverManySlots() {
    $slot1 = 'slotOne';
    $slot2 = 'slotTwo';
    $signal = 'testSignal';
    $data = 'Signal data';
    $sender1 = new Sender();
    $sender2 = new Sender();
    $receiver = $this->getMockBuilder(Receiver::class)
      ->setMethods([$slot1, $slot2])
      ->getMock();

    $receiver->expects($this->exactly(1))
      ->method($slot1)
      ->with($data);

    $receiver->expects($this->exactly(1))
      ->method($slot2)
      ->with($data);

    ConnectionManager::connect($sender1, $signal, $receiver, $slot1);
    ConnectionManager::connect($sender2, $signal, $receiver, $slot2);

    $sender1->emit($signal, $data);
    $sender2->emit($signal, $data);
  }

  /**
   * Test connection.
   *
   * One sender, many signals, many receivers, one slot.
   */
  public function testOneSenderManySignalsToManyReceiversOneSlot() {
    $slot = 'slotOne';
    $signal1 = 'testSignal1';
    $signal2 = 'testSignal2';
    $data = 'Signal data';
    $sender = new Sender();
    $receiver1 = $this->getMockBuilder(Receiver::class)
      ->setMethods([$slot])
      ->getMock();

    $receiver1->expects($this->exactly(1))
      ->method($slot)
      ->with($data);

    $receiver2 = $this->getMockBuilder(Receiver::class)
      ->setMethods([$slot])
      ->getMock();

    $receiver2->expects($this->exactly(1))
      ->method($slot)
      ->with($data);

    ConnectionManager::connect($sender, $signal1, $receiver1, $slot);
    ConnectionManager::connect($sender, $signal2, $receiver2, $slot);

    $sender->emit($signal1, $data);
    $sender->emit($signal2, $data);
  }

  /**
   * Test connection.
   *
   * One sender, many signals, one receiver, many slots.
   */
  public function testOneSenderManySignalsToOneReceiverManySlots() {
    $slot1 = 'slotOne';
    $slot2 = 'slotTwo';
    $signal1 = 'testSignal1';
    $signal2 = 'testSignal2';
    $data = 'Signal data';
    $sender = new Sender();
    $receiver = $this->getMockBuilder(Receiver::class)
      ->setMethods([$slot1, $slot2])
      ->getMock();

    $receiver->expects($this->exactly(1))
      ->method($slot1)
      ->with($data);

    $receiver->expects($this->exactly(1))
      ->method($slot2)
      ->with($data);

    ConnectionManager::connect($sender, $signal1, $receiver, $slot1);
    ConnectionManager::connect($sender, $signal2, $receiver, $slot2);

    $sender->emit($signal1, $data);
    $sender->emit($signal2, $data);
  }

  /**
   * Test connection.
   *
   * One sender, one signal, many receivers, many slots.
   */
  public function testOneSenderOneSignalToManyReceiversManySlots() {
    $slot1 = 'slotOne';
    $slot2 = 'slotTwo';
    $signal = 'testSignal';
    $data = 'Signal data';
    $sender = new Sender();
    $receiver1 = $this->getMockBuilder(Receiver::class)
      ->setMethods([$slot1])
      ->getMock();

    $receiver1->expects($this->exactly(1))
      ->method($slot1)
      ->with($data);

    $receiver2 = $this->getMockBuilder(Receiver::class)
      ->setMethods([$slot2])
      ->getMock();

    $receiver2->expects($this->exactly(1))
      ->method($slot2)
      ->with($data);

    ConnectionManager::connect($sender, $signal, $receiver1, $slot1);
    ConnectionManager::connect($sender, $signal, $receiver2, $slot2);

    $sender->emit($signal, $data);
  }

  /**
   * Test connection.
   *
   * Many senders, many signals, many receivers, one slot.
   */
  public function testManySendersManySignalsToManyReceiversOneSlot() {
    $slot = 'slotOne';
    $signal1 = 'testSignal1';
    $signal2 = 'testSignal2';
    $data = 'Signal data';
    $sender1 = new Sender();
    $sender2 = new Sender();
    $receiver1 = $this->getMockBuilder(Receiver::class)
      ->setMethods([$slot])
      ->getMock();

    $receiver1->expects($this->exactly(1))
      ->method($slot)
      ->with($data);

    $receiver2 = $this->getMockBuilder(Receiver::class)
      ->setMethods([$slot])
      ->getMock();

    $receiver2->expects($this->exactly(1))
      ->method($slot)
      ->with($data);

    ConnectionManager::connect($sender1, $signal1, $receiver1, $slot);
    ConnectionManager::connect($sender2, $signal2, $receiver2, $slot);

    $sender1->emit($signal1, $data);
    $sender2->emit($signal2, $data);
  }

  /**
   * Test connection.
   *
   * Many senders, one signal, many receivers, many slots.
   */
  public function testManySendersOneSignalToManyReceiversManySlots() {
    $slot1 = 'slotOne';
    $slot2 = 'slotTwo';
    $signal = 'testSignal';
    $data = 'Signal data';
    $sender1 = new Sender();
    $sender2 = new Sender();
    $receiver1 = $this->getMockBuilder(Receiver::class)
      ->setMethods([$slot1])
      ->getMock();

    $receiver1->expects($this->exactly(1))
      ->method($slot1)
      ->with($data);

    $receiver2 = $this->getMockBuilder(Receiver::class)
      ->setMethods([$slot2])
      ->getMock();

    $receiver2->expects($this->exactly(1))
      ->method($slot2)
      ->with($data);

    ConnectionManager::connect($sender1, $signal, $receiver1, $slot1);
    ConnectionManager::connect($sender2, $signal, $receiver2, $slot2);

    $sender1->emit($signal, $data);
    $sender2->emit($signal, $data);
  }

  /**
   * Test connection.
   *
   * Many senders, many signals, one receiver, many slots.
   */
  public function testManySendersManySignalsToOneReceiverManySlots() {
    $slot1 = 'slotOne';
    $slot2 = 'slotTwo';
    $signal1 = 'testSignal1';
    $signal2 = 'testSignal2';
    $data = 'Signal data';
    $sender1 = new Sender();
    $sender2 = new Sender();
    $receiver = $this->getMockBuilder(Receiver::class)
      ->setMethods([$slot1, $slot2])
      ->getMock();

    $receiver->expects($this->exactly(1))
      ->method($slot1)
      ->with($data);

    $receiver->expects($this->exactly(1))
      ->method($slot2)
      ->with($data);

    ConnectionManager::connect($sender1, $signal1, $receiver, $slot1);
    ConnectionManager::connect($sender2, $signal2, $receiver, $slot2);

    $sender1->emit($signal1, $data);
    $sender2->emit($signal2, $data);
  }

  /**
   * Test connection.
   *
   * One sender, many signals, many receivers, many slots.
   */
  public function testOneSenderManySignalsToManyReceiversManySlots() {
    $slot1 = 'slotOne';
    $slot2 = 'slotTwo';
    $signal1 = 'testSignal1';
    $signal2 = 'testSignal2';
    $data = 'Signal data';
    $sender = new Sender();
    $receiver1 = $this->getMockBuilder(Receiver::class)
      ->setMethods([$slot1])
      ->getMock();

    $receiver1->expects($this->exactly(1))
      ->method($slot1)
      ->with($data);

    $receiver2 = $this->getMockBuilder(Receiver::class)
      ->setMethods([$slot2])
      ->getMock();

    $receiver2->expects($this->exactly(1))
      ->method($slot2)
      ->with($data);

    ConnectionManager::connect($sender, $signal1, $receiver1, $slot1);
    ConnectionManager::connect($sender, $signal2, $receiver2, $slot2);

    $sender->emit($signal1, $data);
    $sender->emit($signal2, $data);
  }

  /**
   * Test connection.
   *
   * One sender, many signals, many receivers, many slots.
   */
  public function testManySendersManySignalsToManyReceiversManySlots() {
    $slot1 = 'slotOne';
    $slot2 = 'slotTwo';
    $signal1 = 'testSignal1';
    $signal2 = 'testSignal2';
    $data = 'Signal data';
    $sender1 = new Sender();
    $sender2 = new Sender();
    $receiver1 = $this->getMockBuilder(Receiver::class)
      ->setMethods([$slot1])
      ->getMock();

    $receiver1->expects($this->exactly(1))
      ->method($slot1)
      ->with($data);

    $receiver2 = $this->getMockBuilder(Receiver::class)
      ->setMethods([$slot2])
      ->getMock();

    $receiver2->expects($this->exactly(1))
      ->method($slot2)
      ->with($data);

    ConnectionManager::connect($sender1, $signal1, $receiver1, $slot1);
    ConnectionManager::connect($sender2, $signal2, $receiver2, $slot2);

    $sender1->emit($signal1, $data);
    $sender2->emit($signal2, $data);
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
   * Test connection type: one time.
   *
   * Connection with type "CONNECTION_ONE_TIME" will be disconnected after first
   * signal emission.
   */
  public function testOneTimeConnection() {
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

  /**
   * Test services connections.
   */
  public function testServicesConnections() {
    $container = new ContainerBuilder();
    $serviceLoader = new YamlFileLoader($container, new FileLocator(__DIR__));
    $serviceLoader->load('services.yml');

    $serviceConnections = ConnectionManager::parseServicesConnections(file_get_contents(__DIR__ . '/services.connections.yml'), $container);
    ConnectionManager::initConnections($serviceConnections);

    $container->get('service.sender')->emit('testSignal', 'Signal data');
    $this->expectOutputString('Slot one: Signal data' . PHP_EOL . 'Slot two: Signal data' . PHP_EOL);

    // Second connection is "one-time" so after this emission string
    // will contain only three 'Signal data' sub-strings. See
    // services.connections.yml.
    $container->get('service.sender')->emit('testSignal', 'Signal data');
    $this->expectOutputString('Slot one: Signal data' . PHP_EOL . 'Slot two: Signal data' . PHP_EOL . 'Slot one: Signal data' . PHP_EOL);
  }

  /**
   * Test services disconnect.
   */
  public function testServicesDisconnect() {
    $container = new ContainerBuilder();
    $serviceLoader = new YamlFileLoader($container, new FileLocator(__DIR__));
    $serviceLoader->load('services.yml');

    $serviceConnections = ConnectionManager::parseServicesConnections(file_get_contents(__DIR__ . '/services.connections.yml'), $container);
    ConnectionManager::initConnections($serviceConnections);
    ConnectionManager::disconnect($container->get('service.sender'), 'testSignal', $container->get('service.receiver'), 'slotTwo');

    $container->get('service.sender')->emit('testSignal', 'Signal data');
    $this->expectOutputString('Slot one: Signal data' . PHP_EOL);
  }

  /**
   * Test malformed service connections parsing.
   */
  public function testMalformedServicesConnectionsParsing() {
    $container = new ContainerBuilder();
    $serviceLoader = new YamlFileLoader($container, new FileLocator(__DIR__));
    $serviceLoader->load('services.yml');

    $serviceConnections = ConnectionManager::parseServicesConnections('test: test' . PHP_EOL . 'test', $container);
    ConnectionManager::initConnections($serviceConnections);

    $this->assertEquals([], $serviceConnections);
  }

}
