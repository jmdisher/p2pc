#!/bin/bash
DIRECTORY="$1"
COMPILER="$DIRECTORY/p2pc.php"
GRAMMAR="$DIRECTORY/grammar.xml"


compileAndCompare()
{
	OPTION="$1"
	
	echo -e "\e[1;36mRunning \"$OPTION\"...\e[m"
	echo -e "\e[1;36mCompiling test $OPTION...\e[m"
	$COMPILER -I resources -g NoCompile.php $OPTION -o test_output.php test_input.php
	chmod +x test_output.php
	
	echo -e "\e[1;36mRunning before and after...\e[m"
	./test_input.php >& input.out
	./test_output.php >& output.out
	
	echo -e "\e[1;36mComparing output...\e[m"
	diff input.out output.out >& diff.out
	result=$?
	
	if [ $result -eq 0 ]; then
		echo -e "\e[1;32mPASS\e[m"
		echo -e "\e[1;36mCleaning up...\e[m"
		rm -f test_output.php input.out output.out diff.out
	else
		echo -e "\e[1;31mFAIL\e[m"
		cat diff.out
	fi
}

compileAndCompare "--preprocess"
compileAndCompare "--strip"
compileAndCompare "--parser $GRAMMAR --deadCode"
