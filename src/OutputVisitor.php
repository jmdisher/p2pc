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
// An implementation of the tree walker which exists to serialize a parse tree back into a PHP source file.  This is the
//  end-point of any compilation operation since the source and target language are both PHP.
// This means that the high-level structure of the parse tree nodes are ignored and only serve to direct the order of
//  leaf node visitation (in the tree nodes, themselves).  Only the leaf nodes are of interest and they are passed to
//  the provided $outputStream for output formatting.
class OA_OutputVisitor implements OA_ITreeWalker
{
	private $outputStream;
	
	
	// Creates an empty representation of the receiver.
	public function __construct($outputStream)
	{
		$this->outputStream = $outputStream;
	}
	
	// OA_ITreeWalker.
	public function preVisitTree($tree)
	{
		// We want to walk all leaves so we need to visit the children of every tree node.
		return true;
	}
	
	// OA_ITreeWalker.
	public function postVisitTree($tree)
	{
		// Do nothing.
	}
	
	// OA_ITreeWalker.
	public function visitLeaf($leaf)
	{
		$this->outputStream->writeToken($leaf);
	}
}

?>
