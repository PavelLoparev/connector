<?php

/**
 * @file
 * Contains definition of signal interface.
 */

namespace Fluffy\Connector\Signal;

/**
 * Interface SignalInterface
 * @package Fluffy\Connector\Signal
 */
interface SignalInterface
{

  /**
   * Emit signal with parameters.
   *
   * @param string $signal
   *   Signal name.
   * @param mixed $data
   *   Passed into slot arguments.
   */
  public function emit($signal, $data);

}
