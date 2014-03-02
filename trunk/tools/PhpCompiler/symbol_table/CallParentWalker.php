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
require_once('Symbol_StaticCall.php');


// Author:  Jeff Disher (Open Autonomy Inc.)
// An implementation of the tree walker which interprets calls to parent and synthesizes them as either NEW or INSTANCE
//  call symbols.
class OA_CallParentWalker implements OA_ITreeWalker
{
	const kIdentifier = 'IDENTIFIER';
	const kParentCall = 'P_PARENT_CALL';
	
	
	private $callingContext;
	private $receiverFunctionNameToken;
	
	
	// Creates an empty representation of the receiver.
	public function __construct($callingContext)
	{
		$this->callingContext = $callingContext;
		$this->receiverFunctionNameToken = null;
	}
	
	// OA_ITreeWalker.
	public function preVisitTree($tree)
	{
		return ($tree->getName() === OA_CallParentWalker::kParentCall);
	}
	
	// OA_ITreeWalker.
	public function postVisitTree($tree)
	{
		// Do nothing on exit.
	}
	
	// OA_ITreeWalker.
	public function visitLeaf($leaf)
	{
		// We are interested in the first identifier we see, but nothing else.
		//  (looking for "PARENT COLON_COLON IDENTIFIER")
		if (OA_CallParentWalker::kIdentifier === $leaf->getName())
		{
			assert(null === $this->receiverFunctionNameToken);
			$this->receiverFunctionNameToken = $leaf;
		}
	}
	
	public function getFunctionCallObject()
	{
		$object = null;
		if ('__construct' === $this->receiverFunctionNameToken->getText())
		{
			$object = new OA_Symbol_NewCall($this->callingContext->getClassNameToken());
		}
		else
		{
			$object = new OA_Symbol_VirtualCall($this->receiverFunctionNameToken);
		}
		return $object;
	}
}

?>
