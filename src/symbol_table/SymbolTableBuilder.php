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
require_once('CodeBlockHelpers.php');
require_once('FunctionRegistry.php');


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
	const kAbstractClassDecl = 'P_ABSTRACT_CLASS_DECL';
	const kInterfaceDecl = 'P_INTERFACE_DECL';
	
	
	// The array of global function declaration objects.
	private $functionObjects;
	// The array of class declaration objects.
	private $classObjects;
	// The array of calls made.
	private $functionCallObjects;
	
	
	// Creates an empty representation of the receiver.
	public function __construct()
	{
		$this->functionObjects = array();
		$this->classObjects = array();
		$this->functionCallObjects = array();
	}
	
	// OA_ITreeWalker.
	public function preVisitTree($tree)
	{
		// By default, we will walk all children unless we identity a sub-tree of specialized interest to us.
		$shouldVisitChildren = true;
		// There is no calling context for the top-level global calls.
		$callingContext = null;
		$name = $tree->getName();
		switch($name)
		{
			case OA_SymbolTableBuilder::kFunctionDecl:
				// We want to switch over to the function declaration walker so terminate our traversal of this tree.
				$shouldVisitChildren = false;
				$childWalker = new OA_FunctionDeclarationWalker($callingContext, $tree);
				$tree->visit($childWalker);
				$functionObject = $childWalker->getFunctionDeclarationObject();
				assert(null !== $functionObject);
				$this->functionObjects[] = $functionObject;
			break;
			case OA_SymbolTableBuilder::kClassDecl:
			case OA_SymbolTableBuilder::kAbstractClassDecl:
				// NOTE:  For the time being, we will parse interfaces as classes.
			case OA_SymbolTableBuilder::kInterfaceDecl:
				// We want to switch over to the class declaration walker so terminate our traversal of this tree.
				// Note that we also use this parser for abstract classes but any abstract function declarations will be
				//  skipped since they are a different parse tree shape than the other kinds of functions the class
				//  walks and processes.
				$shouldVisitChildren = false;
				$childWalker = new OA_ClassDeclarationWalker();
				$tree->visit($childWalker);
				$classObject = $childWalker->getClassDeclarationObject();
				assert(null !== $classObject);
				$this->classObjects[] = $classObject;
			break;
			default:
				$callObject = OA_CodeBlockHelpers::findCallObject($callingContext, $tree, $name);
				if (null !== $callObject)
				{
					$this->functionCallObjects[] = $callObject;
				}
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
	
	public function performDeadCodeElimination()
	{
		// Build the connections between caller and receiver ("mark").
		$registry = new OA_FunctionRegistry();
		foreach ($this->functionObjects as $functionObject)
		{
			$registry->registerNormalFunction($functionObject->getName(), $functionObject);
		}
		foreach ($this->classObjects as $classObject)
		{
			$classObject->registerAllFunctions($registry);
		}
		OA_SymbolTableBuilder::_markAllCalls($registry, $this->functionCallObjects);
		foreach ($this->classObjects as $classObject)
		{
			$exportedCalls = $classObject->getAllExportedCalls();
			OA_SymbolTableBuilder::_markAllCalls($registry, $exportedCalls);
		}
		
		// Now delete all dead receivers ("sweep").
		foreach ($this->functionObjects as $functionObject)
		{
			$functionObject->cleanIfDead();
		}
		foreach ($this->classObjects as $classObject)
		{
			$classObject->cleanDeadFunctions();
		}
	}
	
	// Called to write a human readable report of the created symbol table to an output stream.
	public function writeReportToStream($stream)
	{
		// Output all of the top-level global functions.
		foreach ($this->functionObjects as $functionObject)
		{
			$string = $functionObject->getDescription('GLOBAL ', '');
			fwrite($stream, $string);
		}
		// Output all of the classes.
		foreach ($this->classObjects as $classObject)
		{
			$string = $classObject->getDescription('');
			fwrite($stream, $string);
		}
		// Output top-level calls.
		fwrite($stream, "Top-level function calls\n");
		foreach ($this->functionCallObjects as $functionCallObject)
		{
			$string = $functionCallObject->getDescription();
			fwrite($stream, $string);
		}
	}
	
	
	private static function _markAllCalls($registry, $callArray)
	{
		foreach($callArray as $call)
		{
			$functionObjects = $call->getTargetsFromRegistry($registry);
			foreach ($functionObjects as $functionObject)
			{
				$calls = $functionObject->setAliveAndGetCalls();
				OA_SymbolTableBuilder::_markAllCalls($registry, $calls);
			}
		}
	}
}

?>
