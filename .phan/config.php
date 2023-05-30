<?php

/**
 * This configuration will be read and overlaid on top of the
 * default configuration. Command line arguments will be applied
 * after this file is read.
 *
 * @see src/Phan/Config.php
 * See Config for all configurable options.
 *
 * A Note About Paths
 * ==================
 *
 * Files referenced from this file should be defined as
 *
 * ```
 *   Config::projectPath('relative_path/to/file')
 * ```
 *
 * where the relative path is relative to the root of the
 * project which is defined as either the working directory
 * of the phan executable or a path passed in via the CLI
 * '-d' flag.
 */

use Phan\Config;

return [
	// "target_php_version" => "8.2",
	"minimum_target_php_version" => "8.1",
	// turn color on (-C)
	"color_issue_messages_if_supported" => true,
	// If true, missing properties will be created when
	// they are first seen. If false, we'll report an
	// error message.
	"allow_missing_properties" => false,

	// Allow null to be cast as any type and for any
	// type to be cast to null.
	"null_casts_as_any_type" => false,

	// Backwards Compatibility Checking
	'backward_compatibility_checks' => false,

	// Run a quick version of checks that takes less
	// time
	"quick_mode" => false,

	// Only emit critical issues to start with
	// (0 is low severity, 5 is normal severity, 10 is critical)
	"minimum_severity" => 0,

	// enable for dead code check
	// this will spill out errors for all methods never called
	// use after all is OK to try to find unused code blocks
	// ignore recommended: PhanUnreferencedPublicMethod
	// "dead_code_detection" => true,

	// default false for include path check
	"enable_include_path_checks" => true,
	"include_paths" => [
		'.', '../configs/'
	],
	'ignore_undeclared_variables_in_global_scope' => true,

	"file_list" => [
		"./www/configs/config.db.php",
		"./www/configs/config.host.php",
		"./www/configs/config.path.php",
		"./www/configs/config.other.php",
		"./www/configs/config.master.php",
		"./www/includes/admin_header.php",
	],

	// A list of directories that should be parsed for class and
	// method information. After excluding the directories
	// defined in exclude_analysis_directory_list, the remaining
	// files will be statically analyzed for errors.
	//
	// Thus, both first-party and third-party code being used by
	// your application should be included in this list.
	'directory_list' => [
		// Change this to include the folders you wish to analyze
		// (and the folders of their dependencies)
		'www',
		// To speed up analysis, we recommend going back later and
		// limiting this to only the vendor/ subdirectories your
		// project depends on.
		// `phan --init` will generate a list of folders for you
		//'www/vendor',
	],


	// A list of directories holding code that we want
	// to parse, but not analyze
	"exclude_analysis_directory_list" => [
		'www/vendor',
		'www/tests',
		'www/templates_c',
		'www/log',
		'www/tmp',
		'www/cache',
		'www/media',
	],
	'exclude_file_list' => [
		// ignore all symlink files to edit
		'www/admin/edit_access.php',
		'www/admin/edit_groups.php',
		'www/admin/edit_languages.php',
		'www/admin/edit_menu_group.php',
		'www/admin/edit_order.php',
		'www/admin/edit_pages.php',
		'www/admin/edit_schemes.php',
		'www/admin/edit_users.php',
		'www/admin/edit_visible_group.php',
		// ignore the old qq tests
		'www/admin/qq_file_upload_front.php',
		'www/admin/qq_file_upload_ajax.php',
	],

	// what not to show as problem
	'suppress_issue_types' => [
		// 'PhanUndeclaredMethod',
		'PhanEmptyFile',
		// ignore unreferences public methods, etc here (for dead code check)
		'PhanUnreferencedPublicMethod',
		'PhanUnreferencedClass',
		'PhanWriteOnlyPublicProperty',
		'PhanUnreferencedConstant',
		'PhanWriteOnlyPublicProperty',
		'PhanReadOnlyPublicProperty',
		// start ignore annotations
		'PhanUnextractableAnnotationElementName',
		'PhanUnextractableAnnotationSuffix'
	],

	// Override to hardcode existence and types of (non-builtin) globals in the global scope.
	// Class names should be prefixed with `\`.
	//
	// (E.g. `['_FOO' => '\FooClass', 'page' => '\PageClass', 'userId' => 'int']`)
	'globals_type_map' => [],
];
