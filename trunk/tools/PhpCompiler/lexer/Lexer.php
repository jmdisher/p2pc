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
require_once('LexerNames.php');
require_once('LexerMaps.php');
require_once('LexerToken.php');


// This class is the lexer for the compiler.  It attaches to the preprocessor as data stream and converts it into a
//  token stream.
// Note that this is meant to be part of a pipeline so it internally doesn't store more than enough of a data stream
//  look-ahead to determine the token it just parsed.
// NOTE:  THIS BUFFER BECOMES LARGE IN 1 SPECIAL CASE:  THE BUFFER STARTS WITH '/*' (MUTLI-LINE COMMENT SUPPORT).
// Only the parts of the script which are within the start-end PHP tags are processed here.  The implementation assumes
//  that any other decorations are handled elsewhere.
// Regarding comments:
//  As is common with many lexers, comments are special-cases:
//  -single-line:  If the buffer starts with '//', then the rest of the buffer, up to the new line, is determined to be
//   a single-line comment and returned.
//  -multi-line:  If the buffer starts with '/*', then the buffer is scanned for the first '*/', adding new lines as
//   needed.
//  This is done because these tokens don't obey normal token rules:
//  -single-line:  The comment consumes the rest of the line, no matter what was found there.
//  -multi-line:  The comment greedily consumes characters until the first end is seen.
// "Special" comments:  Single-line comments starting with "//$" are considered "special" since they will not be
//  stripped as a normal comment as they are assumed to be used by downstream tools.
class OA_Lexer
{
	// Immutable attributes.
	private $preprocessor;
	private $sortedKeywordArray;
	
	// Active state.
	private $buffer;
	private $fileName;
	private $line;
	private $error;
	
	public function __construct($preprocessor)
	{
		$this->preprocessor = $preprocessor;
		// We want the keywords sorted so we check the longest ones first.
		$keywords = array_keys(OA_LexerMaps::$keywordMap);
		//EXPORT OA_LexerMaps::lengthSort;
		usort($keywords, 'OA_LexerMaps::lengthSort');
		$this->sortedKeywordArray = $keywords;
		
		// Prime the buffer.
		$this->_refreshBuffer();
	}
	
