<?php

namespace Atomic;

require_once __DIR__ . '/StrictClass.php';

use RecursiveDirectoryIterator;
use RuntimeException;
use RecursiveIteratorIterator;
use SplFileInfo;
use UnexpectedValueException;

class AutoLoader extends StrictClass {

	private $classMap = array();
	private $paths = array();
	private $scannedFiles = array();

	/**
	 * @var array
	 */
	private $extensions = array("php");

	private $indexFile = '';

	public function __construct($paths) {
		if (is_string($paths)) {
			$paths = array($paths);
		}
		foreach($paths as $path) {
			$fullPath = realpath($path);
			if (!in_array($fullPath, $this->paths)) {
				$this->paths[] = $fullPath;
			}
		}
		$this->indexFile = sys_get_temp_dir() . PATH_SEPARATOR . md5(serialize($this->paths));
	}

	/**
	 * Registers this class on the SPL autoloader stack.
	 * @return boolean Returns true if registration was successful, false otherwise.
	 */
	public function register() {
		$this->setupIndex();

		// as spl_autoload_register() disables __autoload() and this might be unwanted, we put it onto autoload stack
		if (function_exists('__autoload')) {
			spl_autoload_register('__autoload');
		}

		return spl_autoload_register(array($this, 'classAutoLoad'));
	}

	/**
	 * @param string $className Name of the class.
	 * @param bool $rescan
	 * @return bool Returns true if class is loaded, false otherwise.
	 */
	public function classAutoLoad($className, $rescan=true) {
		if (class_exists($className, false) || interface_exists($className, false)) {
			return true;
		}

		$path = isset($this->classMap[$className]) ? $this->classMap[$className] : null;

		if ($path !== null) {
			if (file_exists($path)) {
				require_once $path;
				if (class_exists($className, false) || interface_exists($className, false)) {
				// check if it now exists (index could be out of date)
					return true;
				}
			}
		}

		$result = false;
		if ($rescan) {
			$this->reScan();
			$result = $this->classAutoLoad($className, false);
			if ($result) {
				$this->saveIndex();
			}
		}
		return $result;
	}
	private function setupIndex() {
		if (is_file($this->indexFile)) {
			$this->classMap = unserialize(file_get_contents($this->indexFile));
		} else {
			$this->reScan();
			$this->saveIndex();
		}
	}

	private function saveIndex() {
		file_put_contents($this->indexFile, serialize($this->classMap));
	}

	private function reScan() {
		$this->classMap = array();
		foreach ($this->paths as $path) {
			$this->scanDirectory($path);
		}
	}

	private function scanDirectory($dirName) {
		$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dirName),
			RecursiveIteratorIterator::SELF_FIRST);

		/** @var $fileInfo \SplFileInfo */
		foreach ($files as $fileInfo) {
			if ($this->checkFile($fileInfo)) {
				$this->scanFileContent($fileInfo);
			}
		}
	}


	private function checkFile(SplFileInfo $fileInfo) {
		return $fileInfo->isFile() &&
			$fileInfo->isReadable() &&
			$this->checkExtension($fileInfo) &&
		    $this->notScanned($fileInfo);
	}

	private function notScanned(SplFileInfo $fileInfo) {
		return !in_array($fileInfo->getRealPath(), $this->scannedFiles);
	}

	private function checkExtension(SplFileInfo $fileInfo) {
		return in_array($fileInfo->getExtension(), $this->extensions);
	}


	private function scanFileContent(SplFileInfo $fileInfo) {
		$fileName = $fileInfo->getRealPath();

		$content = file_get_contents($fileName);

		if (false === $content) {
			throw new RuntimeException(__METHOD__ . '(): cannot read file: ' . $fileName . '!');
		}

		$namespace_prefix = '';

		$tokens = token_get_all($content);
		for ($i = 0, $size = count($tokens); $i < $size; $i++) {
			switch ($tokens[$i][0]) {
				case T_NAMESPACE:
					$i += 2; //skip the whitespace token
					while ($tokens[$i] !== ";") {
						$namespace_prefix .= $tokens[$i][1];
						$i++;
					}
					$namespace_prefix .= "\\";
					break;

				case T_CLASS:
				case T_INTERFACE:
					$i += 2; //skip the whitespace token
					$className = $namespace_prefix . $tokens[$i][1];
					if (false == isset($this->classMap[$className])) {
						$this->classMap[$className] = $this->dos2unix($fileName);
					} else {
						throw new UnexpectedValueException(__METHOD__ . '(): ' . $className . ' is already defined in file: '
							. $this->classMap[$className] . ' Please rename its duplicate found in ' . $fileName);
					}

					break;
			}
		}

		$this->scannedFiles[] = $fileName;
	}

	private function dos2unix($fileName) {
		return str_replace('\\', '/', $fileName);
	}
}