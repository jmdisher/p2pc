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
require_once('Symbol_NewCall.php');


// Author:  Jeff Disher (Open Autonomy Inc.)
// An implementation of the tree walker which extracts information specific to a "new" call.
class OA_CallNewWalker implements OA_ITreeWalker
{
	const kIdentifier = 'IDENTIFIER';
	const kCallNew = 'P_NEW';
	
	
	private $receiverClassNameToken;
	
	
	// Creates an empty representation of the receiver.
	public function __construct()
	{
		$this->receiverClassNameToken = null;
	}
	
	// OA_ITreeWalker.
	public function preVisitTree($tree)
	{
		// This walker is only called when traversing a virtual call, which is close to the leaf of the parse tree, so
		//  we never want to walk children, except for the very first call (which is the instance call node).
		return ($tree->getName() === OA_CallNewWalker::kCallNew);
	}
	
	// OA_ITreeWalker.
	public function postVisitTree($tree)
	{
		// Do nothing on exit.
	}
	
	// OA_ITreeWalker.
	public function visitLeaf($leaf)
	{
		// We are interested in the first identifier call we see, but nothing else.
		//  (looking for "NEW IDENTIFIER P_RARG_BRACKETS")
		if (OA_CallNewWalker::kIdentifier === $leaf->getName())
		{
			assert(null === $this->receiverClassNameToken);
			$this->receiverClassNameToken = $leaf;
		}
	}
	
	public function getFunctionCallObject()
	{
		return new OA_Symbol_NewCall($this->receiverClassNameToken);
	}
}

?>