	// Returns the next OA_LexerToken instance in the stream or null, if there aren't any.
	public function getNextToken()
	{
		$token = null;
		// A line will be left empty once it has been processed so refill it.
		if ('' === $this->buffer)
		{
			$this->_refreshBuffer();
		}
		// If the buffer is valid, proceed.
		if (null !== $this->buffer)
		{
			$prefix = $this->buffer[0];
			if ('/' === $prefix)
			{
				// Handle the comment special-cases.
				if (0 === strpos($this->buffer, '//$'))
				{
					// "special" comments - these are set up as special so that they can be left in the output stream since
					//  other tools often want to sniff for information in comments which we don't want to remove them.
					// Consume everything except for the final character (the new line).
					$bufferLength = strlen($this->buffer);
					$token = $this->_advanceBufferAndCreate(OA_LexerNames::kSpecialComment, $bufferLength - 1);
				}
				else if (0 === strpos($this->buffer, '//EXPORT'))
				{
					// "export" comments - these are similar to "special" in that they are passed through the lexer and
					//  the parser sees them.  Their purpose is more specific, however, in that they are followed by a
					//  symbol name and they act as a "virtual callsite" for the function, for the purposes of dead code
					//  elimination liveness checking.  This is required since some functions are called via
					//  string-formatted callbacks, etc.
					$token = $this->_advanceBufferAndCreate(OA_LexerNames::kExportComment, strlen('//EXPORT'));
				}
				else if (0 === strpos($this->buffer, '//'))
				{
					// Single-line comment.
					// Consume everything except for the final character (the new line).
					$bufferLength = strlen($this->buffer);
					$token = $this->_advanceBufferAndCreate(OA_LexerNames::kSingleComment, $bufferLength - 1);
				}
				else if (0 === strpos($this->buffer, '/*'))
				{
					// Multi-line comment.
					// Keep adding lines to the buffer until we find the closing comment.
					$commentClose = '*/';
					while ((null === $this->error) && (false === strpos($this->buffer, $commentClose)))
					{
						list($line, $this->fileName, $this->line, $this->error) = $this->preprocessor->getLine();
						if (null !== $line)
						{
							$this->buffer .= $line;
						}
						else
						{
							$preError = $this->preprocessor->getError();
							if (null !== $preError)
							{
								$this->error = $preError;
							}
							else
							{
								// We fell off the end of the input.
								$this->error = 'End of input while searching for comment end';
							}
						}
					}
					// See if we succeeded.
					$end = strpos($this->buffer, $commentClose);
					if (false !== $end)
					{
						$token = $this->_advanceBufferAndCreate(OA_LexerNames::kMultiComment, $end + strlen($commentClose));
					}
				}
				else
				{
					// This falls back to the keyword slash case.
					$token = $this->_advanceBufferAndCreate(OA_LexerNames::kSlash, 1);
				}
			}
			else
			{
				// Normal lexing.
				// This has a few steps:
				// 1) Try to match all the regexes and store the result of the longest match.
				// 2) Try the keywords differently, based on whether a regex matched:
				//  -match) See if the matched string is a keyword.  If so, then return the keyword token, instead.
				//  -no match) Search for the keywords in the buffer and return them on match.
				// This ordering is meant to ensure that things like "while" are identified as a keyword but things like
				//  "whilenot" are not.
				$tokenName = null;
				$textLength = 0;
				switch ($prefix)
				{
					case '@':
						list($tokenName, $textLength) = $this->_parseIdentifier(OA_LexerNames::kSilentIdentifier, 1);
					break;
					case '$':
						list($tokenName, $textLength) = $this->_parseIdentifier(OA_LexerNames::kVariable, 1);
					break;
					case '\'':
						list($tokenName, $textLength) = $this->_matchQuotedString(OA_LexerNames::kSingleQuoteString, $prefix);
					break;
					case '"':
						list($tokenName, $textLength) = $this->_matchQuotedString(OA_LexerNames::kDoubleQuoteString, $prefix);
					break;
					case ' ':
					case "\t":
						list($tokenName, $textLength) = $this->_matchWhitespace(OA_LexerNames::kWhiteSpace);
					break;
					default:
						if (('_' === $prefix) || ctype_alpha($prefix))
						{
						list($tokenName, $textLength) = $this->_parseIdentifier(OA_LexerNames::kIdentifier, 0);
						}
						else if (ctype_digit($prefix))
						{
							list($tokenName, $textLength) = $this->_matchNumber();
						}
				}
				if (null !== $tokenName)
				{
					$tokenName = $this->_reduceRegexToken($tokenName, $textLength);
				}
				else
				{
					list($tokenName, $textLength) = $this->_getLongestKeywordToken();
				}
				
				if (null !== $tokenName)
				{
					assert($textLength > 0);
					$token = $this->_advanceBufferAndCreate($tokenName, $textLength);
				}
				else
				{
					$this->error = 'No token found starting line: ' . $this->buffer;
				}
			}
		}
		return $token;
	}
	
	// Returns a string representing the lexing error or null, if there wasn't an error.
	public function getError()
	{
		return $this->error;
	}
	
	
	private function _refreshBuffer()
	{
		// Prime the buffer.
		list($this->buffer, $this->fileName, $this->line, $this->error) = $this->preprocessor->getLine();
	}
	
	private function _advanceBufferAndCreate($tokenName, $count)
	{
		$extracted = null;
		$length = strlen($this->buffer);
		if ($count === $length)
		{
			$extracted = $this->buffer;
			$this->buffer = '';
		}
		else
		{
			assert($count < $length);
			$extracted = substr($this->buffer, 0, $count);
			$this->buffer = substr($this->buffer, $count);
		}
		return new OA_LexerToken($tokenName, $extracted, $this->fileName, $this->line);
	}
	
