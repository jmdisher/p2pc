%start	P_INPUT

/* Keywords. */
%token	WHILE
%token	BREAK
%token	FOR
%token	FOREACH
%token	IF
%token	ELSE
%token	SWITCH
%token	CASE
%token	DEFAULT
%token	TRUE
%token	FALSE
%token	NULL
%token	PRIVATE
%token	PROTECTED
%token	PUBLIC
%token	STATIC
%token	FUNCTION
%token	CLASS
%token	ABSTRACT
%token	INTERFACE
%token	AS
%token	CONST
%token	RETURN
%token	ARRAY
%token	LIST
%token	NEW
%token	EXTENDS
%token	IMPLEMENTS
%token	ECHO
%token	INSTANCEOF
%token	GLOBAL
%token	PARENT
		
/* Structure. */
%token	NEW_LINE
%token	OPEN_BRACE
%token	CLOSE_BRACE
%token	OPEN_PAREN
%token	CLOSE_PAREN
%token	OPEN_SQUARE
%token	CLOSE_SQUARE
%token	SEMI_COLON
%token	COLON_COLON
%token	COMMA
%token	CALL
		
/* Operators. */
%token	BIT_OR
%token	LOGIC_OR
%token	OR_EQUAL
%token	BIT_AND
%token	LOGIC_AND
%token	AND_EQUAL
%token	XOR
%token	NOT
%token	SLASH
%token	MINUS
%token	MINUS_MINUS
%token	STAR
%token	STAR_EQUAL
%token	PLUS
%token	PLUS_PLUS
%token	PLUS_EQUAL
%token	MINUS_EQUAL
%token	DOT
%token	DOT_EQUAL
%token	PERCENT
		
/* Comparators. */
%token	EQUAL
%token	EQUAL_EQUAL
%token	NOT_EQUAL
%token	EQUAL_EQUAL_EQUAL
%token	NOT_EQUAL_EQUAL
%token	GREATER
%token	EQUAL_GREATER
%token	GREATER_EQUAL
%token	LESS
%token	EQUAL_LESS
%token	LESS_EQUAL
%token	QUESTION
%token	COLON
	
/* Regex. */
%token	IDENTIFIER
%token	SILENT_IDENTIFIER
%token	WHITE_SPACE
%token	FLOAT_CONST
%token	INT_CONST
%token	VARIABLE
%token	SINGLE_QUOTE_STRING
%token	DOUBLE_QUOTE_STRING
	
/* Comments. */
%token	SINGLE_COMMENT
%token	MULTI_COMMENT
%token	SPECIAL_COMMENT
%token	EXPORT_COMMENT

%%

P_INPUT:	P_GLOBALLINES
		;

P_GLOBALLINES:	P_GLOBALLINE P_GLOBALLINES
		| 
		;

P_LINES:	
		|	P_LINE P_LINES
		;

P_GLOBALLINE:	P_LINE
		| P_FUNCTION_DECL
		| P_CLASS_DECL
		| P_INTERFACE_DECL
		| P_ABSTRACT_CLASS_DECL
		| SPECIAL_COMMENT
		;

P_LINE:		P_STATEMENT SEMI_COLON
		| P_SUB_BLOCK
		;

P_STATEMENT:		P_ASSIGNMENT
		|	P_CALL
		|	P_NEW
		|	P_ECHO
		|	P_RETURN
		|	BREAK
		|	GLOBAL VARIABLE
		;

P_SUB_BLOCK:	P_IF_BLOCK
		|		P_LOOP_BLOCK
		|		P_SWITCH_BLOCK
		;

P_SWITCH_BLOCK:	SWITCH OPEN_PAREN P_RVALUE CLOSE_PAREN OPEN_BRACE P_SWITCH_LINES CLOSE_BRACE
		;

P_SWITCH_LINES:
		|	P_SWITCH_LINE P_SWITCH_LINES
		;

P_SWITCH_LINE:	P_LINE
		|	CASE P_RVALUE COLON
		|	DEFAULT COLON
		;

P_LOOP_BLOCK:	P_FOR_LOOP
		|		P_FOREACH_LOOP
		|		P_WHILE_LOOP
		;

P_FOR_LOOP:		FOR OPEN_PAREN P_FOR_INIT SEMI_COLON P_RVALUE SEMI_COLON P_FOR_INCREMENT CLOSE_PAREN P_BLOCK
		;

P_FOR_INIT:	P_ASSIGNMENT
		;

P_FOR_INCREMENT:	P_STATEMENT
		;

P_FOREACH_LOOP:	FOREACH OPEN_PAREN P_RVALUE AS P_FOREACH_PAIR CLOSE_PAREN P_BLOCK
		;

