<?php
// The StaticCall support.
require_once('Constants.php');


class StaticCall
{
	public static function call($number)
	{
		assert(is_int($number));
		echo Constants::kPrefix . ' ' . $number . "\n";
	}
	
	public static function notCalled($ignored)
	{
		// This function isn't called so we shouldn't see it when dead code elimination is engaged.
		return StaticCall::_unreachable($ignored);
	}
	
	
	private static function _unreachable($ignored)
	{
		// The only caller of this function is not called so this will also be deleted when dead code elimination is
		//  engaged.
		return $ignored;
	}
}
?>
