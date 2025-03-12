<?php

$finder = PhpCsFixer\Finder::create()
  ->in([
    __DIR__ . '/src',
    __DIR__ . '/tests',
  ])
  ->exclude([
    'vendor',
    'Infrastructure/Persistence/Migrations',
  ]);

$config = new PhpCsFixer\Config();
return $config
  ->setRules([
    '@PSR12' => true,
    'array_syntax' => ['syntax' => 'short'],
    'ordered_imports' => ['sort_algorithm' => 'alpha'],
    'no_unused_imports' => true,
    'not_operator_with_successor_space' => true,
    'trailing_comma_in_multiline' => true,
    'phpdoc_scalar' => true,
    'unary_operator_spaces' => true,
    'binary_operator_spaces' => true,
    'blank_line_before_statement' => [
      'statements' => ['break', 'continue', 'declare', 'return', 'throw', 'try'],
    ],
    'phpdoc_single_line_var_spacing' => true,
    'phpdoc_var_without_name' => true,
    'class_attributes_separation' => [
      'elements' => [
        'method' => 'one',
        'property' => 'one',
        'trait_import' => 'none',
        'const' => 'one',
      ],
    ],
    'method_argument_space' => [
      'on_multiline' => 'ensure_fully_multiline',
      'keep_multiple_spaces_after_comma' => true,
    ],
    'single_trait_insert_per_statement' => true,
    'no_superfluous_phpdoc_tags' => [
      'allow_mixed' => true,
      'allow_unused_params' => true,
    ],
    'declare_strict_types' => true,
    'void_return' => true,
    'fully_qualified_strict_types' => true,
    'global_namespace_import' => [
      'import_classes' => true,
      'import_constants' => true,
      'import_functions' => true,
    ],
    'no_leading_import_slash' => true,
    'no_trailing_comma_in_singleline' => true,
    'no_singleline_whitespace_before_semicolons' => true,
    'no_whitespace_in_blank_line' => true,
    'single_blank_line_at_eof' => true,
    'single_class_element_per_statement' => true,
    'single_import_per_statement' => true,
    'single_line_after_imports' => true,
    'strict_comparison' => true,
    'strict_param' => true,
    'no_trailing_whitespace' => true,
    'no_trailing_whitespace_in_comment' => true,
    'clean_namespace' => true,
    'no_useless_else' => true,
    'no_useless_return' => true,
    'combine_consecutive_issets' => true,
    'combine_consecutive_unsets' => true,
    'explicit_string_variable' => true,
    'multiline_whitespace_before_semicolons' => ['strategy' => 'no_multi_line'],
    'no_empty_statement' => true,
    'no_extra_blank_lines' => [
      'tokens' => [
        'extra',
        'throw',
        'use',
        'use_trait',
      ],
    ],
    'no_spaces_around_offset' => true,
    'no_unneeded_control_parentheses' => true,
    'no_unneeded_curly_braces' => true,
    'no_whitespace_before_comma_in_array' => true,
    'normalize_index_brace' => true,
    'object_operator_without_whitespace' => true,
    'semicolon_after_instruction' => true,
    'space_after_semicolon' => true,
    'standardize_not_equals' => true,
    'trim_array_spaces' => true,
    'whitespace_after_comma_in_array' => true,
  ])
  ->setFinder($finder)
  ->setRiskyAllowed(true)
  ->setUsingCache(true);