P_FOREACH_PAIR:	VARIABLE
		|		VARIABLE EQUAL_GREATER VARIABLE
		;

P_WHILE_LOOP:	WHILE P_CLOSED_CONDITION P_BLOCK
		;

P_IF_BLOCK:		IF P_CLOSED_CONDITION P_BLOCK P_ELSE_SIDE
		;

P_ELSE_SIDE:	
		|	ELSE IF P_CLOSED_CONDITION P_BLOCK P_ELSE_SIDE
		|	ELSE P_BLOCK
		;

P_CLOSED_CONDITION:	OPEN_PAREN P_RVALUE CLOSE_PAREN
		;

P_RETURN:	RETURN P_RVALUE
		;

P_CALL:		P_GLOBAL_CALL
		|	P_STATIC_CALL
		|	P_VIRTUAL_CALL
		|	P_PARENT_CALL
		|	P_EXPORT_COMMENT
		;

P_GLOBAL_CALL:		P_IDENTIFIER P_RARG_BRACKETS
		;

P_IDENTIFIER:	IDENTIFIER
		|	SILENT_IDENTIFIER
		;

P_STATIC_CALL:		IDENTIFIER COLON_COLON IDENTIFIER P_RARG_BRACKETS
		;

P_VIRTUAL_CALL:		P_RVALUE CALL IDENTIFIER P_RARG_BRACKETS
		;

P_PARENT_CALL:		PARENT COLON_COLON IDENTIFIER P_RARG_BRACKETS
		;

P_RARG_BRACKETS:	OPEN_PAREN P_RARGLIST CLOSE_PAREN
		;

/* WRONG - why? */
P_RARGLIST:		
		|	P_RVALUE
		|	P_RVALUE COMMA P_RARGLIST
		;

P_ECHO:		ECHO P_RVALUE
		;

P_ASSIGNMENT:		P_LVALUE P_ASSIGNMENT_OPERATOR P_RVALUE
		|	PLUS_PLUS P_LVALUE
		|	MINUS_MINUS P_LVALUE
		;

P_ASSIGNMENT_OPERATOR:	EQUAL
		| PLUS_EQUAL
		| MINUS_EQUAL
		| STAR_EQUAL
		| DOT_EQUAL
		| AND_EQUAL
		| OR_EQUAL
		;

P_LIST:		LIST OPEN_PAREN P_LARGLIST CLOSE_PAREN
		;

/* WRONG - why? */
P_LARGLIST:		
		|	P_LVALUE
		|	P_LVALUE COMMA P_LARGLIST
		;

P_FUNCTION_DECL:	FUNCTION IDENTIFIER P_RARG_BRACKETS P_BLOCK
		;

P_CLASS_DECL:	CLASS IDENTIFIER P_EXTENSION P_IMPLEMENTS OPEN_BRACE P_CLASS_LINES CLOSE_BRACE
		;

P_INTERFACE_DECL:	INTERFACE IDENTIFIER P_EXTENSION OPEN_BRACE P_INTERFACE_LINES CLOSE_BRACE
		;

P_INTERFACE_LINES:	
		|	P_INTERFACE_LINE P_INTERFACE_LINES
		;

P_INTERFACE_LINE:	P_PERMISSION FUNCTION IDENTIFIER P_RARG_BRACKETS SEMI_COLON
		;

P_ABSTRACT_CLASS_DECL:	ABSTRACT CLASS IDENTIFIER P_EXTENSION P_IMPLEMENTS OPEN_BRACE P_CLASS_LINES CLOSE_BRACE
		;

P_EXPORT_COMMENT:	EXPORT_COMMENT IDENTIFIER
		|			EXPORT_COMMENT IDENTIFIER COLON_COLON IDENTIFIER
		;

P_EXTENSION:
		| EXTENDS IDENTIFIER
		;

P_IMPLEMENTS:
		| IMPLEMENTS IDENTIFIER P_INTERFACE_EXT
		;

P_INTERFACE_EXT:
		|	COMMA IDENTIFIER P_INTERFACE_EXT
		;

P_CLASS_LINES:	P_CLASS_LINE P_CLASS_LINES
		| 
		;

P_CLASS_LINE:		P_STATICVAR SEMI_COLON
		|	P_INSTVAR SEMI_COLON
		|	P_CONST_DECL SEMI_COLON
		|	P_STATIC_FUNCTION_DECL
		|	P_INST_FUNCTION_DECL
		|	P_ABSTRACT_FUNCTION_DECL
		;

P_CONST_DECL:	CONST IDENTIFIER EQUAL P_RO_VAL
		;

