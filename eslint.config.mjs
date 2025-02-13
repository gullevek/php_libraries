import globals from 'globals';
import pluginJs from '@eslint/js';

/*
module.exports = {
	'env': {
		'browser': true,
		'es6': true,
		'commonjs': true,
		'jquery': true
	},
	'extends': 'eslint:recommended',
	'parserOptions': {
		'ecmaVersion': 6
	},
	// rules copied
};
*/

/** @type {import('eslint').Linter.Config[]} */
export default [
	{languageOptions: { globals: globals.browser }},
	pluginJs.configs.recommended,
	{
		'rules': {
			'indent': [
				'error',
				'tab',
				{
					'SwitchCase': 1
				}
			],
			'linebreak-style': [
				'error',
				'unix'
			],
			// 'quotes': [
			// 	'error',
			// 	'single'
			// ],
			'semi': [
				'error',
				'always'
			],
			'no-console': 'off',
			'no-unused-vars': [
				'error', {
					'vars': 'all',
					'args': 'after-used',
					'ignoreRestSiblings': false
				}
			],
			// Requires eslint >= v8.14.0
			'no-constant-binary-expression': 'error'
		}
	}
];

// __END__
