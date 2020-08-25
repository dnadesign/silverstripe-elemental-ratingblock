module.exports = {
    parser: 'babel-eslint',
    extends: ['standard', 'standard-jsx', 'standard-react'],
    rules: {
        'indent': [4, 4, { 'SwitchCase': 1 }],
        'one-var': [2, { 'initialized': 'always' }],
        'import/prefer-default-export': 'off',
        'react/prefer-stateless-function': 'off',
        'react/jsx-filename-extension': 'off',
        'no-use-before-define': 'off',
        'class-methods-use-this': 'off',
        'react/jsx-closing-bracket-location': 'off',
        'react/jsx-closing-tag-location': 'off',
        'react/jsx-curly-spacing': 'off',
        'react/jsx-equals-spacing': 'off',
        'react/jsx-first-prop-new-line': 'off',
        'react/jsx-indent': [4, 4],
        'react/jsx-indent-props': 'off',
        'react/jsx-max-props-per-line': 'off',
        'react/jsx-tag-spacing': 'off',
        'react/jsx-wrap-multilines': 'off',
        'react/no-danger': 'off',
        'react/jsx-indent': 'off',
        'semi': [2, 'always'],
        'dot-notation': 'off',
        'quote-props': 'off',
        'indent': 'off',
        'space-before-function-paren': [0, 'never'],
        'import/no-webpack-loader-syntax': 'off',
        'react/prop-types': 'off'
    },
    parserOptions: {
        ecmaFeatures: {
            legacyDecorators: true
        }
    },
    globals: {
        'document': false,
        'navigator': false,
        'window': false,
        'fetch': true,
        'URL': true
    }
}
