#!/bin/bash
SRC_DIRECTORY="../src"
COMPILER="$SRC_DIRECTORY/p2pc.php"
ARGS="-I $SRC_DIRECTORY -I $SRC_DIRECTORY/preprocessor -I $SRC_DIRECTORY/lexer -I $SRC_DIRECTORY/parser -I $SRC_DIRECTORY/symbol_table"
GRAMMAR="$SRC_DIRECTORY/parser/grammar.xml"
SYMBOL_TABLE=symbol_table.txt


compareCompilers()
{
	echo -e "\e[1;36mComparing compilers...\e[m"
	diff compiler0.php compiler1.php >& diff.out
	result=$?
	if [ $result -eq 0 ]; then
		echo -e "\e[1;32mPASS\e[m"
	else
		echo -e "\e[1;31mFAIL\e[m"
		cat diff.out
	fi
}

count()
{
	COUNT=`grep -c "$1" $SYMBOL_TABLE`
	if [ $COUNT -eq $2 ]; then
		echo -e "\e[1;32mFOUND $1 $COUNT times\e[m"
	else
		echo -e "\e[1;31mFOUND $1 $COUNT times (expected $2)\e[m"
	fi
}


# Get the compiler to compile itself and then use that version to compile itself again.  Both resultant files should be identical.
echo -e "\e[1;36mCompiling using raw source...\e[m"
time $COMPILER $ARGS -o compiler0.php "$COMPILER"

echo -e "\e[1;36mCompiling using compiled compiler...\e[m"
chmod +x compiler0.php
time ./compiler0.php $ARGS -o compiler1.php "$COMPILER"
compareCompilers

echo -e "\e[1;36mCompiling with dead code removal using raw source...\e[m"
time $COMPILER --parser "$GRAMMAR" --deadCode $ARGS -o compiler0.php "$COMPILER"

echo -e "\e[1;36mCompiling with dead code removal using compiled compiler...\e[m"
chmod +x compiler0.php
time ./compiler0.php --parser "$GRAMMAR" --deadCode $ARGS -o compiler1.php "$COMPILER"
compareCompilers

echo -e "\e[1;36mCompiling for symbol table output using compiled compiler...\e[m"
time ./compiler0.php --parser "$GRAMMAR" $ARGS --symbolTable $SYMBOL_TABLE -o compiler1.php "$COMPILER"
count "GLOBAL FUNCTION: parseCommandLine" 1
count "GLOBAL FUNCTION: notFound" 0
count "CLASS: OA_LexerMaps" 1
count "STATIC FUNCTION: OA_LexerMaps::lengthSort" 1

echo -e "\e[1;36mRenaming built compiler to p2pc.php for external consumers...\e[m"
mv compiler1.php p2pc.php
cp "$GRAMMAR" grammar.xml
chmod +x p2pc.php

echo -e "\e[1;36mCleaning up...\e[m"
rm -f compiler0.php diff.out symbol_table.txt

echo -e "\e[1;36mDone!\e[m"
