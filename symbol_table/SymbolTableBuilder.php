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
require_once('FunctionDeclarationWalker.php');
require_once('ClassDeclarationWalker.php');


// Author:  Jeff Disher (Open Autonomy Inc.)
// An implementation of the tree walker which exists to build a description of various symbols within a program for use
//  in some analysis routines or for output into an offline report.
// Note that this walker demonstrates the brittle implications of walking the parse tree instead of a high-level
//  abstract syntax tree.  If that is added in the future, it will make visitors like this far more robust.  Currently,
//  a change to the grammar could break this implementation.
class OA_SymbolTableBuilder implements OA_ITreeWalker
{
	// Define the constants we are interested in searching through 
	const kFunctionDecl = 'P_FUNCTION_DECL';
	const kClassDecl = 'P_CLASS_DECL';
	
	
	// The array of global function name tokens.
	private $functionNameTokens;
	// The array of class name tokens.
	private $classNameTokens;
	// The map of textual class name strings to arrays of the function name tokens for all static functions declared
	//  within said class.
	private $classNameToStaticFunctions;
	
	
	// Creates an empty representation of the receiver.
	public function __construct()
	{
		$this->functionNameTokens = array();
		$this->classNameTokens = array();
		$this->classNameToStaticFunctions = array();
	}
	
	// OA_ITreeWalker.
	public function preVisitTree($tree)
	{
		// By default, we will walk all children unless we identity a sub-tree of specialized interest to us.
		$shouldVisitChildren = true;
		$name = $tree->getName();
		switch($name)
		{
			case OA_SymbolTableBuilder::kFunctionDecl:
				// We want to switch over to the function declaration walker so terminate our traversal of this tree.
				$shouldVisitChildren = false;
				$childWalker = new OA_FunctionDeclarationWalker();
				$tree->visit($childWalker);
				$token = $childWalker->getFunctionNameToken();
				assert(null !== $token);
				$this->functionNameTokens[] = $token;
			break;
			case OA_SymbolTableBuilder::kClassDecl:
				// We want to switch over to the class declaration walker so terminate our traversal of this tree.
				$shouldVisitChildren = false;
				$childWalker = new OA_ClassDeclarationWalker();
				$tree->visit($childWalker);
				$token = $childWalker->getClassNameToken();
				assert(null !== $token);
				$this->classNameTokens[] = $token;
				$this->classNameToStaticFunctions[$token->getText()] = $childWalker->getStaticFunctionNameTokens();
			break;
			default:
		}
		return $shouldVisitChildren;
	}
	
	// OA_ITreeWalker.
	public function postVisitTree($tree)
	{
		// Do nothing.
	}
	
	// OA_ITreeWalker.
	public function visitLeaf($leaf)
	{
		// This walker isn't interested in leaf nodes.
	}
	
	// Called to write a human readable report of the created symbol table to an output stream.
	public function writeReportToStream($stream)
	{
		// Output all of the top-level global functions.
		foreach ($this->functionNameTokens as $token)
		{
			$functionName = $token->getText();
			$fileName = $token->getFile();
			$lineNumber = $token->getLine();
			$string = "GLOBAL FUNCTION: $functionName\n\t$fileName:$lineNumber\n";
			fwrite($stream, $string);
		}
		// Output all of the classes.
		foreach ($this->classNameTokens as $token)
		{
			$className = $token->getText();
			$fileName = $token->getFile();
			$lineNumber = $token->getLine();
			$string = "CLASS: $className\n\t$fileName:$lineNumber\n";
			fwrite($stream, $string);
			$staticTokens = $this->classNameToStaticFunctions[$className];
			// Output all of the static functions in said classes.
			foreach ($staticTokens as $staticToken)
			{
				$functionName = $staticToken->getText();
				$fileName = $staticToken->getFile();
				$lineNumber = $staticToken->getLine();
				$functionString = "\tSTATIC FUNCTION: $className::$functionName\n\t\t$fileName:$lineNumber\n";
				fwrite($stream, $functionString);
			}
		}
	}
}

?>
