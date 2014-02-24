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
require_once('ParsedElement.php');


// Author:  Jeff Disher (Open Autonomy Inc.)
// The class representing a branching structure within the parse tree.  Instances of this object are created by
//  OA_ParserRule when applying a reduction rule within OA_Parser.
class OA_ParseTree extends OA_ParsedElement
{
	private $children;
	
	// Creates a new tree instance with no children.
	// Args:
	// -$name - The token type name.
	public function __construct($name)
	{
		parent::__construct($name);
		$this->children = array();
	}
	
	// OA_ParsedElement.
	public function visit($visitor)
	{
		$shouldVisit = $visitor->preVisitTree($this);
		if ($shouldVisit)
		{
			foreach ($this->children as $child)
			{
				$child->visit($visitor);
			}
		}
		$visitor->postVisitTree($this);
	}
	
	// Adds the given child to the end of the receiver's list of children.
	public function addChild($child)
	{
		assert($child instanceof OA_ParsedElement);
		$this->children[] = $child;
	}
}

?>
