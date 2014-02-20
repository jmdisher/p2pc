#!/usr/bin/php
<?php
// A simple tool which reduces the Bison output XML into the minimal XML subset we need for the compiler.
// The only reasons for this are to reduce source control waste, reduce parser start-up cost, reduce packaged size.

if (2 === $argc)
{
	$inputPath = $argv[1];
	$outputPath = dirname($argv[0]) . DIRECTORY_SEPARATOR . 'grammar.xml';
	echo "Loading XML from \"$inputPath\" saving to \"$outputPath\"...\n";
	
	$reducedRoot = new SimpleXMLElement('<p2pc-reduced-bison-xml />');
	$inputRoot = simplexml_load_file($inputPath);
	
	$reducedGrammarNode = $reducedRoot->addChild('grammar');
	$inputGrammarNode = $inputRoot->grammar;
	
	$reducedRulesNode = $reducedGrammarNode->addChild('rules');
	$inputRulesNode = $inputGrammarNode->rules;
	foreach ($inputRulesNode->children() as $node)
	{
		$reducedRuleNode = $reducedRulesNode->addChild('rule');
		
		$reducedRuleNode->addChild('lhs', (string)$node->lhs);
		$reducedRhsNode = $reducedRuleNode->addChild('rhs');
		foreach ($node->rhs->symbol as $symbolNode)
		{
			$reducedRhsNode->addChild('symbol', (string)$symbolNode);
		}
		$reducedRuleNode->addAttribute('number', (string)$node->attributes()->number);
	}
	
	// Note that $grammarNode->terminals and $grammarNode->nonterminals may be of use to verify that the pieces of
	//  the compiler are in sync.
	
	$reducedAutomatonNode = $reducedRoot->addChild('automaton');
	foreach ($inputRoot->automaton->state as $stateNode)
	{
		$reducedStateNode = $reducedAutomatonNode->addChild('state');
		$reducedActionsNode = $reducedStateNode->addChild('actions');
		
		$reducedTransitionsNode = $reducedActionsNode->addChild('transitions');
		foreach ($stateNode->actions->transitions->transition as $transitionNode)
		{
			$reducedTransitionNode = $reducedTransitionsNode->addChild('transition');
			$attributes = $transitionNode->attributes();
			$reducedTransitionNode->addAttribute('symbol', (string)$attributes->symbol);
			$reducedTransitionNode->addAttribute('state', (string)$attributes->state);
			$reducedTransitionNode->addAttribute('type', (string)$attributes->type);
		}
		
		$reducedReductionsNode = $reducedActionsNode->addChild('reductions');
		foreach ($stateNode->actions->reductions->reduction as $reductionNode)
		{
			$reducedReductionNode = $reducedReductionsNode->addChild('reduction');
			$attributes = $reductionNode->attributes();
			$reducedReductionNode->addAttribute('symbol', (string)$attributes->symbol);
			$reducedReductionNode->addAttribute('rule', (string)$attributes->rule);
		}
		$reducedStateNode->addAttribute('number', (string)$stateNode->attributes()->number);
	}
	$putResult = file_put_contents($outputPath, $reducedRoot->asXML());
	assert(FALSE !== $putResult);
	
}
else
{
	error_log("Usage:  reduce_xml.php <input_xml_file>");
}

?>
