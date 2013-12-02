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
	private $skipPaths = array();
	private $scannedFiles = array();

	/**
	 * @var array
	 */
	private $extensions = array("php");

	private $indexFile = '';

	public function __construct($paths, $skipPaths=null) {
		if (is_string($paths)) {
			$paths = array($paths);
		}
		foreach($paths as $path) {
			$fullPath = realpath($path);
			if (!in_array($fullPath, $this->paths)) {
				$this->paths[] = $fullPath;
			}
		}
        if ($skipPaths) {
            foreach($skipPaths as $path) {
                $fullPath = realpath($path);
                if (!in_array($fullPath, $this->skipPaths)) {
                    $this->skipPaths[] = $fullPath;
                }
            }
        }

		$this->indexFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'AutoLoader.' . md5(serialize($this->paths)) . md5(serialize($this->skipPaths)) . '.idx';
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
            }
			if (class_exists($className, false) || interface_exists($className, false)) {
			// check if it now exists (index could be out of date)
				return true;
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
		$this->scannedFiles = array();
		foreach ($this->paths as $path) {
            if ($this->notInSkipPaths($path)) {
			    $this->scanDirectory($path);
            }
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
			$this->notInSkipPaths($fileInfo->getRealPath()) &&
		    $this->notScanned($fileInfo);
	}

	private function notInSkipPaths($path) {
        foreach ($this->skipPaths as $skipPath) {
            if (preg_match("#^$skipPath#", $path)) {
                return false;
            }
        }
        return true;
	}
	private function notScanned(SplFileInfo $fileInfo) {

		return !in_array($fileInfo->getRealPath(), $this->scannedFiles);
	}

	private function checkExtension(SplFileInfo $fileInfo) {
		//return in_array($fileInfo->getExtension(), $this->extensions); // getExtension() only avail in >= 5.3.6
		return in_array(pathinfo($fileInfo->getFilename(), PATHINFO_EXTENSION), $this->extensions);
	}


    /**
     * Scans the file and adds its classes to the map
     * @param \SplFileInfo $fileInfo
     * @throws \UnexpectedValueException
     */
    private function scanFileContent(SplFileInfo $fileInfo)
    {

        $fileName = $fileInfo->getRealPath();
        $classes = $this->findClasses($fileName);

        foreach($classes as $className) {

            if (false == isset($this->classMap[$className])) {
                $this->classMap[$className] = $this->dos2unix($fileName);
            } else {
                if ($this->classMap[$className] !== $fileName) { // some libraries use conditional runtime obj54.215.11.216arect definitions
                    throw new UnexpectedValueException(__METHOD__ . '(): ' . $className . ' is already defined in file: '
                        . $this->classMap[$className] . ' Please rename its duplicate found in ' . $fileName);
                }
            }

        }

        $this->scannedFiles[] = $fileName;
    }


    /**
     * Extract the classes in the given file
     *
     * @param  string            $path The file to check
     * @throws \RuntimeException
     * @return array             The found classes
     * @author composer project
     */
    private static function findClasses($path)
    {
        $traits = version_compare(PHP_VERSION, '5.4', '<') ? '' : '|trait';

        try {
            $contents = php_strip_whitespace($path);
        } catch (\Exception $e) {
            throw new \RuntimeException('Could not scan for classes inside '.$path.": \n".$e->getMessage(), 0, $e);
        }

        // return early if there is no chance of matching anything in this file
        if (!preg_match('{\b(?:class|interface'.$traits.')\s}i', $contents)) {
            return array();
        }

        // strip heredocs/nowdocs
        $contents = preg_replace('{<<<\'?(\w+)\'?(?:\r\n|\n|\r)(?:.*?)(?:\r\n|\n|\r)\\1(?=\r\n|\n|\r|;)}s', 'null', $contents);
        // strip strings
        $contents = preg_replace('{"[^"\\\\]*(\\\\.[^"\\\\]*)*"|\'[^\'\\\\]*(\\\\.[^\'\\\\]*)*\'}s', 'null', $contents);
        // strip leading non-php code if needed
        if (substr($contents, 0, 2) !== '<?') {
            $contents = preg_replace('{^.+?<\?}s', '<?', $contents);
        }
        // strip non-php blocks in the file
        $contents = preg_replace('{\?>.+<\?}s', '?><?', $contents);
        // strip trailing non-php code if needed
        $pos = strrpos($contents, '?>');
        if (false !== $pos && false === strpos(substr($contents, $pos), '<?')) {
            $contents = substr($contents, 0, $pos);
        }

        preg_match_all('{
            (?:
                 \b(?<![\$:>])(?P<type>class|interface'.$traits.') \s+ (?P<name>[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)
               | \b(?<![\$:>])(?P<ns>namespace) (?P<nsname>\s+[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*(?:\s*\\\\\s*[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)*)? \s*[\{;]
            )
        }ix', $contents, $matches);

        $classes = array();
        $namespace = '';

        for ($i = 0, $len = count($matches['type']); $i < $len; $i++) {
            if (!empty($matches['ns'][$i])) {
                $namespace = str_replace(array(' ', "\t", "\r", "\n"), '', $matches['nsname'][$i]) . '\\';
            } else {
                $classes[] = ltrim($namespace . $matches['name'][$i], '\\');
            }
        }

        return $classes;
    }

	private function dos2unix($fileName) {
		return str_replace('\\', '/', $fileName);
	}
}