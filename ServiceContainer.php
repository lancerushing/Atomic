<?php
namespace Atomic;

class ServiceContainer {
	/**
	 * @var Config
	 */
	protected $config;

	public function __construct() {
		$this->config = new Config();
	}

	/**
	 * @return Mailer
	 */
	public function mailer() {
		$cacheKey = __METHOD__;
		if (!isset($this->objectCache[$cacheKey])) {
			$this->objectCache[$cacheKey] = new Mailer();
		}
		return $this->objectCache[$cacheKey];
	}

	/**
	 * @return \PDO
	 */
	public function pdo() {
		$cacheKey = __METHOD__;
		if (!isset($this->objectCache[$cacheKey])) {

			$this->objectCache[$cacheKey] = new PDO($this->config->databaseDsn, $this->config->databaseUserName, $this->config->databasePassword, array(PDO::ATTR_PERSISTENT => true)

			);
			$this->objectCache[$cacheKey]->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}
		return $this->objectCache[$cacheKey];
	}


}
