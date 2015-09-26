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
// The description of a function declaration symbol in a compiled program.  This can either be global or static.
class OA_Symbol_FunctionDeclaration
{
	private $isAlive;
	private $functionTreeTop;
	private $nameToken;
	private $functionCallObjects;
	
	public function __construct($functionTreeTop, $nameToken, $functionCallObjects)
	{
		assert(null !== $functionTreeTop);
		assert(null !== $nameToken);
		assert(null !== $functionCallObjects);
		
		$this->isAlive = false;
		$this->functionTreeTop = $functionTreeTop;
		$this->nameToken = $nameToken;
		$this->functionCallObjects = $functionCallObjects;
	}
	
	public function getDescription($indentation, $namePrefix)
	{
		$functionName = $this->nameToken->getText();
		$fileName = $this->nameToken->getFile();
		$lineNumber = $this->nameToken->getLine();
		$liveness = ($this->isAlive ? 'alive' : 'DEAD');
		$string = $indentation . "FUNCTION: $namePrefix$functionName ($liveness)\n\t(declared $fileName:$lineNumber)\n";
		foreach ($this->functionCallObjects as $functionCallObject)
		{
			$string .= "\t\t" . $functionCallObject->getDescription();
		}
		return $string;
	}
	
	public function getName()
	{
		return $this->nameToken->getText();
	}
	
	// Sets this function as alive (reachable) and returns the list of functions that it calls.  Note that the list of
	//  functions is only returned the first time this is called.  Any later calls will see an empty array.
	public function setAliveAndGetCalls()
	{
		$calls = array();
		if (!$this->isAlive)
		{
			$this->isAlive = true;
			$calls = $this->functionCallObjects;
		}
		return $calls;
	}
	
	// If the receiver has not been marked alive, this call will request that it remove its underlying parse tree node
	//  from its parent.
	public function cleanIfDead()
	{
		if (!$this->isAlive)
		{
			// This function declaration is dead so prune the underlying call from the tree.
			$this->functionTreeTop->removeFromTree();
		}
	}
}


?>
