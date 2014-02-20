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
require_once('Parser.php');
require_once('ParserRule.php');
require_once('ParserState.php');


// Author:  Jeff Disher (Open Autonomy Inc.)
// The utility class which builds a new instance of the OA_Parser from a Bison-generated XML description.
class OA_ParserBuilder
{
	const kTransitionShift = 'shift';
	const kTransitionGoto = 'goto';
	
	// Build the parser from the Bison XML in $path.
	public static function buildFromXmlFile($path)
	{
		$parserObject = new OA_Parser();
		
		$data = simplexml_load_file($path);
		$grammarNode = $data->grammar;
		$rulesNode = $grammarNode->rules;
		foreach ($rulesNode->children() as $node)
		{
			$lhs = (string)$node->lhs;
			$rhs = array();
			foreach ($node->rhs->symbol as $symbolNode)
			{
				$rhs[] = (string)$symbolNode;
			}
			$rule = new OA_ParserRule($lhs, $rhs);
			$number = (string)$node->attributes()->number;
			$parserObject->addRule($number, $rule);
		}
		
		// Note that $grammarNode->terminals and $grammarNode->nonterminals may be of use to verify that the pieces of
		//  the compiler are in sync.
		
		foreach ($data->automaton->state as $stateNode)
		{
			// Build the object before adding it.
			$stateObject = new OA_ParserState();
			
			foreach ($stateNode->actions->transitions->transition as $transitionNode)
			{
				$attributes = $transitionNode->attributes();
				$symbol = (string)$attributes->symbol;
				$state = (string)$attributes->state;
				switch ((string)$attributes->type)
				{
					case OA_ParserBuilder::kTransitionShift:
						$stateObject->addShift($symbol, $state);
					break;
					case OA_ParserBuilder::kTransitionGoto:
						$stateObject->addGoto($symbol, $state);
					break;
					default:
						assert(false);
				}
			}
			
			foreach ($stateNode->actions->reductions->reduction as $reductionNode)
			{
				$attributes = $reductionNode->attributes();
				$symbol = (string)$attributes->symbol;
				$rule = (string)$attributes->rule;
				$stateObject->addReduction($symbol, $rule);
			}
			$number = (string)$stateNode->attributes()->number;
			$parserObject->addState($number, $stateObject);
		}
		return $parserObject;
	}
}

?>
