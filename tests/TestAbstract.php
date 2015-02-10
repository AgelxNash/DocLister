<?php namespace DocLister\Tests;

abstract class TestAbstract extends \PHPUnit_Framework_TestCase {
	public function getMethod($class, $method)
	{
		$reflection = new \ReflectionClass($class);
		$method = $reflection->getMethod($method);
		$method->setAccessible(true);

		return $method;
	}

	public function getProperty($class, $property)
	{
		$reflection = new \ReflectionClass($class);
		$property = $reflection->getProperty($property);
		$property->setAccessible(true);

		return $property->getValue($class);
	}

	public function setProperty($class, $property, $value)
	{
		$reflection = new \ReflectionClass($class);
		$property = $reflection->getProperty($property);
		$property->setAccessible(true);

		return $property->setValue($class, $value);
	}
}