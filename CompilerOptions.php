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
	// An array of include paths to use when resolving a given require_once directive.
	public $preprocessorIncludePathsArray;
	// An array of file names which, when encountered as a require_once, should be ignored (that is, consume the
	//  require_once but don't process it).  These are generally files which include information which is either unused
	//  or incorrect once compiled (typically include path manipulation).
	public $preprocessorIgnoredFileNameArray;
	// True if only the preprocessor should be run.  This effectively inlines all require_once calls into the resultant
	//  output file.  Note that this provides a substantial performance improvement (especially obvious with APC enabled
	//  since it avoids consulting the include path for each require_once on each call).
	public $preprocessOnly;
	// True if only the preprocessor and lexer should be engaged to inline the require_once calls, strip comments and
	//  whitespace.  This won't change the code in any substantial way and takes about 10* as long as preprocessOnly.
	//  It is probably only useful for creating minimally-sized packages for distribution.
	public $stripOnly;
	// Only useful for testing the lexer as this does NOT generate a PHP program.  Instead, the names of the tokens
	//  lexed are output where the program would normally go.
	public $testLexer;
	// The path to the grammar XML file to use if the parser should be run.
	public $parserGrammarFilePath;
	// The path to the the file where a symbol table report should be generated, if one is requested.
	public $symbolTableReportPath;
	
	
	public function __construct()
	{
		// Default values.
		$this->preprocessorIncludePathsArray = array();
		$this->preprocessorIgnoredFileNameArray = array();
		$this->preprocessOnly = false;
		$this->stripOnly = true;
		$this->testLexer = false;
		$this->parserGrammarFilePath = null;
		$this->symbolTableReportPath = null;
	}
}

?>
