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
	private $staticFunctions;
	private $instanceFunctions;
	
	public function __construct($nameToken)
	{
		$this->nameToken = $nameToken;
		$this->staticFunctions = array();
		$this->instanceFunctions = array();
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
}


?>
