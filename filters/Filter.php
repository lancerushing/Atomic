<?php
/**
 * @author Lance Rushing <lance@lancerushing.com>
 * @since 2013-04-04
 */

namespace Atomic;

/**
 * Base Class for Filters
 */
abstract class Filter extends StrictClass {

	/**
	 *
	 * @var ServiceContainer
	 */
	protected $serviceContainer;


	public function __construct(ServiceContainer $serviceContainer) {
		$this->serviceContainer = $serviceContainer;
	}

	/**
	 * @param HttpRequest $request
	 * @param HttpResponse $response
	 * @return
	 */
	abstract public function flow(HttpRequest $request, HttpResponse $response);

}