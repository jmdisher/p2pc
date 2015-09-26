#!/usr/bin/php
<?php
// This is a very simple test which demonstrates how the compiler works.  It should produce the same output when compiled or not.
require_once('NoCompile.php');

echo "START\n";
require_once('a.php');
require_once('b.php');
require_once('c.php');
echo "END\n";

?>
