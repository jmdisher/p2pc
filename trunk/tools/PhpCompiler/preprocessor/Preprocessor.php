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
assert_options(ASSERT_BAIL, 1);

// This preprocessor is used by OA_PhpCompiler to produce a stream of bytes for lexing or serialization by attempting to
//  inline all require_once calls found in the starting file.
// Note that, despite lexers typically working on byte-level granularity, this interface exposes a line-at-once level of
//  granularity since that avoids extra calls through the API given that the internal preprocessor implementation uses
//  lines and any external lexer needs to maintain an internal buffer, anyway.
class OA_Preprocessor
{
	// An array of include paths to use when resolving a given require_once directive.
	private $includePathArray;
	// An array of file names which, when encountered as a require_once, should be ignored (that is, consume the require_once but don't process it).  These are generally files which include information which is either unused or incorrect once compiled (typically include path manipulation).
	private $ignoredFileNameArray;
	
	// The array of full paths to the already-included files.
	private $alreadyIncludedPathArray;
	// The array of file names which we have already referenced as an external require_once.
	private $alreadyReferencedFileNamesArray;
	private $postLines;
	private $lineStack;
	private $fileStack;
	private $lineNumberStack;
	private $error;
	
	private $currentLines;
	private $currentFileName;
	private $currentLineNumber;
	
	
	// Creates the compiler instance, configured with the given arguments.  It can be re-run multiple times on new files with the same configuration.
	// -$includePathArray - an array of paths, relative to the current directory, which will be used to resolve file paths.  Cannot be null.
	// -$ignoredFileNameArray - an array of the file names which will be ignored when a require is resolved (useful for files which include data which is invalid or redundant when compiled).  Cannot be null.
	public function __construct($includePathArray, $ignoredFileNameArray)
	{
		// This cannot be used this way.
		assert(null !== $includePathArray);
		// This cannot be used this way.
		assert(null !== $ignoredFileNameArray);
		$this->includePathArray = $includePathArray;
		$this->ignoredFileNameArray = $ignoredFileNameArray;
	}
	
	// Starts preprocessing the given $inputFilePath, returning the string, up to and including the first PHP start.
	public function start($inputFilePath)
	{
		// This cannot be used this way.
		assert(null !== $inputFilePath);
		$realInputPath = realpath($inputFilePath);
		assert(FALSE !== $realInputPath);
		
		// Set up the receiver ivar state.
		$this->alreadyIncludedPathArray = array();
		$this->alreadyReferencedFileNamesArray = array();
		$this->lineStack = array();
		$this->fileStack = array();
		$this->lineNumberStack = array();
		$this->startingDirectory = dirname($realInputPath);
		
		$this->currentLines = array();
		$this->currentFileName = '';
		$this->currentLineNumber = 0;
		
		$toReturn = null;
		list($preLines, $lines, $this->postLines) = $this->_loadFile($realInputPath);
		if (null === $this->error)
		{
			// Process these lines until we find one containing only the PHP start.
			if (count($lines) > 0)
			{
				$this->currentLines = $lines;
				$this->currentFileName = $realInputPath;
				$this->currentLineNumber = count($preLines);
			}
			$toReturn = $preLines;
		}
		return array($toReturn, $this->error);
	}
	
