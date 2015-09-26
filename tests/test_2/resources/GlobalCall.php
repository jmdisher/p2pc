<?php
// The GlobalCall support.
require_once('Constants.php');


function globalCall($number)
{
	assert(is_int($number));
	echo Constants::kPrefix . ' ' . $number . "\n";
}

function hiddenCall($number)
{
	assert(is_int($number));
	return Constants::kPrefix . ' ' . $number . "\n";
}
?>
