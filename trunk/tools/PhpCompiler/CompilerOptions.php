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


// The compiler behaviour can be controlled via options but the description of options needs to be extensible without
//  changing existing interfaces so this class as provided as a basic struct to configure the behaviour.
// All fields here are public as it is meant to be a struct, not so much an object.  The constructor merely sets these
//  fields to their default values.
// By default, the options struct needs to be initialized with all stable optimizations enabled and the user code will
//  disable stable options or enable experimental/speculative ones on a case-by-case basis.
class OA_CompilerOptions
{
	public $preprocessorIncludePathsArray;
	public $preprocessorIgnoredFileNameArray;
	
	public function __construct()
	{
		// Default values.
		$this->preprocessorIncludePathsArray = array();
		$this->preprocessorIgnoredFileNameArray = array();
	}
}

?>