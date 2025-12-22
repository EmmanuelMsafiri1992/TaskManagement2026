module.exports = {
  trailingComma: 'all',
  tabWidth: 2,
  semi: false,
  singleQuote: true,
  vueIndentScriptAndStyle: true,
  singleAttributePerLine: false,
  overrides: [
    {
      files: ['*.md'],
      options: {
        tabWidth: 4,
      },
    },
  ],
  tailwindConfig: './tailwind.config.cjs',
  plugins: [
    require('prettier-plugin-tailwindcss')
  ]
}
