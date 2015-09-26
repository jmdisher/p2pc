#!/usr/bin/php
<?php
/*
 Copyright (c) 2013 Open Autonomy Inc.
 
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

// The executable entry-point for the OA PHP-PHP Compiler.
// The core compiler doesn't have the shell executable entry-point since it can
//  be included as used generically, by other components.
// This entry-point is a very quick-and-dirty approach to creating a command-
//  line callable entry-point for the PhpCompiler.
$includePath = get_include_path();
$currentDirectory = dirname(__FILE__);
$newComponents = array(
	$currentDirectory,
	$currentDirectory . DIRECTORY_SEPARATOR . 'preprocessor',
	$currentDirectory . DIRECTORY_SEPARATOR . 'lexer',
	$currentDirectory . DIRECTORY_SEPARATOR . 'parser',
	$currentDirectory . DIRECTORY_SEPARATOR . 'symbol_table',
);
set_include_path($includePath . PATH_SEPARATOR . implode(PATH_SEPARATOR, $newComponents));
require_once('PhpCompiler.php');
require_once('CompilerOptions.php');


// Returns list($inputFilePath, $outputFileName).
function parseCommandLine($options, $argc, $argv)
{
	$inputFilePath = null;
	$outputFileName = null;
	
	$parsingInclude = false;
	$parsingIgnore = false;
	$parsingOutput = false;
	for ($i = 1; $i < $argc; ++$i)
	{
		$arg = $argv[$i];
		if ($parsingInclude)
		{
			$options->preprocessorIncludePathsArray[] = realpath($arg);
			$parsingInclude = false;
		}
		else if ($parsingOutput)
		{
			$outputFileName = $arg;
			$parsingOutput = false;
		}
		else if ($parsingIgnore)
		{
			$options->preprocessorIgnoredFileNameArray[] = $arg;
			$parsingIgnore = false;
		}
		else if ('-I' === $arg)
		{
			assert(!$parsingOutput);
			assert(!$parsingIgnore);
			$parsingInclude = true;
		}
		else if ('-g' === $arg)
		{
			assert(!$parsingOutput);
			assert(!$parsingInclude);
			$parsingIgnore = true;
		}
		else if ('-o' === $arg)
		{
			assert(!$parsingInclude);
			assert(!$parsingIgnore);
			$parsingOutput = true;
		}
		else if ('--preprocess' === $arg)
		{
			$options->preprocessOnly = true;
		}
		else if ('--strip' === $arg)
		{
			$options->preprocessOnly = false;
			$options->stripOnly = true;
		}
		else if ('--parser' === $arg)
		{
			$options->preprocessOnly = false;
			$options->stripOnly = false;
			$path = $argv[$i + 1];
			$grammarFilePath = realpath($path);
			if (false !== $grammarFilePath)
			{
				$options->parserGrammarFilePath = $grammarFilePath;
			}
			else
			{
				echo "Grammar file path (\"$path\") invalid\n";
				exit(1);
			}
		}
		else if ('--testLexer' === $arg)
		{
			$options->testLexer = true;
			$options->preprocessOnly = false;
			$options->stripOnly = false;
		}
		else if ('--symbolTable' === $arg)
		{
			$options->preprocessOnly = false;
			$options->stripOnly = false;
			$options->symbolTableReportPath = $argv[$i + 1];
		}
		else if ('--deadCode' === $arg)
		{
			$options->preprocessOnly = false;
			$options->stripOnly = false;
			$options->eliminateDeadCode = true;
		}
		else
		{
			$inputFilePath = realPath($arg);
		}
	}
	return array($inputFilePath, $outputFileName);
}


$options = new OA_CompilerOptions();
// Set the default grammar file location:  We default to assuming that the parser grammar is right next to the
//  entry-point script (as it is in the package).
$grammarFilePath = realpath(dirname($argv[0]) . '/grammar.xml');
if (false !== $grammarFilePath)
{
	$options->parserGrammarFilePath = $grammarFilePath;
}
list($inputFilePath, $outputFileName) = parseCommandLine($options, $argc, $argv);
if (null !== $inputFilePath)
{
	$compiler = new OA_PhpCompiler($options);
	$compiler->compile($inputFilePath, $outputFileName);
}
else
{
	echo "Usage:  p2pc.php [-I include/path]* [-g ignored_file]* [-o output_file] [--preprocess] [--strip] [--parser <grammar.xml>] [--symbolTable <symbol_table_output>] [--testLexer] input_file\n";
}

?>