P_STATICVAR:	P_PERMISSION STATIC VARIABLE P_STATIC_VAR_INIT
		;

P_STATIC_VAR_INIT:	
		|	EQUAL P_RVALUE
		;

P_INSTVAR:	P_PERMISSION VARIABLE
		;

P_STATIC_FUNCTION_DECL:	P_PERMISSION STATIC P_FUNCTION_DECL
		;

P_INST_FUNCTION_DECL:		P_PERMISSION P_FUNCTION_DECL
		;

P_ABSTRACT_FUNCTION_DECL:		P_PERMISSION ABSTRACT FUNCTION IDENTIFIER P_RARG_BRACKETS SEMI_COLON
		;

P_PERMISSION:	PRIVATE
		| PROTECTED
		| PUBLIC
		;

P_BLOCK:	OPEN_BRACE P_LINES CLOSE_BRACE
		;


/* VALUE TYPES */

/* R-VALUES */

/* Note that an identifier needs to count as an R-value since there are lots of them and some are provided via define calls */
P_RVALUE:	P_LVALUE
		|	P_EXPRESSION
		|	IDENTIFIER
		;

P_EXPRESSION:	P_CALL
		|	P_NEW
		|	P_ARRAY
		|	OPEN_PAREN P_RVALUE CLOSE_PAREN
		|	P_RO_VAL
		|	P_RVALUE P_OPERATOR P_RVALUE
 		|	P_UNARY P_RVALUE
		|	P_TERNARY
		|	P_RVALUE INSTANCEOF IDENTIFIER
		|	P_CAST P_RVALUE
		|	OPEN_PAREN P_ASSIGNMENT CLOSE_PAREN
		;

P_CAST:	OPEN_PAREN IDENTIFIER CLOSE_PAREN
		;

P_TERNARY:	P_RVALUE QUESTION P_RVALUE COLON P_RVALUE
		;

P_RO_VAL:	P_STRING
		|	FLOAT_CONST
		|	INT_CONST
		|	P_KEYWORD_CONSTANT
		|	P_CONST_REF
		;

P_STRING:	SINGLE_QUOTE_STRING
		|	DOUBLE_QUOTE_STRING
		;

P_KEYWORD_CONSTANT:	TRUE
		| FALSE
		| NULL
		;

P_CONST_REF:	IDENTIFIER COLON_COLON IDENTIFIER
		;

P_ARRAY:	ARRAY OPEN_PAREN P_ARRAY_CONTENTS CLOSE_PAREN
		;

P_ARRAY_CONTENTS:	
		| P_LINEAR_ARRAY
		| P_MAP_ARRAY
		;

P_LINEAR_ARRAY:	P_RVALUE P_LINEAR_ARRAY_CONT
		;

P_LINEAR_ARRAY_CONT:	
		| COMMA
		| COMMA P_LINEAR_ARRAY
		;

P_MAP_ARRAY:	P_RVALUE EQUAL_GREATER P_RVALUE P_MAP_ARRAY_CONT
		;

P_MAP_ARRAY_CONT:	
		| COMMA
		| COMMA P_MAP_ARRAY
		;

P_NEW:		NEW IDENTIFIER P_RARG_BRACKETS
		;


/* L-VALUES */

P_LVALUE:	P_LINDEXED_VARIABLE
		|	P_SCALAR_VARIABLE
		|	P_LIST
		;

P_LINDEXED_VARIABLE:	P_SCALAR_VARIABLE OPEN_SQUARE CLOSE_SQUARE
		;

P_SCALAR_VARIABLE:	VARIABLE
		|	P_INST_VARIABLE_REF
		|	P_STATIC_VARIABLE_REF
		|	P_SCALAR_VARIABLE OPEN_SQUARE P_RVALUE CLOSE_SQUARE
		;
		
P_INST_VARIABLE_REF:	P_RVALUE CALL IDENTIFIER
		|	P_RVALUE CALL OPEN_BRACE INT_CONST CLOSE_BRACE
		;

P_STATIC_VARIABLE_REF:	IDENTIFIER COLON_COLON VARIABLE
		;


/* OPERATORS */

P_OPERATOR:	EQUAL_EQUAL_EQUAL
		|	NOT_EQUAL_EQUAL
		|	GREATER
		|	GREATER_EQUAL
		|	LESS
		|	LESS_EQUAL
		|	LOGIC_AND
		|	LOGIC_OR
		|	PLUS
		|	MINUS
		|	DOT
		|	BIT_OR
		|	STAR
		|	SLASH
		|	XOR
		|	PERCENT
		;

P_UNARY:	NOT
		|	MINUS
		;
	
%%

