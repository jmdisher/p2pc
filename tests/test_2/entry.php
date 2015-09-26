#!/usr/bin/php
<?php
// The entry-point to the p2pc example used on the wiki (also included in the tests).
set_include_path(get_include_path() . PATH_SEPARATOR . 'externals');
require_once('NoCompile.php');
require_once('GlobalCall.php');
require_once('StaticCall.php');
require_once('VirtualCall.php');


echo "Starting...\n";
for ($i = 0; $i < 3; ++$i)
{
	globalCall($i);
}
for ($i = 3; $i < 6; ++$i)
{
	StaticCall::call($i);
}
for ($i = 6; $i < 9; ++$i)
{
	$virt = new VirtualCall($i);
	$virt->output();
}
for ($i = 9; $i < 12; ++$i)
{
	//EXPORT hiddenCall;
	$result = call_user_func('hiddenCall', $i);
	echo $result;
}
echo "Done!\n";
?>
