#!/bin/bash
DIRECTORY="$1"
COMPILER="$DIRECTORY/p2pc.php"
GRAMMAR="$DIRECTORY/grammar.xml"


echo -e "\e[1;36mRunning vanilla output...\e[m"
./entry.php > OUTPUT0
RET=$?
if [[ 0 -ne $RET ]]; then
	echo "Failure to run vanilla test"
	exit 1
fi
COUNT=`grep Done OUTPUT0 | wc -l`
if [[ 1 -ne $COUNT ]]; then
	echo "Expected finish not found"
	exit 1
fi
echo -e "\e[1;36mDone!\e[m"

# Compile with preprocess only to make sure that it has the same behaviour and only one require_once (for Constants.php
#  in externals).
echo -e "\e[1;36mCompiling and running for --preprocess..\e[m"
$COMPILER -I resources -g NoCompile.php --preprocess -o out1.php entry.php
chmod +x out1.php
./out1.php > OUTPUT1
RET=$?
if [[ 0 -ne $RET ]]; then
	echo "Failure to run preprocess test"
	exit 1
fi
RESULT1=`cat OUTPUT1`
DIFF=`diff OUTPUT0 OUTPUT1`
if [[ "" != $DIFF ]]; then
	echo "Output mismatch in preprocess"
	exit 1
fi
COUNT=`grep require_once out1.php | wc -l`
if [[ 1 -ne $COUNT ]]; then
	echo "Expected require_once count not found in preprocess"
	exit 1
fi
echo -e "\e[1;36mDone!\e[m"

# Compile with strip only to make sure that it has the same behaviour and only one require_once (for Constants.php
#  in externals).
echo -e "\e[1;36mCompiling and running for --strip..\e[m"
$COMPILER -I resources -g NoCompile.php --strip -o out2.php entry.php
chmod +x out2.php
./out2.php > OUTPUT2
RET=$?
if [[ 0 -ne $RET ]]; then
	echo "Failure to run strip test"
	exit 1
fi
RESULT1=`cat OUTPUT2`
DIFF=`diff OUTPUT0 OUTPUT2`
if [[ "" != $DIFF ]]; then
	echo "Output mismatch in strip"
	exit 1
fi
COUNT=`grep require_once out2.php | wc -l`
if [[ 1 -ne $COUNT ]]; then
	echo "Expected require_once count not found in strip"
	exit 1
fi
echo -e "\e[1;36mDone!\e[m"

# Compile with deadCode only to make sure that it has the same behaviour and only one require_once (for Constants.php
#  in externals).
echo -e "\e[1;36mCompiling and running for --deadCode..\e[m"
$COMPILER -I resources -g NoCompile.php --parser $GRAMMAR --deadCode -o out3.php entry.php
chmod +x out3.php
./out3.php > OUTPUT3
RET=$?
if [[ 0 -ne $RET ]]; then
	echo "Failure to run deadCode test"
	exit 1
fi
RESULT1=`cat OUTPUT3`
DIFF=`diff OUTPUT0 OUTPUT3`
if [[ "" != $DIFF ]]; then
	echo "Output mismatch in deadCode"
	exit 1
fi
COUNT=`grep require_once out3.php | wc -l`
if [[ 1 -ne $COUNT ]]; then
	echo "Expected require_once count not found in deadCode"
	exit 1
fi
COUNT=`grep notCalled out3.php | wc -l`
if [[ 0 -ne $COUNT ]]; then
	echo "notCalled seen, even though it should have been deemed uncalled"
	exit 1
fi
COUNT=`grep _unreachable out3.php | wc -l`
if [[ 0 -ne $COUNT ]]; then
	echo "_unreachable seen, even though it should have been deemed unreachable"
	exit 1
fi

# Clean up.
rm -f out1.php out2.php out3.php OUTPUT0 OUTPUT1 OUTPUT2 OUTPUT3

echo -e "\e[1;36mDone!\e[m"
