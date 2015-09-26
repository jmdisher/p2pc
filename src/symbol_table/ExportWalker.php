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
require_once('Symbol_GlobalCall.php');


// Author:  Jeff Disher (Open Autonomy Inc.)
// An implementation of the tree walker which exists to extract information regarding the data in export comments.
class OA_ExportWalker implements OA_ITreeWalker
{
	const kIdentifier = 'IDENTIFIER';
	
	
	private $firstIdentifierToken;
	private $secondIdentifierToken;
	
	
	// Creates an empty representation of the receiver.
	public function __construct()
	{
		$this->firstIdentifierToken = null;
		$this->secondIdentifierToken = null;
	}
	
	// OA_ITreeWalker.
	public function preVisitTree($tree)
	{
		return true;
	}
	
	// OA_ITreeWalker.
	public function postVisitTree($tree)
	{
		// Do nothing on exit.
	}
	
	// OA_ITreeWalker.
	public function visitLeaf($leaf)
	{
		if (OA_CallStaticWalker::kIdentifier === $leaf->getName())
		{
			if (null === $this->firstIdentifierToken)
			{
				$this->firstIdentifierToken = $leaf;
			}
			else if (null === $this->secondIdentifierToken)
			{
				$this->secondIdentifierToken = $leaf;
			}
			else
			{
				assert(false);
			}
		}
	}
	
	// Exports return function call objects, synthesizing the call appropriate for whether this is a global or static
	//  call.
	// Instance method exports are not currently supported.
	public function getFunctionCallObject()
	{
		$object = null;
		if (null !== $this->secondIdentifierToken)
		{
			$object = new OA_Symbol_StaticCall($this->firstIdentifierToken, $this->secondIdentifierToken);
		}
		else
		{
			$object = new OA_Symbol_GlobalCall($this->firstIdentifierToken);
		}
		return $object;
	}
}

?>
