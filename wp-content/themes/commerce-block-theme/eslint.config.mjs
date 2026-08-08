export default [
    {
        languageOptions: {
            ecmaVersion: 2022,
            sourceType: 'module',
            globals: {
                window: 'readonly',
                document: 'readonly',
                console: 'readonly',
                wp: 'readonly',
                jQuery: 'readonly',
                woocommerce: 'readonly',
                ajaxurl: 'readonly',
                commerceCoreData: 'readonly',
                commerceThemeData: 'readonly',
                sessionStorage: 'readonly',
            },
        },
        rules: {
            'no-console': 'warn',
            'no-unused-vars': ['error', { argsIgnorePattern: '^_' }],
            'no-undef': 'error',
            'no-empty': ['error', { allowEmptyCatch: false }],
            'prefer-const': 'error',
            eqeqeq: ['error', 'always'],
            'no-var': 'error',
            quotes: ['error', 'single'],
            semi: ['error', 'always'],
        },
        ignores: ['**/node_modules/**', '**/build/**', '**/*.min.js'],
    },
];
