// eslint-disable-next-line no-undef
module.exports = {
    extends: [
        'eslint:recommended',
        'plugin:prettier/recommended',
        'plugin:vue/recommended',
        'prettier/vue',
    ],
    parser: 'vue-eslint-parser',
    parserOptions: {
        parser: 'babel-eslint',
        sourceType: 'module',
    },
    plugins: ['babel', 'vue'],
    rules: {
        quotes: ['error', 'single'],
        'no-debugger': 'off',
        'no-console': 'off',
        'babel/no-unused-expressions': 'error',
        'no-unused-expressions': 'off',
        'prettier/prettier': [
            'error',
            {
                singleQuote: true,
                semi: true,
                trailingComma: 'all',
                jsxBracketSameLine: true,
                endOfLine: 'auto',
            },
        ],
    },
};
