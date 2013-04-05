<?php
/**
 * @author Lance Rushing <lance@lancerushing.com>
 * @since 2013-04-04
 */

namespace Atomic;


class HttpResponse extends StrictClass {

	/**
	 * Wraps php function header().
	 * @param string $string
	 * @param string $replace
	 * @param integer $httpResponseCode
	 * @return void
	 */
	public function sendHeader($string, $replace = NULL, $httpResponseCode = NULL) {
		if (NULL !== $httpResponseCode) {
			header($string, $replace, $httpResponseCode);
		} else {
			header($string, $replace);
		}
	}

	public function sendBody($string) {
		echo $string;
	}


	/**
	 * Redirect request.
	 * @param string $url A URL to redirect to.
	 * @return void
	 */
	public function redirect($url) {
		$this->sendHeader('Location: ' . $url, TRUE, 302);
		exit;
	}

	/**
	 * Redirect request.
	 * @param string $url A URL to redirect to.
	 * @return void
	 */
	public function redirectPermanently($url) {
		$this->sendHeader('Location: ' . $url, TRUE, 301);
		exit;
	}

	/**
	 * @return void
	 */
	public function sendCreated() {
		$this->sendHeader('HTTP/1.1 201 Created', TRUE, 201);
	}

	/**
	 * @return void
	 */
	public function sendNotFound() {
		$this->sendHeader('HTTP/1.1 404 Not Found', TRUE, 404);
	}

	/**
	 * @return void
	 */
	public function sendNoContent() {
		$this->sendHeader('HTTP/1.1 204 No Content', TRUE, 204);
	}

	/**
	 * @return void
	 */
	public function sendConflict() {
		$this->sendHeader('HTTP/1.1 409 Conflict', TRUE, 409);
	}

	/**
	 * @return void
	 */
	public function sendBadRequest() {
		$this->sendHeader('HTTP/1.1 400 Bad Request', TRUE, 400);
	}

	/**
	 * @return void
	 */
	public function sendBadRequestHeader() {
		$this->sendHeader('HTTP/1.1 400 Bad Request', TRUE, 400);
	}

}
