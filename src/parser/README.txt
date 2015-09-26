Using Bison output to build the p2pc parser

Step 1)  Run Bison to produce XML:
bison -x bison.y
-this will create bison.xml

Step 2)  Reduce the XML:
reduce_xml.php bison.xml
-this will create grammar.xml

At runtime, run p2pc with the path to this grammar.xml file as the --parser argument in order to run with the given grammar.
