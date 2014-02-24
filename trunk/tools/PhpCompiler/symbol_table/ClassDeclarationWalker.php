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
// The tree walker used by the symbol table builder to walk the subtree rooted in the class declaration.
class OA_ClassDeclarationWalker implements OA_ITreeWalker
{
	const kIdentifier = 'IDENTIFIER';
	const kClassLines = 'P_CLASS_LINES';
	const kClassLine = 'P_CLASS_LINE';
	const kStaticFunctionDecl = 'P_STATIC_FUNCTION_DECL';
	const kFunctionDecl = 'P_FUNCTION_DECL';
	
	// Since we need to dig a few levels down into the parse tree to start walking the class lines, here is the white
	//  list of nodes which we should be willing to visit.
	private static $whitelist = array(
		OA_ClassDeclarationWalker::kClassLines,
		OA_ClassDeclarationWalker::kClassLine,
		OA_ClassDeclarationWalker::kStaticFunctionDecl,
	);
	
	private $classNameToken;
	private $isWalkingClass;
	private $functionNameTokens;
	
	
	// Creates an empty representation of the receiver.
	public function __construct()
	{
		$this->classNameToken = null;
		$this->isWalkingClass = false;
		$this->functionNameTokens = array();
	}
	
	// OA_ITreeWalker.
	public function preVisitTree($tree)
	{
		// We only want to walk the class declaration and some specific details relating to static function declarations
		//  so white-list those tokens for further traversal.
		$shouldVisitChildren = true;
		if ($this->isWalkingClass)
		{
			$name = $tree->getName();
			$shouldVisitChildren = false;
			if (in_array($name, OA_ClassDeclarationWalker::$whitelist))
			{
				$shouldVisitChildren = true;
			}
			else if (OA_ClassDeclarationWalker::kFunctionDecl === $name)
			{
				// We can now switch into the function declaration walker since our white list and parse tree ensure
				//  that this is a static function declaration.
				$childWalker = new OA_FunctionDeclarationWalker();
				$tree->visit($childWalker);
				$token = $childWalker->getFunctionNameToken();
				assert(null !== $token);
				$this->functionNameTokens[] = $token;
			}
		}
		$this->isWalkingClass = true;
		return $shouldVisitChildren;
	}
	
	// OA_ITreeWalker.
	public function postVisitTree($tree)
	{
	}
	
	// OA_ITreeWalker.
	public function visitLeaf($leaf)
	{
		// We want to get the first identifier we encounter since that will be the class name.
		if ((OA_ClassDeclarationWalker::kIdentifier === $leaf->getName()) && (null === $this->classNameToken))
		{
			$this->classNameToken = $leaf;
		}
	}
	
	// Returns the lexer token of the class name.
	public function getClassNameToken()
	{
		return $this->classNameToken;
	}
	
	// Returns the array of lexer tokens representing the names of the static functions defined in this class.
	public function getStaticFunctionNameTokens()
	{
		return $this->functionNameTokens;
	}
}

?>
