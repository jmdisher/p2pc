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
require_once('Symbol_ClassDeclaration.php');
require_once('StaticFunctionDeclarationWalker.php');
require_once('InstanceFunctionDeclarationWalker.php');
require_once('CallingContext.php');
require_once('AbstractFunctionDeclarationWalker.php');


// Author:  Jeff Disher (Open Autonomy Inc.)
// The tree walker used by the symbol table builder to walk the subtree rooted in the class declaration.
class OA_ClassDeclarationWalker implements OA_ITreeWalker
{
	const kIdentifier = 'IDENTIFIER';
	const kClassLines = 'P_CLASS_LINES';
	const kClassLine = 'P_CLASS_LINE';
	const kInterfaceLines = 'P_INTERFACE_LINES';
	const kStaticFunctionDecl = 'P_STATIC_FUNCTION_DECL';
	const kInstanceFunctionDecl = 'P_INST_FUNCTION_DECL';
	const kInterfaceLine = 'P_INTERFACE_LINE';
	const kAbstractFunctionDecl = 'P_ABSTRACT_FUNCTION_DECL';
	const kExtension = 'P_EXTENSION';
	
	// Since we need to dig a few levels down into the parse tree to start walking the class lines, here is the white
	//  list of nodes which we should be willing to visit.
	private static $whitelist = array(
		OA_ClassDeclarationWalker::kClassLines,
		OA_ClassDeclarationWalker::kClassLine,
		OA_ClassDeclarationWalker::kInterfaceLines,
	);
	
	private $className;
	private $classObject;
	private $isWalkingClass;
	// Note that we will handle the case of the extension clause within this walker, itself.  This is a bit of a hack
	//  but it is a very simple clause so it is simpler than creating a whole new walker class.
	private $isWalkingExtension;
	private $callingContext;
	
	
	// Creates an empty representation of the receiver.
	public function __construct()
	{
		$this->className = null;
		$this->classObject = null;
		$this->isWalkingClass = false;
		$this->isWalkingExtension = false;
		$this->callingContext = null;
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
			else if (OA_ClassDeclarationWalker::kStaticFunctionDecl === $name)
			{
				// Switch to the static function declaration walker and extract the function it finds.
				$childWalker = new OA_StaticFunctionDeclarationWalker();
				$tree->visit($childWalker);
				$functionObject = $childWalker->getFunctionDeclarationObject();
				assert(null !== $functionObject);
				$this->classObject->addStaticFunction($functionObject);
			}
			else if (OA_ClassDeclarationWalker::kInstanceFunctionDecl === $name)
			{
				// Switch to the instance function declaration walker and extract the function it finds.
				$childWalker = new OA_InstanceFunctionDeclarationWalker($this->callingContext);
				$tree->visit($childWalker);
				$functionObject = $childWalker->getFunctionDeclarationObject();
				assert(null !== $functionObject);
				$this->classObject->addInstanceFunction($functionObject);
			}
			else if ((OA_ClassDeclarationWalker::kInterfaceLine === $name) || (OA_ClassDeclarationWalker::kAbstractFunctionDecl === $name))
			{
				// Switch to the abstract function declaration walker and extract the function it finds as a root.
				$childWalker = new OA_AbstractFunctionDeclarationWalker();
				$tree->visit($childWalker);
				$functionObject = $childWalker->getFunctionDeclarationObject();
				assert(null !== $functionObject);
				$this->classObject->addExportedFunction($functionObject);
			}
			else if (OA_ClassDeclarationWalker::kExtension === $name)
			{
				$this->isWalkingExtension = true;
				$shouldVisitChildren = true;
			}
		}
		$this->isWalkingClass = true;
		return $shouldVisitChildren;
	}
	
	// OA_ITreeWalker.
	public function postVisitTree($tree)
	{
		if ($this->isWalkingExtension && (OA_ClassDeclarationWalker::kExtension === $tree->getName()))
		{
			$this->isWalkingExtension = false;
		}
	}
	
	// OA_ITreeWalker.
	public function visitLeaf($leaf)
	{
		// We want to get the first identifier we encounter since that will be the class name.
		if (OA_ClassDeclarationWalker::kIdentifier === $leaf->getName())
		{
			if (null === $this->classObject)
			{
				$this->className = $leaf->getText();
				$this->classObject = new OA_Symbol_ClassDeclaration($leaf);
			}
			else if ($this->isWalkingExtension)
			{
				$this->classObject->setSuperclassName($leaf->getText());
				$this->callingContext = new OA_CallingContext($leaf);
			}
		}
	}
	
	public function getClassDeclarationObject()
	{
		return $this->classObject;
	}
}

?>
