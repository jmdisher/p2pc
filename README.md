# p2pc
PHP-to-PHP Compiler

A simple compiler, written in PHP, which compiles from PHP and into PHP.

## How to build:
```
cd build
./self_compile.sh
# This will produce p2pc.php and grammar.xml.
```

## How to test:
```
# This requires that you previously built the compiler.
cd tests
./run_all_tests.sh
# This will run a few basic tests on the built compiler.  If there is no red text, produced by any of the tests, it is working.
```

## Usage:
```
Usage:  p2pc.php [-I include/path]* [-g ignored_file]* [-o output_file] [--preprocess] [--strip] [--parser <grammar.xml>] [--symbolTable <symbol_table_output>] [--testLexer] input_file
```
The tests can show how these are applied but the general case is something along the lines of:
```
# Note that --deadCode is not the default as it requires annotating source to ensure that all entry-points can be determined by the compiler.
build/p2pc.php --parser build/grammar.xml -o output.php input.php
```

## Why?
Much of the time in a PHP script is found looking up and loading files (especially true with a code cache enabled).  p2pc was originally designed to statically inline these `require_once` dependencies to eliminate this cost and also ease deployment (since there is much less worrying about include path).

Over time, it evolved to also shrink the resulting files through removing comments and performing other forms of minification.

Later, it also evolved to shrink the per-script code size through removing dead code.

## The Future
Currently, there is no formal abstract syntax tree produced between the parse tree and the optimizer, meaning that the optimizer runs directly on the parse tree.  This is a very brittle approach since changes to the structure of the grammar would break optimizaters.  This also adds complexity to the optimizer since it needs to be sensitive to the parse tree structure as opposed to more abstract view of the program created by an abstract syntax tree.  Hence, the next step is to create one in order to break this tight coupling and enable more aggressive optimizations and a broader grammar, in the future.
