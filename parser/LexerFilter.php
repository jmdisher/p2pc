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
// The object placed between the lexer and the parser to provide the interface the parser wants to see (in that it
//  buffers a single token for "peaking") but also stripping out the node types which the parser wants to ignore (such
//  as whitespace or special built-in keywords which have special meaning to the lexer but are just typed constants to
//  the parser).
// Note that this also handles end-of-input by synthesizing the "$end" tokens used by the parser to allow for its final
//  shift and reductions to close and accept the tree.
class OA_LexerFilter
{
	private $lexer;
	private $nextToken;
	private $isDone;
	
	// Creates an instance of the receiver using $lexer as its token source.
	public function __construct($lexer)
	{
		assert($lexer instanceof OA_Lexer);
		$this->lexer = $lexer;
		$this->nextToken = null;
		$this->isDone = false;
	}
	
	// Returns the next token available in the stream but doesn't move past it.  If the input is exhausted, this will
	//  return the $end token.
	public function peakNextToken()
	{
		while (!$this->isDone && (null === $this->nextToken))
		{
			$token = $this->lexer->getNextToken();
			if (null !== $token)
			{
				$name = $token->getName();
				switch ($name)
				{
					case OA_LexerNames::kWhiteSpace:
					case OA_LexerNames::kNewLine:
					case OA_LexerNames::kSingleComment:
					case OA_LexerNames::kMultiComment:
						// Strip all whitespace, new lines, and normal comments.
					break;
					// Handle the cases of special tokens which will be strings at runtime.
					case OA_LexerNames::kFile:
					case OA_LexerNames::kDirectorySeparator:
					case OA_LexerNames::kPathSeparator:
						$this->nextToken = new OA_LexerToken(OA_LexerNames::kSingleQuoteString, $token->getText(), $token->getFile(), $token->getLine());
					break;
					default:
						// We use anything else.
						$this->nextToken = $token;
				}
			}
			else
			{
				$this->isDone = true;
				$this->nextToken = new OA_LexerToken('$end', '', '', '');
			}
		}
		return $this->nextToken;
	}
	
	// Advances to the next token in the stream, unless the stream is already at $end.
	public function acceptToken()
	{
		assert(null !== $this->nextToken);
		if (!$this->isDone)
		{
			$this->nextToken = null;
		}
	}
}

?>
