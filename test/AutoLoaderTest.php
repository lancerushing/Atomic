<?php

namespace Atomic;


class AutoLoaderTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var AutoLoader
	 */
	public $autoLoader;

	protected function setUp() {
		$this->autoLoader = new AutoLoader(array());
	}

	public function testSetupIndex() {
		$property = new \ReflectionProperty($this->autoLoader, 'indexFile');
		$property->setAccessible(true);

		$method = new \ReflectionMethod($this->autoLoader, 'setupIndex');
		$method->setAccessible(true);

		$fileName = $property->getValue($this->autoLoader);
		@unlink($fileName);

		$method->invoke($this->autoLoader);
		$this->assertFileExists($fileName);

		$method->invoke($this->autoLoader);

	}

	public function testScanDirectory() {
		$method = new \ReflectionMethod($this->autoLoader, 'scanDirectory');
		$method->setAccessible(true);

		$actual = $method->invoke($this->autoLoader, dirname(__DIR__));
	}


	public function testCheckFile() {
		$method = new \ReflectionMethod($this->autoLoader, 'checkFile');
		$method->setAccessible(true);

		$input = new \SplFileInfo(__FILE__);
		$expected = true;
		$actual = $method->invoke($this->autoLoader, $input);

		$this->assertEquals($expected, $actual);
	}


	public function testCheckIfIncluded() {
		$method = new \ReflectionMethod($this->autoLoader, 'checkExtension');
		$method->setAccessible(true);

		$input = new \SplFileInfo(__FILE__);
		$expected = true;
		$actual = $method->invoke($this->autoLoader, $input);

		$this->assertEquals($expected, $actual);
	}

	public function testCheckIfIncludedIsFalse() {
		$method = new \ReflectionMethod($this->autoLoader, 'checkExtension');
		$method->setAccessible(true);

		$mockFileInfo = $this->getMockBuilder('SplFileInfo')->disableOriginalConstructor()->getMock();
		$mockFileInfo->expects($this->once())->method('getExtension')->will($this->returnValue('notPhp'));

		$input = $mockFileInfo;
		$expected = false;
		$actual = $method->invoke($this->autoLoader, $input);

		$this->assertEquals($expected, $actual);
	}


	public function testDos2unix() {
		$method = new \ReflectionMethod($this->autoLoader, 'dos2unix');
		$method->setAccessible(true);

		$input = '\\this\\is\\a\\path';
		$expected = '/this/is/a/path';
		$actual = $method->invoke($this->autoLoader, $input);

		$this->assertEquals($expected, $actual);
	}
}
