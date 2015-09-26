#!/usr/bin/php
<?php

abstract class Superclass
{
	public function __construct()
	{
		echo "Superclass constructor";
	}
	
	public abstract function abstractAlive();
}

interface TheInterface
{
	public function interfaceAlive();
}

// We will add another interface here, just to prove that we can parse multiple interface references.
interface UnusedInterface
{
	public function unusedFunction();
}

class DeadTest extends Superclass implements TheInterface, UnusedInterface
{
	public static function alive()
	{
		$object = new DeadTest();
		$object->instanceAlive();
	}
	
	public static function dead()
	{
		$object = new DeadTest();
		$object->instanceDead();
	}
	
	public function instanceAlive()
	{
	}
	
	public function instanceDead()
	{
	}
	
	public function abstractAlive()
	{
		echo "ABSTRACT ALIVE";
	}
	
	public function interfaceAlive()
	{
		echo "INTERFACE ALIVE";
	}
	
	public function unusedFunction()
	{
		echo 'unused';
	}
}

function globalAlive()
{
}

function directAlive()
{
}

function dead()
{
	DeadTest::dead();
}

directAlive();

//EXPORT globalAlive;
//EXPORT DeadTest::alive;

// Adding some other simple tests for problems found later in dead code elimination support (these were grammar issues).
$modTest = 5 % 3;
new DeadTest();

?>
