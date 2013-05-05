<?php
namespace Atomic;

use \PDO;
class ServiceContainer {
	/**
	 * @var Config
	 */
	protected $config;

	protected $containerIndex = array();

	public function __construct() {
		$this->config = new Config();
	}

	/**
	 * @return \PDO
	 */
	public function pdo() {
		$cacheKey = __METHOD__;
		if (!isset($this->containerIndex[$cacheKey])) {
			$pdo = new PDO($this->config->databaseDsn, $this->config->databaseUserName, $this->config->databasePassword, array(PDO::ATTR_PERSISTENT => true));
			$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

			$this->containerIndex[$cacheKey] = $pdo;
		}
		return $this->containerIndex[$cacheKey];
	}


}
