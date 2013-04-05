<?php
/**
 * @author Lance Rushing <lance@lancerushing.com>
 * @since 2013-04-04
 */

namespace Atomic;


/**
 * The Pipe class to attach filters to
 */

class Pipe extends StrictClass {

	/**
	 *
	 * @var Array $filters to hold the pipe's filters
	 */
	private $filters = array();

	/**
	 * @param Filter $filter
	 * @return Pipe
	 */
	public function add(Filter $filter) {
		$this->filters[] = $filter;
		return $this;
	}

	/**
	 *
	 * @param HttpRequest $request
	 * @param HttpResponse $response
	 * @return boolean
	 */
	public function flow(HttpRequest $request, HttpResponse $response) {
		$result = false;
		foreach ($this->filters as $filter) {
			$result = $filter->flow($request, $response);
			if ($result === false) {
				break;
			}
		}

		return $result;
	}

}