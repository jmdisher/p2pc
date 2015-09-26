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


// Author:  Jeff Disher (Open Autonomy Inc.)
// The abstract class backing both the lexer-generated leaf tokens and parser-generated tree elements.  It provides the
//  common name facility as well as the required entry-point for the visitor interface.
abstract class OA_ParsedElement
{
	private $name;
	private $parentTree;
	
	protected function __construct($name)
	{
		$this->name = $name;
		$this->parentTree = null;
	}
	
	// Returns the name which identifies the type of node the receiver represents, within the grammar of the language.
	public function getName()
	{
		return $this->name;
	}
	
	// Sets the parent tree node to $parent.  This is only used for dead code elimination.
	public function setParent($parent)
	{
		assert($parent instanceof OA_ParseTree);
		assert(null === $this->parentTree);
		$this->parentTree = $parent;
	}
	
	// Removes the receiver from the parse tree by requesting that its parent remove it.  This is only used for dead
	//  code elimination.
	public function removeFromTree()
	{
		assert(null !== $this->parentTree);
		$this->parentTree->removeChild($this);
		$this->parentTree = null;
	}
	
	// Starts a recursive, in-order traversal of the parse tree rooted at the receiver node.
	public abstract function visit($visitor);
}

?>
