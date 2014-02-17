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

// A PHP "compiler" used for static inlining of require_once.
// This reads in a starting PHP script and information making up an "include
//  path" and writes out a single PHP script, representing an inlined (order-
//  preserving) version of all the referenced files.

// Reasons for this:
// 1)  require_once is possibly the slowest single operation in PHP and it gets
//  slower for every directory added to the include path (and APC only magnifies
//  this cost as it keeps each file as a distinct block of opcodes).
// 2)  is is trivial to statically determine which files will be reachable from
//  a single entry-point script for every invocation (assuming that the
//  require_once is done as it would in a language like C:  unconditionally)
// 3)  doing this allows the total size of code reachable from a single entry-
//  point to be obviously determined
// 4)  well-factored code is typically organized into reusable and isolated
//  modules which incur extra require_once calls and add extra directories to
//  the include path, both of which reduce performance.  This performance
//  trade-off should not detract from writing code in way which is
//  understandable, maintainable, and reusable.

// Negative side-effects of this process:
// 1)  debugging is more difficult as line numbers are incorrect
// 2)  APC maximum files size thresholds must be increased (since each 
//  entry-point now includes all reachable code as one module)
// 3)  Total APC memory reservation must be increased (since there is now heavy
//  code duplication: a file is now duplicated for each file which includes it)

// Other approaches:
// 1)  avoid extra include paths by always using a relative component in the
//  require_once path.  This has its merits as it avoids include path problems
//  and arguably makes the require_once more meaningful to read (as the
//  factoring is obvious).  However, it only resolves part of the require_once
//  cost (include path searching).  It also has the negative side-effect that
//  the code now knows about the on-disk directory structure which is generally
//  considered a bad idea.

// Future ideas:
// For the moment, this is not so much a "compiler" as a preprocessor in that it
//  doesn't understand the language and just looks for keywords in order to
//  perform file resolutions (so, it is more like "cpp" than "gcc").  In the
//  future, this would be an ideal jumping-off point for a real compiler which
//  interprets the relevant subset of PHP (the subset used by OA) and re-writes
//  PHP output after analysis and basic optimizations have been performed.  The
//  performance optimizations which would be possible in this case would likely
//  be: dead code elimination and inlining trivial or single-call-site
//  functions.  Note that this would likely need to generate some kind of map
//  file to be used by diagnostic tools, though, since the output would not
//  closely resemble the input.  The bigger benefit would be in other kinds of
//  static verification:  type checking, correct parameter list and return list
//  size and type verification, and unused or inconsistently declared variables.
assert_options(ASSERT_BAIL, 1);
require_once('Preprocessor.php');

// This class only has a public constructor and one public method "compile" so using it is relatively straight-forward.
// In the future, it might be further extended to be more of a "compiler" than a "preprocessor" (which is a more appropriate name, for now).
class OA_PhpCompiler
{
	// An array of include paths to use when resolving a given require_once directive.
	private $includePathArray;
	// An array of file names which, when encountered as a require_once, should be ignored (that is, consume the require_once but don't process it).  These are generally files which include information which is either unused or incorrect once compiled (typically include path manipulation).
	private $ignoredFileNameArray;
	// The array of full paths to the already-included files.
	private $alreadyIncludedPathArray;
	// The array of file names which we have already referenced as an external require_once.
	private $alreadyReferencedFileNamesArray;
	
	
	// Creates the compiler instance, configured with the given arguments.  It can be re-run multiple times on new files
	//  with the same configuration.
	// Args:
	// -$options - The OA_CompilerOptions instance to configure the compiler.
	public function __construct($options)
	{
		assert($options instanceof OA_CompilerOptions);
		// This cannot be used this way.
		assert(null !== $options->preprocessorIncludePathsArray);
		// This cannot be used this way.
		assert(null !== $options->preprocessorIgnoredFileNameArray);
		$this->includePathArray = $options->preprocessorIncludePathsArray;
		$this->ignoredFileNameArray = $options->preprocessorIgnoredFileNameArray;
	}
	
	// Runs the compiler, with its constant configuration, on $inputFilePath, output written to $outputFileName.  This function leaves the receiver an a consistent state to be run again.
	// -$inputFilePath - a path which will resolve an input file for starting the compiler.  Cannot be null.
	// -$outputFileName - the name of the output file to which the resolve of the compilation will be written.  Note that the write is atomic.  If null, the output will be written to STDOUT.
	// Returns nothing.
	public function compile($inputFilePath, $outputFileName)
	{
		// By default, we run in a mode which outputs to STDOUT.
		$stream = STDOUT;
		// We write to a temporary file, first, for two reasons:  it allows for atomic file updating, and it means that the same file name can be input and output (otherwise, we would delete the input file by opening the new file before we have read it).
		$tempName = null;
		if (null !== $outputFileName)
		{
			$tempName = $outputFileName . '.tmp';
			$stream = fopen($tempName, 'w');
			// This error is unrecoverable.
			assert(FALSE !== $stream);
		}
		
		// Start the preprocessor.
		$preprocessor = new OA_Preprocessor($this->includePathArray, $this->ignoredFileNameArray);
		$startLines = $preprocessor->start($inputFilePath);
		// Writing to an internal buffer uses more memory but is faster than making lots of fwrites so we will batch the
		//  calls.
		$buffer = '';
		foreach ($startLines as $line)
		{
			$buffer .= $line;
		}
		fwrite($stream, $buffer);
		$buffer = '';
		while (null !== ($line = $preprocessor->getLine()))
		{
			$buffer .= $line;
		}
		fwrite($stream, $buffer);
		$buffer = '';
		$endLines = $preprocessor->end();
		foreach ($endLines as $line)
		{
			$buffer .= $line;
		}
		fwrite($stream, $buffer);
		
		if (STDOUT !== $stream)
		{
			fclose($stream);
			// This would be inconsistent with the above if statement behaviour.
			assert(null !== $tempName);
			$didRename = rename($tempName, $outputFileName);
			// This error is unrecoverable.
			assert($didRename);
		}
	}
}

?>
