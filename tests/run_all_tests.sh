#!/bin/bash

DIRECTORY=`readlink -e "../build"`
P2PC_PATH="$DIRECTORY/p2pc.php"
GRAMMAR_PATH="$DIRECTORY/grammar.xml"

if ! [[ ( -x "$P2PC_PATH" ) && ( -f "$GRAMMAR_PATH" ) ]]; then
	echo "Missing executable p2pc.php or readable grammar.xml from \"$DIRECTORY\""
	exit 1
fi

files=`ls | grep test_`
for f in $files
do
	echo -e "\e[1;36mTesting:  $f\e[m"
	cd $f
	./run_test.sh "$DIRECTORY"
	cd ../
done

