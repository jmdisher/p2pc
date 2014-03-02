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
// An implementation of the tree walker which exists primarily to remove extraneous state decisions from the class
//  declaration walker so it doesn't need to maintain distinguishing state between instance and static functions.
// This walker just knows how to walk the space between the instance function declaration and the function declaration.
class OA_InstanceFunctionDeclarationWalker implements OA_ITreeWalker
{
	const kFunctionDecl = 'P_FUNCTION_DECL';
	
	
	private $functionTreeTop;
	private $functionObject;
	
	
	// Creates an empty representation of the receiver.
	public function __construct()
	{
		$this->functionTreeTop = null;
		$this->functionObject = null;
	}
	
	// OA_ITreeWalker.
	public function preVisitTree($tree)
	{
		$shouldVisitChildren = (null === $this->functionTreeTop);
		if (null === $this->functionTreeTop)
		{
			$this->functionTreeTop = $tree;
		}
		else
		{
			if (OA_StaticFunctionDeclarationWalker::kFunctionDecl === $tree->getName())
			{
				$childWalker = new OA_FunctionDeclarationWalker($this->functionTreeTop);
				$tree->visit($childWalker);
				$functionObject = $childWalker->getFunctionDeclarationObject();
				assert(null !== $functionObject);
				$this->functionObject = $functionObject;
			}
		}
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
		// Do nothing on leaves.
	}
	
	public function getFunctionDeclarationObject()
	{
		return $this->functionObject;
	}
}

?>
