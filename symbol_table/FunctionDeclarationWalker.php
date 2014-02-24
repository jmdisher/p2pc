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


// Author:  Jeff Disher (Open Autonomy Inc.)
// An implementation of the tree walker which exists to extract the name of a function from the parse tree representing
//  its declaration.
class OA_FunctionDeclarationWalker implements OA_ITreeWalker
{
	const kIdentifier = 'IDENTIFIER';
	
	
	private $functionNameToken;
	private $shouldWalkNextChild;
	
	
	// Creates an empty representation of the receiver.
	public function __construct()
	{
		$this->functionNameToken = null;
		$this->shouldWalkNextChild = true;
	}
	
	// OA_ITreeWalker.
	public function preVisitTree($tree)
	{
		// We only want to visit the top-level declaration node so switch this to no after this call.
		$shouldVisitChildren = $this->shouldWalkNextChild;
		$this->shouldWalkNextChild = false;
		return $shouldVisitChildren;
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
		if (OA_FunctionDeclarationWalker::kIdentifier === $leaf->getName())
		{
			assert(null === $this->functionNameToken);
			$this->functionNameToken = $leaf;
		}
	}
	
	// Returns the lexer token instance representing the function name.
	public function getFunctionNameToken()
	{
		return $this->functionNameToken;
	}
}

?>
