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
require_once('Symbol_GlobalCall.php');


// Author:  Jeff Disher (Open Autonomy Inc.)
// An implementation of the tree walker which extracts information regarding a call to a top-level global function.
class OA_CallGlobalWalker implements OA_ITreeWalker
{
	const kIdentifier = 'IDENTIFIER';
	const kSilentIdentifier = 'SILENT_IDENTIFIER';
	
	
	private $functionNameToken;
	
	
	// Creates an empty representation of the receiver.
	public function __construct()
	{
		$this->functionNameToken = null;
	}
	
	// OA_ITreeWalker.
	public function preVisitTree($tree)
	{
		// Since we need to dig through the P_IDENTIFIER, just continue visiting until we have found our function name
		//  token.
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
		// We are interested in the first identifier call we see, but nothing else.
		//  (looking for "P_IDENTIFIER P_RARG_BRACKETS" but the underlying IDENTIFIER or SILENT_IDENTIFIER)
		$name = $leaf->getName();
		if ((OA_CallGlobalWalker::kIdentifier === $name) || (OA_CallGlobalWalker::kSilentIdentifier === $name))
		{
			assert(null === $this->functionNameToken);
			$this->functionNameToken = $leaf;
		}
	}
	
	public function getFunctionCallObject()
	{
		assert(null !== $this->functionNameToken);
		return new OA_Symbol_GlobalCall($this->functionNameToken);
	}
}

?>
