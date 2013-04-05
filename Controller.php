<?php

namespace Atomic;

use RuntimeException;

class Controller {

	/**
	 * @var ServiceContainer
	 */
	protected $services;
	/**
	 * @var HttpRequest
	 */
	protected $request;

	public function __construct(ServiceContainer $services, HttpRequest $request) {
		$this->services = $services;
		$this->request = $request;
	}


	public function process() {
		switch ($this->request->type()) {
			case "GET":
				$this->get();
				break;

			case "POST":
				$this->post();
				break;

			default:
				throw new RuntimeException("Method not allowed");
		}
	}

	public function post() {
		throw new RuntimeException("Method not allowed");
	}

	public function get() {
		throw new RuntimeException("Method not allowed");
	}

}