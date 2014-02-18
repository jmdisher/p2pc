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


// The maps which define keywords or regular expressions needed to convert incoming data into tokens.
class OA_LexerMaps
{
	// Note that keywords and other exact matches are kept in their own map to only be matched on success in order to
	//  avoid enforcing an order in the list of regexes or doing redundant matches on every step.
	public static $keywordMap = array(
		// Keywords.
		'while' => OA_LexerNames::kWhile,
		'break' => OA_LexerNames::kBreak,
		'for' => OA_LexerNames::kFor,
		'foreach' => OA_LexerNames::kForeach,
		'if' => OA_LexerNames::kIf,
		'else' => OA_LexerNames::kElse,
		'true' => OA_LexerNames::kTrue,
		'false' => OA_LexerNames::kFalse,
		'private' => OA_LexerNames::kPrivate,
		'protected' => OA_LexerNames::kProtected,
		'public' => OA_LexerNames::kPublic,
		'static' => OA_LexerNames::kStatic,
		'function' => OA_LexerNames::kFunction,
		'class' => OA_LexerNames::kClass,
		'abstract' => OA_LexerNames::kAbstract,
		'interface' => OA_LexerNames::kInterface,
		'as' => OA_LexerNames::kAs,
		'const' => OA_LexerNames::kConst,
		'return' => OA_LexerNames::kReturn,
		'array' => OA_LexerNames::kArray,
		'new' => OA_LexerNames::kNew,
		'extends' => OA_LexerNames::kExtends,
		'implements' => OA_LexerNames::kImplements,
		
		// Structure.
		"\n" => OA_LexerNames::kNewLine,
		'{' => OA_LexerNames::kOpenBrace,
		'}' => OA_LexerNames::kCloseBrace,
		'(' => OA_LexerNames::kOpenParen,
		')' => OA_LexerNames::kCloseParen,
		'[' => OA_LexerNames::kOpenSquare,
		']' => OA_LexerNames::kCloseSquare,
		';' => OA_LexerNames::kSemiColon,
		'::' => OA_LexerNames::kColonColon,
		',' => OA_LexerNames::kComma,
		'->' => OA_LexerNames::kCall,
		
		// Operators.
		'|' => OA_LexerNames::kBitOR,
		'||' => OA_LexerNames::kLogicOR,
		'|=' => OA_LexerNames::kOrEqual,
		'&' => OA_LexerNames::kBitAND,
		'&&' => OA_LexerNames::kLogicAND,
		'&=' => OA_LexerNames::kAndEqual,
		'^' => OA_LexerNames::kXOR,
		'!' => OA_LexerNames::kNOT,
		'/' => OA_LexerNames::kSlash,
		'-' => OA_LexerNames::kMinus,
		'--' => OA_LexerNames::kMinusMinus,
		'*' => OA_LexerNames::kStar,
		'*=' => OA_LexerNames::kStarEqual,
		'+' => OA_LexerNames::kPlus,
		'++' => OA_LexerNames::kPlusPlus,
		'+=' => OA_LexerNames::kPlusEqual,
		'-=' => OA_LexerNames::kMinusEqual,
		'.' => OA_LexerNames::kDot,
		'.=' => OA_LexerNames::kDotEqual,
		'%' => OA_LexerNames::kPercent,
		
		// Comparators.
		'=' => OA_LexerNames::kEqual,
		'==' => OA_LexerNames::kEqualEqual,
		'!=' => OA_LexerNames::kNotEqual,
		'===' => OA_LexerNames::kEqualEqualEqual,
		'!==' => OA_LexerNames::kNotEqualEqual,
		'>' => OA_LexerNames::kGreater,
		'=>' => OA_LexerNames::kEqualGreater,
		'>=' => OA_LexerNames::kGreaterEqual,
		'<' => OA_LexerNames::kLess,
		'=<' => OA_LexerNames::kEqualLess,
		'<=' => OA_LexerNames::kLessEqual,
		'?' => OA_LexerNames::kQuestion,
		':' => OA_LexerNames::kColon,
	);
	
	// This comparator function is used to sort the keyword map so it is provided here.
	// When used with a usort, this function will sort the keywords from longest to shortest.
	public static function lengthSort($a, $b)
	{
		$aLength = strlen($a);
		$bLength = strlen($b);
		$result = 0;
		if ($aLength !== $bLength)
		{
			$result = ($aLength > $bLength) ? -1 : 1;
		}
		return $result;
	}
}

?>
