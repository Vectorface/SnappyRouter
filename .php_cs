<?php
$rules = [
    '@PSR2' => true,
    // additional rules
    'array_syntax' => ['syntax' => 'short'],
	'binary_operator_spaces' => [
	    'default' => 'single_space',
	    'operators' => [
	        '=>' => 'align_single_space_minimal',
	    ],
	],
	'cast_spaces' => false,
	'combine_consecutive_issets' => true,
	'function_declaration' => ['closure_function_spacing' => 'none'],
	'function_typehint_space' => true,
	'hash_to_slash_comment' => true,
	'include' => true,
	'method_chaining_indentation' => true,
	'no_blank_lines_after_class_opening' => true,
	'no_closing_tag' => true,
	'no_empty_statement' => true,
    'no_multiline_whitespace_before_semicolons' => true,
    'no_short_echo_tag' => true,
	'no_trailing_whitespace' => true,
	'no_trailing_whitespace_in_comment' => true,
	'no_unneeded_control_parentheses' => ['return'],
	'no_useless_return' => true,
	'no_whitespace_before_comma_in_array' => true,
	'no_whitespace_in_blank_line' => true,
    'not_operator_with_successor_space' => false,
	'semicolon_after_instruction' => true,
	'standardize_not_equals' => true,
	'ternary_operator_spaces' => true,
	'ternary_to_null_coalescing' => true,
	'trim_array_spaces' => true,
	'whitespace_after_comma_in_array' => true,
];
$excludes = [
    'vendor',
    'node_modules',
];
return PhpCsFixer\Config::create()
    ->setRules($rules)
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->exclude($excludes)
			->notName('*.js')
			->notName('*.css')
            ->notName('*.md')
            ->notName('*.xml')
            ->notName('*.yml')
            ->notName('*.tpl.php')
    );
