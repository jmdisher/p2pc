#!/bin/bash
DIRECTORY="$1"
COMPILER="$DIRECTORY/p2pc.php"
GRAMMAR="$DIRECTORY/grammar.xml"
OUTPUT=output.php

count()
{
	COUNT=`grep -c "$1" $OUTPUT`
	if [ $COUNT -eq $2 ]; then
		echo -e "\e[1;32mFOUND $1 $COUNT times\e[m"
	else
		echo -e "\e[1;31mFOUND $1 $COUNT times (expected $2)\e[m"
	fi
}


# Get the compiler to compile itself and then use that version to compile itself again.  Both resultant files should be identical.
rm -f "$OUTPUT"
echo -e "\e[1;36mCompiling deadTest.php for dead code elimination test...\e[m"
$COMPILER --parser "$GRAMMAR" --deadCode -o "$OUTPUT" deadTest.php
count "public static function alive" 1
count "public function instanceAlive" 1
count "function globalAlive" 1
count "function directAlive" 1
count "ABSTRACT ALIVE" 1
count "INTERFACE ALIVE" 1
count "Superclass constructor" 1
count "public static function dead" 0
count "public function instanceDead" 0
count "function dead" 0

# Clean up.
rm -f "$OUTPUT"
