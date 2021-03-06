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
// The interface which describes the callbacks which will be made when passed into the "visit" method in
//  OA_ParsedElement.  Implementing this interface is required to inspect the parse tree.
interface OA_ITreeWalker
{
	// Called upon visiting a branching node structure (a parser reduction) before any of its children are visited.
	// Returns a boolean indicating whether the visitation should continue to $tree's children.  True means to continue
	//  while false will immediately call postVisitTree() and then return from this node.
	public function preVisitTree($tree);
	
	// Called upon visiting a branching node structure (a parser reduction) after any of its children have been visited.
	public function postVisitTree($tree);
	
	// Called upon visiting a leaf node structure (a lexer token).
	public function visitLeaf($leaf);
}

?>
