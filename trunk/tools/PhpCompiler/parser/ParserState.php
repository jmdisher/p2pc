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


// Author:  Jeff Disher (Open Autonomy Inc.)
// The representation of a state within the parser which defines the rules for how to handle the next token.
// States contain the following information:
//  1) Shift transitions - which state to enter when shifting a token.
//  2) Reduce rules - which reduction rules to apply when a new token in seen.
//  3) Goto transitions - which state to enter when a reduction has left this state on the top of the stack.
class OA_ParserState
{
	private $shiftTransitions;
	private $gotoTransitions;
	private $reductions;
	
	
	// Creates an empty state.
	public function __construct()
	{
		$this->shiftTransitions = array();
		$this->gotoTransitions = array();
		$this->reductions = array();
	}
	
	// Adds a shift to transition to $state number on $symbol.
	public function addShift($symbol, $state)
	{
		$this->shiftTransitions[$symbol] = $state;
	}
	
	// Adds a goto to transition to $state number on $symbol.
	public function addGoto($symbol, $state)
	{
		$this->gotoTransitions[$symbol] = $state;
	}
	
	// Adds a reduction to use $rule number on $symbol.
	public function addReduction($symbol, $rule)
	{
		$this->reductions[$symbol] = $rule;
	}
	
	// Gets the shift state number for the given symbol or null, if there is no shift transition for that symbol.
	public function getShiftForSymbol($symbol)
	{
		$shiftState = null;
		if (isset($this->shiftTransitions[$symbol]))
		{
			$shiftState = $this->shiftTransitions[$symbol];
		}
		return $shiftState;
	}
	
	// Gets the goto state number for the given symbol or null, if there is no goto transition for that symbol.
	public function getGotoForSymbol($symbol)
	{
		$gotoState = null;
		if (isset($this->gotoTransitions[$symbol]))
		{
			$gotoState = $this->gotoTransitions[$symbol];
		}
		return $gotoState;
	}
	
	// Gets the reduction rule number for the given symbol or null, if there is no reduction for that symbol.
	public function getReductionForSymbol($symbol)
	{
		$reductionRule = null;
		if (isset($this->reductions[$symbol]))
		{
			$reductionRule = $this->reductions[$symbol];
		}
		else
		{
			// Search for "$default" reductions.
			if (isset($this->reductions['$default']))
			{
				$reductionRule = $this->reductions['$default'];
			}
		}
		return $reductionRule;
	}
}

?>
