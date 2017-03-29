<?php

namespace Fluffy\Tests\Service;

use Fluffy\Connector\Signal\SignalInterface;
use Fluffy\Connector\Signal\SignalTrait;

class TestService implements SignalInterface {

  use SignalTrait;

  public function test() {
    $this->emit('testSignal', 'Test data');
  }

  public function testSlot($data) {
    return $data;
  }

}
