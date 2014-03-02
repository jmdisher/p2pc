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
require_once('CallNewWalker.php');
require_once('CallGlobalWalker.php');
require_once('CallStaticWalker.php');
require_once('CallVirtualWalker.php');
require_once('CallParentWalker.php');


// Author:  Jeff Disher (Open Autonomy Inc.)
// Common helpers for processing the contents of a block to find its calls.
class OA_CodeBlockHelpers
{
	const kCallNew = 'P_NEW';
	const kCallGlobal = 'P_GLOBAL_CALL';
	const kCallStatic = 'P_STATIC_CALL';
	const kCallVirtual = 'P_VIRTUAL_CALL';
	const kCallParent = 'P_PARENT_CALL';
	
	
	public static function findCallObject($callingContext, $tree, $treeName)
	{
		$callObject = null;
		switch($treeName)
		{
			case OA_CodeBlockHelpers::kCallNew:
				// Note that we still need to call children to see the argument lists.
				$childWalker = new OA_CallNewWalker();
				$tree->visit($childWalker);
				$callObject = $childWalker->getFunctionCallObject();
			break;
			case OA_CodeBlockHelpers::kCallGlobal:
				// Note that we still need to call children to see the argument lists.
				$childWalker = new OA_CallGlobalWalker();
				$tree->visit($childWalker);
				$callObject = $childWalker->getFunctionCallObject();
			break;
			case OA_CodeBlockHelpers::kCallStatic:
				// Note that we still need to call children to see the argument lists.
				$childWalker = new OA_CallStaticWalker();
				$tree->visit($childWalker);
				$callObject = $childWalker->getFunctionCallObject();
			break;
			case OA_CodeBlockHelpers::kCallVirtual:
				// Note that we still need to call children to see the argument lists.
				$childWalker = new OA_CallVirtualWalker();
				$tree->visit($childWalker);
				$callObject = $childWalker->getFunctionCallObject();
			break;
			case OA_CodeBlockHelpers::kCallParent:
				// Note that we still need to call children to see the argument lists.
				assert($callingContext instanceof OA_CallingContext);
				$childWalker = new OA_CallParentWalker($callingContext);
				$tree->visit($childWalker);
				$callObject = $childWalker->getFunctionCallObject();
			break;
			default:
				// Do nothing.
		}
		return $callObject;
	}
}

?>
