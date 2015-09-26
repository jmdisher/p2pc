<?php
// The VirtualCall support.
require_once('Constants.php');


class VirtualCall
{
	private $number;
	
	public function __construct($number)
	{
		assert(is_int($number));
		$this->number = $number;
	}
	
	public function output()
	{
		echo Constants::kPrefix . ' ' . $this->number . "\n";
	}
}
?>
