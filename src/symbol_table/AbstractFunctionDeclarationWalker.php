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
require_once('ITreeWalker.php');
require_once('Symbol_FunctionDeclaration.php');


// Author:  Jeff Disher (Open Autonomy Inc.)
// A tree walker which handles abstract function declarations.  It exists to ensure correctness in dead code elimination
//  by allowing abstract function declarations to act as "roots" of functions with those names since the abstract
//  definition may be defined externally to the code being compiled (the abstract class could be provided as a required
//  interface to use with an external component).
class OA_AbstractFunctionDeclarationWalker implements OA_ITreeWalker
{
	const kIdentifier = 'IDENTIFIER';
	
	
	private $functionNameToken;
	
	
	// Creates an empty representation of the receiver.
	public function __construct()
	{
		$this->functionNameToken = null;
	}
	
	// OA_ITreeWalker.
	public function preVisitTree($tree)
	{
		return (null === $this->functionNameToken);
	}
	
	// OA_ITreeWalker.
	public function postVisitTree($tree)
	{
		// Do nothing on exit.
	}
	
	// OA_ITreeWalker.
	public function visitLeaf($leaf)
	{
		// We want to find the identifier token since that is the name under the top-level decl.
		if ((null === $this->functionNameToken) && (OA_FunctionDeclarationWalker::kIdentifier === $leaf->getName()))
		{
			$this->functionNameToken = $leaf;
		}
	}
	
	// Fakes up a virtual call to the function of the given name, thus causing those functions to be live roots.
	public function getFunctionDeclarationObject()
	{
		return new OA_Symbol_VirtualCall($this->functionNameToken);
	}
}

?>
