<?php
/*
 Copyright (c) 2014 Open Autonomy Inc.
 
 Permission is hereby granted, free of charge, to any person obtaining a copy
 of this software and associated documentation files (the "Software"), to deal
 in the Software without restriction, including without limitation the rights
 to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 copies of the Software, and to permit persons to whom the Software is
 furnished to do so, subject to the following conditions:
 
 The above copyright notice and this permission notice shall be included in all
 copies or substantial portions of the Software.
 
 THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 SOFTWARE.
*/


// Author:  Jeff Disher (Open Autonomy Inc.)
// The description of a class declaration symbol in a compiled program.
class OA_Symbol_ClassDeclaration
{
	private $nameToken;
	private $superclassName;
	private $staticFunctions;
	private $instanceFunctions;
	private $exportedFunctions;
	
	public function __construct($nameToken)
	{
		$this->nameToken = $nameToken;
		$this->superclassName = null;
		$this->staticFunctions = array();
		$this->instanceFunctions = array();
		$this->exportedFunctions = array();
	}
	
	public function addStaticFunction($functionObject)
	{
		$this->staticFunctions[] = $functionObject;
	}
	
	public function addInstanceFunction($functionObject)
	{
		$this->instanceFunctions[] = $functionObject;
	}
	
	public function getDescription($indentation)
	{
		$className = $this->nameToken->getText();
		$fileName = $this->nameToken->getFile();
		$lineNumber = $this->nameToken->getLine();
		$string = $indentation . "CLASS: $className\n\t$fileName:$lineNumber\n";
		
		foreach ($this->staticFunctions as $functionObject)
		{
			$functionString = $functionObject->getDescription("\tSTATIC ", "$className::");
			$string .= $functionString;
		}
		foreach ($this->instanceFunctions as $functionObject)
		{
			$functionString = $functionObject->getDescription("\tINSTANCE ", '->');
			$string .= $functionString;
		}
		return $string;
	}
	
	public function registerAllFunctions($registry)
	{
		assert($registry instanceof OA_FunctionRegistry);
		$thisClassName = $this->nameToken->getText();
		
		// Register our superclass relationship.
		if (null !== $this->superclassName)
		{
			$registry->setClassRelationship($this->superclassName, $thisClassName);
		}
		
		// Register the functions.
		$functionPrefix = $thisClassName . '::';
		foreach ($this->staticFunctions as $functionObject)
		{
			$functionName = $functionPrefix . $functionObject->getName();
			$registry->registerNormalFunction($functionName, $functionObject);
		}
		foreach ($this->instanceFunctions as $functionObject)
		{
			$functionIdentifier = $functionObject->getName();
			if ('__construct' === $functionIdentifier)
			{
				$functionName = $functionPrefix . $functionIdentifier;
				$registry->registerNormalFunction($functionName, $functionObject);
			}
			else
			{
				$registry->registerVirtualFunction($functionIdentifier, $functionObject);
			}
		}
	}
	
	// Walks all the functions in this class, removing them from the parse tree if they were not marked as alive.
	public function cleanDeadFunctions()
	{
		foreach ($this->staticFunctions as $functionObject)
		{
			$functionObject->cleanIfDead();
		}
		foreach ($this->instanceFunctions as $functionObject)
		{
			$functionObject->cleanIfDead();
		}
	}
	
	// An exported static function acts as a "root" for dead code elimination.
	public function addExportedFunction($functionObject)
	{
		$this->exportedFunctions[] = $functionObject;
	}
	
	public function getAllExportedCalls()
	{
		return $this->exportedFunctions;
	}
	
	// Note that this is the string name of the superclass.
	public function setSuperclassName($superclassName)
	{
		$this->superclassName = $superclassName;
	}
}


?>
