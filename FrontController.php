<?php

namespace Atomic;


class FrontController extends Controller {

	/**
	 * @var Pipe
	 */
	private $pipe;

	protected function init() {
		$this->pipe = new Pipe();
	}

	public function addFilter(Filter $filter) {
		$this->pipe->add($filter);
	}

	public function process() {

		$result = $this->pipe->flow($this->request, $this->response);

		if($result) {
			$controllerName = $this->request->controllerName;
			$controller = new $controllerName($this->serviceContainer, $this->request, $this->response);
			$controller->process();
		}

		return true;
	}
}
