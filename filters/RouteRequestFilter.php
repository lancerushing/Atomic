<?php

/**
 * @author Lance Rushing <lance@lancerushing.com>
 * @since 2013-04-04
 */
namespace Atomic;

class RouteRequestFilter extends Filter {

	public $routes = array();

	/**
	 * @param HttpRequest $request
	 * @param HttpResponse $response
	 * @return bool
	 */
	public function flow(HttpRequest $request, HttpResponse $response) {
		$path = $request->path();

		if (isset($this->routes[$path])) {
			$request->controllerName = $this->routes[$path];
			return true;
		}

		$response->sendNotFound();
		$response->sendBody(sprintf("Route '%s' not found.", htmlentities($path)));
		return false;
	}

	public function addRoutes(array $array) {
		$this->routes = array_merge($this->routes, $array);
	}

	public function addRoute($key, $value) {
		$this->routes[$key] = $value;
	}
}