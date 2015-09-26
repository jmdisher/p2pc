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


// The token names used by the lexer.
class OA_LexerNames
{
	// Keywords.
	const kWhile = 'WHILE';
	const kBreak = 'BREAK';
	const kFor = 'FOR';
	const kForeach = 'FOREACH';
	const kIf = 'IF';
	const kElse = 'ELSE';
	const kSwitch = 'SWITCH';
	const kCase = 'CASE';
	const kDefault = 'DEFAULT';
	const kTrue = 'TRUE';
	const kFalse = 'FALSE';
	const kNull = 'NULL';
	const kPrivate = 'PRIVATE';
	const kProtected = 'PROTECTED';
	const kPublic = 'PUBLIC';
	const kStatic = 'STATIC';
	const kFunction = 'FUNCTION';
	const kClass = 'CLASS';
	const kAbstract = 'ABSTRACT';
	const kInterface = 'INTERFACE';
	const kAs = 'AS';
	const kConst = 'CONST';
	const kReturn = 'RETURN';
	const kArray = 'ARRAY';
	const kList = 'LIST';
	const kNew = 'NEW';
	const kExtends = 'EXTENDS';
	const kImplements = 'IMPLEMENTS';
	const kEcho = 'ECHO';
	const kInstanceOf = 'INSTANCEOF';
	const kFile = 'FILE';
	const kDirectorySeparator = 'DIR_SEP';
	const kPathSeparator = 'PATH_SEP';
	const kGlobal = 'GLOBAL';
	const kParent = 'PARENT';
		
	// Structure.
	const kNewLine = 'NEW_LINE';
	const kOpenBrace = 'OPEN_BRACE';
	const kCloseBrace = 'CLOSE_BRACE';
	const kOpenParen = 'OPEN_PAREN';
	const kCloseParen = 'CLOSE_PAREN';
	const kOpenSquare = 'OPEN_SQUARE';
	const kCloseSquare = 'CLOSE_SQUARE';
	const kSemiColon = 'SEMI_COLON';
	const kColonColon = 'COLON_COLON';
	const kComma = 'COMMA';
	const kCall = 'CALL';
	const kAt = 'AT';
		
	// Operators.
	const kBitOR = 'BIT_OR';
	const kLogicOR = 'LOGIC_OR';
	const kOrEqual = 'OR_EQUAL';
	const kBitAND = 'BIT_AND';
	const kLogicAND = 'LOGIC_AND';
	const kAndEqual = 'AND_EQUAL';
	const kXOR = 'XOR';
	const kNOT = 'NOT';
	const kSlash = 'SLASH';
	const kMinus = 'MINUS';
	const kMinusMinus = 'MINUS_MINUS';
	const kStar = 'STAR';
	const kStarEqual = 'STAR_EQUAL';
	const kPlus = 'PLUS';
	const kPlusPlus = 'PLUS_PLUS';
	const kPlusEqual = 'PLUS_EQUAL';
	const kMinusEqual = 'MINUS_EQUAL';
	const kDot = 'DOT';
	const kDotEqual = 'DOT_EQUAL';
	const kPercent = 'PERCENT';
		
	// Comparators.
	const kEqual = 'EQUAL';
	const kEqualEqual = 'EQUAL_EQUAL';
	const kNotEqual = 'NOT_EQUAL';
	const kEqualEqualEqual = 'EQUAL_EQUAL_EQUAL';
	const kNotEqualEqual = 'NOT_EQUAL_EQUAL';
	const kGreater = 'GREATER';
	const kEqualGreater = 'EQUAL_GREATER';
	const kGreaterEqual = 'GREATER_EQUAL';
	const kLess = 'LESS';
	const kEqualLess = 'EQUAL_LESS';
	const kLessEqual = 'LESS_EQUAL';
	const kQuestion = 'QUESTION';
	const kColon = 'COLON';
	
	// Regex.
	const kIdentifier = 'IDENTIFIER';
	const kWhiteSpace = 'WHITE_SPACE';
	const kFloatConst = 'FLOAT_CONST';
	const kIntConst = 'INT_CONST';
	const kVariable = 'VARIABLE';
	const kSingleQuoteString = 'SINGLE_QUOTE_STRING';
	const kDoubleQuoteString = 'DOUBLE_QUOTE_STRING';
	
	// Comments.
	const kSingleComment = 'SINGLE_COMMENT';
	const kMultiComment = 'MULTI_COMMENT';
	const kSpecialComment = 'SPECIAL_COMMENT';
	const kExportComment = 'EXPORT_COMMENT';
}

?>
