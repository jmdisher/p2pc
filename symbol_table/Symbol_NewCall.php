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
require_once('IFunctionCall.php');


// Author:  Jeff Disher (Open Autonomy Inc.)
// The description of a "new" call issued within the program.
class OA_Symbol_NewCall implements OA_IFunctionCall
{
	private $receiverClassNameToken;
	
	public function __construct($receiverClassNameToken)
	{
		$this->receiverClassNameToken = $receiverClassNameToken;
	}
	
	public function getDescription()
	{
		$className = $this->receiverClassNameToken->getText();
		return "new $className()\n";
	}
	
	public function getTargetsFromRegistry($registry)
	{
		$className = $this->receiverClassNameToken->getText();
		$identifier = OA_FunctionRegistry::createNameForConstructor($className);
		$target = $registry->resolveStaticReceiverForName($identifier);
		return (null !== $target) ? array($target) : array();
	}
}


?>
