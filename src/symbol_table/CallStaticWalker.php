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
// An implementation of the tree walker which exists to extract information regarding the class and function names used
//  to issue a static call.
class OA_CallStaticWalker implements OA_ITreeWalker
{
	const kIdentifier = 'IDENTIFIER';
	const kStaticCall = 'P_STATIC_CALL';
	
	
	private $receiverClassNameToken;
	private $receiverFunctionNameToken;
	
	
	// Creates an empty representation of the receiver.
	public function __construct()
	{
		$this->receiverClassNameToken = null;
		$this->receiverFunctionNameToken = null;
	}
	
	// OA_ITreeWalker.
	public function preVisitTree($tree)
	{
		// This walker is only called when traversing a static call, which is close to the leaf of the parse tree, so
		//  we never want to walk children, except for the very first call (which is the static call node).
		return ($tree->getName() === OA_CallStaticWalker::kStaticCall);
	}
	
	// OA_ITreeWalker.
	public function postVisitTree($tree)
	{
		// Do nothing on exit.
	}
	
	// OA_ITreeWalker.
	public function visitLeaf($leaf)
	{
		// We are interested in the 2 first identifier calls we see, but nothing else.
		//  (looking for "IDENTIFIER COLON_COLON IDENTIFIER")
		if (OA_CallStaticWalker::kIdentifier === $leaf->getName())
		{
			if (null === $this->receiverClassNameToken)
			{
				$this->receiverClassNameToken = $leaf;
			}
			else if (null === $this->receiverFunctionNameToken)
			{
				$this->receiverFunctionNameToken = $leaf;
			}
			else
			{
				assert(false);
			}
		}
	}
	
	public function getFunctionCallObject()
	{
		return new OA_Symbol_StaticCall($this->receiverClassNameToken, $this->receiverFunctionNameToken);
	}
}

?>
