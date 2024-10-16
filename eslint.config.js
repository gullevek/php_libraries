// // import globals from "globals";
// import js from '@eslint/js';

module.exports = {
	// 'env': {
	// 	'browser': true,
	// 	'es6': true,
	// 	'commonjs': true,
	// 	'jquery': true
	// },
	// 'extends': 'eslint:recommended',
	// 'parserOptions': {
	// 	'ecmaVersion': 11
	// },
	// ...eslint.configs.recommended,
	'rules': {
		'indent': [
			'error',
			'tab', {
				'SwitchCase': 1
			}
		],
		'linebreak-style': [
			'error',
			'unix'
		],
		'quotes': [
			'error',
			'single',
			{ 'avoidEscape': true, 'allowTemplateLiterals': true }
		],
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
};

// export default [
// 	...js.configs.recommended,
// 	{
// 		files: ['**/*.js'],
// 		'rules': {
// 			'indent': [
// 				'error',
// 				'tab', {
// 					'SwitchCase': 1
// 				}
// 			],
// 			'linebreak-style': [
// 				'error',
// 				'unix'
// 			],
// 			'quotes': [
// 				'error',
// 				'single',
// 				{ 'avoidEscape': true, 'allowTemplateLiterals': true }
// 			],
// 			'semi': [
// 				'error',
// 				'always'
// 			],
// 			'no-console': 'off',
// 			'no-unused-vars': [
// 				'error', {
// 					'vars': 'all',
// 					'args': 'after-used',
// 					'ignoreRestSiblings': false
// 				}
// 			],
// 			// Requires eslint >= v8.14.0
// 			'no-constant-binary-expression': 'error'
// 		},
// 		languageOptions: {
// 			globals: {
// 				...globals.browser,
// 				...globals.commonjs,
// 				...globals.jquery,
// 				// myCustomGlobal: "readonly"
// 				'ecmaVersion': 11
// 			}
// 		}
// 	}
// ];

// import globals from "globals";
// import js from '@eslint/js';

// export default [
// 	js.configs.recommended,
// 	{
// 		files: ['**/*.js'],
// 		rules: {
// 			'indent': [
// 				'error',
// 				'tab', {
// 					'SwitchCase': 1
// 				}
// 			],
// 			'linebreak-style': [
// 				'error',
// 				'unix'
// 			],
// 			'quotes': [
// 				'error',
// 				'single',
// 				{ 'avoidEscape': true, 'allowTemplateLiterals': true }
// 			],
// 			'semi': [
// 				'error',
// 				'always'
// 			],
// 			'no-console': 'off',
// 			'no-unused-vars': [
// 				'error', {
// 					'vars': 'all',
// 					'args': 'after-used',
// 					'ignoreRestSiblings': false
// 				}
// 			],
// 			// Requires eslint >= v8.14.0
// 			'no-constant-binary-expression': 'error'
// 		}/* ,
// 		languageOptions: {
// 			ecmaVersion: 11,
// 			// globals: {
//             //     ...globals.browser,
// 			// },
// 		} */
// 	}
// ];
