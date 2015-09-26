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


// The class representing a single token of the stream.  Instances of this object are created by the lexer and returned
//  to the caller.
class OA_LexerToken extends OA_ParsedElement
{
	private $text;
	private $file;
	private $line;
	
	// Creates a new token instance.
	// Args:
	// -$name - The token type name.
	// -$text - The textual content in the source file underlying the token.
	public function __construct($name, $text, $file, $line)
	{
		parent::__construct($name);
		$this->text = $text;
		$this->file = $file;
		$this->line = $line;
	}
	
	// OA_ParsedElement.
	public function visit($visitor)
	{
		$visitor->visitLeaf($this);
	}
	
	// Returns the textual content underlying the token in the original source file.
	public function getText()
	{
		return $this->text;
	}
	
	public function getFile()
	{
		return $this->file;
	}
	
	public function getLine()
	{
		return $this->line;
	}
}

?>
