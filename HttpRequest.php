<?php

namespace Atomic;

class HttpRequest {
	protected $params = array();
	protected $server = array();

	public function __construct() {
		$this->setup();
	}

	protected function setup() {
		$this->server = $_SERVER;

		switch ($this->type()) {
			case 'GET':
			case 'HEAD':
				$this->params = $_GET;
				break;
			case 'POST':
				$this->params = $_POST;
				break;
			case 'PUT':
			case 'DELETE':
				parse_str(file_get_contents('php://input'), $this->params);
				break;
		}
	}

	/**
	 * @return string
	 */
	public function type() {
		return $this->server['REQUEST_METHOD'];
	}

	/**
	 * @return string
	 */
	public function path() {
		return parse_url($this->server['REQUEST_URI'], PHP_URL_PATH);
	}

	/**
	 * @return array
	 */
	public function getParams() {
		return $this->params;
	}


}