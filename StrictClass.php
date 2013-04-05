<?php

namespace Atomic;

use RuntimeException;

/**
 * Uses the __set magic function to prevent properties from being set unless they are defined.
 */
class StrictClass {

	/**
	 * Called when the property is not defined.
	 * @param string $name  Name of property.
	 * @param mixed $value Value of property.
	 * @return void
	 * @throws RuntimeException When called.
	 */
	public function __set($name, $value) {
		$traceMsg = $this->traceMsg();
		throw new RuntimeException("Trying to set unknown property named '$name' for class '" . get_class($this) . "'.  $traceMsg");
	}

	/**
	 * Called when the property is not defined.
	 * @param string $name Name of property.
	 * @return void
	 * @throws RuntimeException When called.
	 */
	public function __get($name) {
		$traceMsg = $this->traceMsg();
		throw new RuntimeException("Trying to get unknown property named '$name' for class '" . get_class($this) . "'.  $traceMsg");
	}

	/**
	 * Provides the calling location to the exception message.
	 * @return string
	 */
	private function traceMsg() {
		$backtrace = debug_backtrace();
		$trace = $backtrace[1];
		return sprintf("Called from %s (%s)", isset($trace['file']) ? $trace['file'] : "internal", isset($trace['line']) ? $trace['line'] : "internal");
	}

}