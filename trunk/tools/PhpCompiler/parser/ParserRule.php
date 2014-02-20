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
require_once('ParseTree.php');


// Author:  Jeff Disher (Open Autonomy Inc.)
// The representation of a reduction rule in the parser.  Given the set of tokens named in the right-hand-side of the
//  rule, it will convert them into a tree under a new token with the name given in the left-hand-side of the rule.
// This is the implementation of a REDUCTION and is the mechanism which builds the branching nodes of the parse tree.
class OA_ParserRule
{
	private $lhs;
	private $rhs;
	
	// Creates a rule which, when given an array of tokens with names listed in $rhs, will produce a tree node with the
	//  name given in $lhs.
	public function __construct($lhs, $rhs)
	{
		assert(is_array($rhs));
		$this->lhs = $lhs;
		$this->rhs = $rhs;
	}
	
	// Returns the number of tokens this will consume, when applied.
	public function consumedCount()
	{
		return count($this->rhs);
	}
	
	// Returns a new parse tree token with the in-order children given in $tokenArray.
	public function applyRule($tokenArray)
	{
		$size = count($tokenArray);
		assert($size === count($this->rhs));
		$tree = new OA_ParseTree($this->lhs);
		for ($i = 0; $i < $size; ++$i)
		{
			$tokenObject = $tokenArray[$i];
			assert($tokenObject->getName() === $this->rhs[$i]);
			$tree->addChild($tokenObject);
		}
		return $tree;
	}
}

?>
