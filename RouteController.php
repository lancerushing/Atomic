<?php

namespace Atomic;


class RouteController extends Controller {
	public $routes = array();

	public function addRoutes(array $routes) {
		$this->routes = $routes;
	}

	public function process() {
		$path = $this->request->path();

		if (isset($this->routes[$path])) {
			$controllerName = $this->routes[$path];
			require_once __DIR__ . "/../controllers/$controllerName.php";
			$controller = new $controllerName($this->services, $this->request);
			$controller->process();
			return true;
		}

		return false;
	}
}
