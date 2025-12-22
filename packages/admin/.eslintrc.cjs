module.exports = {
  env: {
    browser: true,
    node: true,
  },
  extends: [
    'eslint:recommended',
    'plugin:vue/vue3-recommended',
    '@vue/eslint-config-typescript',
    'prettier',
  ],
  parser: 'vue-eslint-parser',
  parserOptions: {
    parser: '@typescript-eslint/parser',
    sourceType: 'module',
  },
  rules: {
    'no-alert': 'warn',
    'no-console': 'off',
    'no-debugger': 'warn',
    'sort-imports': [
      'warn',
      {
        ignoreDeclarationSort: true,
      },
    ],
    'vue/match-component-import-name': 'error',
    'vue/multi-word-component-names': 'off',
    'vue/no-v-html': 'off',
  },
}