	private function _reduceRegexToken($longestToken, $longestLength)
	{
		$reducedToken = $longestToken;
		$text = substr($this->buffer, 0, $longestLength);
		if (isset(OA_LexerMaps::$keywordMap[$text]))
		{
			$reducedToken = OA_LexerMaps::$keywordMap[$text];
		}
		return $reducedToken; 
	}
	
	private function _getLongestKeywordToken()
	{
		$token = null;
		$length = 0;
		foreach ($this->sortedKeywordArray as $keyword)
		{
			if (0 === strpos($this->buffer, $keyword))
			{
				$length = strlen($keyword);
				$token = OA_LexerMaps::$keywordMap[$keyword];
				break;
			}
		}
		return array($token, $length);
	}
	
	private function _matchQuotedString($tokenName, $quote)
	{
		$i = 1;
		$endMatch = 0;
		$limit = strlen($this->buffer);
		$isEscape = false;
		while (($i < $limit) && (0 === $endMatch))
		{
			$char = $this->buffer[$i];
			if ('\\' === $char)
			{
				$isEscape = !$isEscape;
			}
			else
			{
				if (!$isEscape && ($quote === $char))
				{
					$endMatch = $i;
				}
				$isEscape = false;
			}
			$i += 1;
		}
		
		$token = null;
		$length = null;
		if (0 !== $endMatch)
		{
			$token = $tokenName;
			$length = $endMatch + 1;
		}
		else
		{
			$this->error = 'Reached end of line while reading string constant';
		}
		return array($token, $length);
	}
	
	private function _parseIdentifier($tokenName, $startIndex)
	{
		$i = $startIndex;
		$firstMiss = null;
		$limit = strlen($this->buffer);
		while (($i < $limit) && (null === $firstMiss))
		{
			$char = $this->buffer[$i];
			if (('_' === $char) || ctype_alnum($char))
			{
				$i += 1;
			}
			else
			{
				$firstMiss = $i;
			}
		}
		
		$token = null;
		$length = null;
		if ($firstMiss !== $startIndex)
		{
			$token = $tokenName;
			$length = $firstMiss;
		}
		else
		{
			$this->error = 'Expected identifier but found empty';
		}
		return array($token, $length);
	}
	
	private function _matchWhitespace($tokenName)
	{
		$i = 1;
		$firstMiss = null;
		$limit = strlen($this->buffer);
		while (($i < $limit) && (null === $firstMiss))
		{
			$char = $this->buffer[$i];
			if ((' ' === $char) || ("\t" === $char))
			{
				$i += 1;
			}
			else
			{
				$firstMiss = $i;
			}
		}
		
		if (null === $firstMiss)
		{
			$firstMiss = $limit;
		}
		$token = $tokenName;
		$length = $firstMiss;
		return array($token, $length);
	}
	
	private function _matchNumber()
	{
		$i = 1;
		$firstMiss = null;
		$isFloat = false;
		$didError = false;
		$limit = strlen($this->buffer);
		while (($i < $limit) && (null === $firstMiss) && !$didError)
		{
			$char = $this->buffer[$i];
			if (ctype_digit($char))
			{
				$i += 1;
			}
			else if ('.' === $char)
			{
				if (!$isFloat)
				{
					$isFloat = true;
				}
				else
				{
					$didError = true;
				}
				$i += 1;
			}
			else
			{
				$firstMiss = $i;
			}
		}
		
		$token = null;
		$length = null;
		if (!$didError)
		{
			$token = ($isFloat ? OA_LexerNames::kFloatConst : OA_LexerNames::kIntConst);
			$length = $firstMiss;
		}
		else
		{
			$this->error = 'Number had multiple decimal points';
		}
		return array($token, $length);
	}
}

?>
