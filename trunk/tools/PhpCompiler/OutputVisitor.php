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
//  leaf node visitation (in the tree nodes, themselves).  Only the leaf nodes are directly queried for their textual
//  representation but also their token types since care must be taken to only introduce whitespace between the nodes
//  which would otherwise change meaning if adjacent (keywords would become identifiers, for example).  We don't want to
//  gratuitously inject whitespace as that defeats the minification goal of the compiler but can also promote instance
//  variables into keywords ("var", for example).
class OA_OutputVisitor implements OA_ITreeWalker
{
	private static $spaceDelimitedTokens = array(
		OA_LexerNames::kIdentifier,
		OA_LexerNames::kVariable,
		
		OA_LexerNames::kCase,
		OA_LexerNames::kPrivate,
		OA_LexerNames::kProtected,
		OA_LexerNames::kPublic,
		OA_LexerNames::kStatic,
		OA_LexerNames::kFunction,
		OA_LexerNames::kClass,
		OA_LexerNames::kAbstract,
		OA_LexerNames::kInterface,
		OA_LexerNames::kAs,
		OA_LexerNames::kConst,
		OA_LexerNames::kReturn,
		OA_LexerNames::kArray,
		OA_LexerNames::kNew,
		OA_LexerNames::kExtends,
		OA_LexerNames::kImplements,
		OA_LexerNames::kInstanceOf,
	);
	
	
	private $buffer;
	private $needsSpace;
	
	
	// Creates an empty representation of the receiver.
	public function __construct()
	{
		$this->buffer = '';
		$this->needsSpace = false;
	}
	
	// OA_ITreeWalker.
	public function preVisitTree($tree)
	{
		// Do nothing.
	}
	
	// OA_ITreeWalker.
	public function postVisitTree($tree)
	{
		// Do nothing.
	}
	
	// OA_ITreeWalker.
	public function visitLeaf($leaf)
	{
		$leafName = $leaf->getName();
		$leafText = $leaf->getText();
		if (OA_LexerNames::kSpecialComment === $leafName)
		{
			// Special comments get passed through but put newlines around them since they are expected to be on their
			//  own lines.
			$this->buffer .= "\n$leafText\n";
			$this->needsSpace = false;
		}
		else
		{
			$needsSpace = in_array($leafName, OA_OutputVisitor::$spaceDelimitedTokens);
			if ($this->needsSpace && $needsSpace)
			{
				// If both this leaf and the previous leaf need space, we will add it between them.
				$this->buffer .= ' ';
			}
			$this->buffer .= $leafText;
			if (OA_LexerNames::kSemiColon === $leafName)
			{
				// We will drop new lines after semi-colons so that error messages provided during the run will have
				//  some hope of being decyphered.
				$this->buffer .= "\n";
				$this->needsSpace = false;
			}
			else
			{
				$this->needsSpace = $needsSpace;
			}
		}
	}
	
	// Called after the visit operation is complete to flush the visitor's internal buffer to an output stream.  Note
	//  that the receiver's behaviour is undefined on any calls made after this one.
	public function flush($stream)
	{
		fwrite($stream, $this->buffer);
		$this->buffer = null;
	}
}

?>