	// Returns the next line in the input, returning null on error, end-of-file, or encountering PHP end in the initial
	//  include file.
	public function getLine()
	{
		$line = $this->_fetchNextLine();
		$lineToSet = null;
		
		while ((null === $this->error) && (null !== $line) && (null === $lineToSet))
		{
			// If this is a require_once line, we need to process it to see if the file should be inlined, ignored, or
			//  processed as PHP code.
			$requireOnce = 'require_once(';
			if (0 === strpos($line, $requireOnce))
			{
				// This is a require_once line so see how we should handle this one before proceeding.
				$endIndex = strpos($line, ')');
				$requireOnceLength = strlen($requireOnce) + 1;
				$subFileName = substr($line, $requireOnceLength, $endIndex - $requireOnceLength - 1);
				if (in_array($subFileName, $this->ignoredFileNameArray))
				{
					// We can ignore this require_once line so do nothing and loop around again.
				}
				else
				{
					$subFilePath = $this->_getRealPath($subFileName);
					if (null !== $subFilePath)
					{
						// We found the file so see what we should do with it.
						if (in_array($subFilePath, $this->alreadyIncludedPathArray))
						{
							// This is a valid file which we could include but we have already done so so skip it.
						}
						else
						{
							// We did find the file, we haven't yet included it, and it isn't in our ignored list so we can
							//  try to inline it.
							list($preLines, $lines, $postLines) = $this->_loadFile($subFilePath);
							if (null === $this->error)
							{
								if (count($lines) > 0)
								{
									// Push our existing state onto the stack and assume these.
									array_push($this->lineStack, $this->currentLines);
									array_push($this->fileStack, $this->currentFileName);
									array_push($this->lineNumberStack, $this->currentLineNumber);
									
									$this->currentLines = $lines;
									$this->currentFileName = $subFilePath;
									$this->currentLineNumber = count($preLines);
								}
							}
						}
					}
					else
					{
						// We couldn't find this so we will leave the existing require_once (as this means we can still
						//  leave large, external libraries in their original shapes).
						if (in_array($subFileName, $this->alreadyReferencedFileNamesArray))
						{
							// We already have this file referenced so we will skip referencing it a second time.
						}
						else
						{
							// Return this as a normal line.
							$this->alreadyReferencedFileNamesArray[] = $subFileName;
							$lineToSet = $line;
						}
					}
				}
			}
			else
			{
				// This is just a normal line so we can return it.
				$lineToSet = $line;
			}
			
			// If we didn't find a line to return, get the next one.
			if (null === $lineToSet)
			{
				$line = $this->_fetchNextLine();
			}
		}
		assert(false !== $lineToSet);
		return array($lineToSet, $this->currentFileName, $this->currentLineNumber, $this->error);
	}
	
	public function end()
	{
		return $this->postLines;
	}
	
	
	private function _fetchNextLine()
	{
		$nextLine = null;
		if (0 === count($this->currentLines))
		{
			if (count($this->lineStack) > 0)
			{
				$this->currentLines = array_pop($this->lineStack);
				$this->currentFileName = array_pop($this->fileStack);
				$this->currentLineNumber = array_pop($this->lineNumberStack);
				assert(count($this->currentLines) > 0);
			}
		}
		if (0 !== count($this->currentLines))
		{
			$nextLine = array_shift($this->currentLines);
			$this->currentLineNumber += 1;
		}
		return $nextLine;
	}
	
	private function _loadFile($realPath)
	{
		$this->alreadyIncludedPathArray[] = $realPath;
		
		// Load the initial file contents.
		$lines = file($realPath);
		// We don't expect to fail to read the file.
		assert(FALSE !== $lines);
		// We want to make sure that there is exactly one PHP start and PHP end in this file.
		$startCount = 0;
		$endCount = 0;
		$preLines = array();
		$phpLines = array();
		$postLines = array();
		$isPre = true;
		$isPhp = false;
		foreach ($lines as $line)
		{
			if ($isPre)
			{
				$preLines[] = $line;
				if ("<?php\n" === $line)
				{
					$startCount += 1;
					assert(1 === $startCount);
					$isPre = false;
					$isPhp = true;
				}
			}
			else if ($isPhp)
			{
				if ("?>\n" === $line)
				{
					$endCount += 1;
					assert(1 === $endCount);
					$isPhp = false;
					$postLines[] = $line;
				}
				else
				{
					$phpLines[] = $line;
				}
			}
			else
			{
				$postLines[] = $line;
			}
		}
		if (1 !== $startCount)
		{
			$this->error = "\"$realPath\" - Expected 1 PHP start but found $startCount";
		}
		if (1 !== $endCount)
		{
			$this->error = "\"$realPath\" - Expected 1 PHP end but found $endCount";
		}
		return array($preLines, $phpLines, $postLines);
	}
	
	// Gets the real path of the file by the given name.
	// Note that it locates it by first looking relative to the current directory,
	//  then relative to the directory containing $includingFilePath,
	//  and then looks relative to each directory in $includePathArray.
	// Returns the string for the path, on success, or null if it couldn't be found.
	private function _getRealPath($fileName)
	{
		$realPath = realpath($fileName);
		if (FALSE === $realPath)
		{
			$realPath = realpath($this->startingDirectory . DIRECTORY_SEPARATOR . $fileName);
			if (FALSE === $realPath)
			{
				foreach($this->includePathArray as $path)
				{
					$realPath = realpath($path . DIRECTORY_SEPARATOR . $fileName);
					if (FALSE !== $realPath)
					{
						break;
					}
				}
			}
		}
		return (FALSE !== $realPath) ? $realPath : null;
	}
}

?>
