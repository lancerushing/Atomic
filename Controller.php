<?php

namespace Atomic;

use RuntimeException;

class Controller extends StrictClass {

	/**
	 * @var ServiceContainer
	 */
	protected $services;

	/**
	 * @var HttpRequest
	 */
	protected $request;

	/**
	 * @var HttpResponse
	 */
	protected $response;

	public function __construct(ServiceContainer $services, HttpRequest $request, HttpResponse $response) {
		$this->services = $services;
		$this->request = $request;
		$this->response = $response;
		$this->init();
	}

	protected function init() {}

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