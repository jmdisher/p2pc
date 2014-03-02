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
// This is the central data structure for the dead code removal optimization:  It contains the mappings of all the
//  static calls and virtual calls to their corresponding target function implementations (or potential implementations,
//  in the case of virtual calls).
class OA_FunctionRegistry
{
	// Creates a name for the constructor for the given $className which can be used for registration and lookup in the
	//  functions below.
	public static function createNameForConstructor($className)
	{
		return "$className::__construct";
	}
	
	// Creates a name for the static function in $className with $functionName which can be used for registration and
	//  lookup in the functions below.
	public static function createNameForStaticFunction($className, $functionName)
	{
		return "$className::$functionName";
	}
	
	
	private $normalFunctionMap;
	private $virtualFunctionArrayMap;
	
	
	public function __construct()
	{
		$this->normalFunctionMap = array();
		$this->virtualFunctionArrayMap = array();
	}
	
	// Called to register a statically-callable function.  The given name can only map to one function object.
	public function registerNormalFunction($functionName, $functionObject)
	{
		assert(!isset($this->normalFunctionMap[$functionName]));
		$this->normalFunctionMap[$functionName] = $functionObject;
	}
	
	// Called to register a virtually-callable function.  Multiple function objects can be registered with the same name
	//  but any call to any of these functions will mark them all as alive since the receiver type is unknown.
	public function registerVirtualFunction($functionIdentifier, $functionObject)
	{
		$array = isset($this->virtualFunctionArrayMap[$functionIdentifier]) ? $this->virtualFunctionArrayMap[$functionIdentifier] : array();
		$array[] = $functionObject;
		$this->virtualFunctionArrayMap[$functionIdentifier] = $array;
	}
	
	// Returns the receiver object or null.
	// NOTE:  Failure to resolve is common in the case of calling a function in the standard library.
	public function resolveStaticReceiverForName($functionName)
	{
		return isset($this->normalFunctionMap[$functionName]) ? $this->normalFunctionMap[$functionName] : null;
	}
	
	// Returns an array of receiver objects, could be empty.
	public function resolveVirtualReceiversForName($functionName)
	{
		return isset($this->virtualFunctionArrayMap[$functionName]) ? $this->virtualFunctionArrayMap[$functionName] : array();
	}
}

?>
