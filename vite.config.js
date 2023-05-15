const path = require('path')
const { defineConfig } = require('vite')

module.exports = defineConfig({
  build: {
    lib: {
      entry: path.resolve(__dirname, 'scripts/main.js'),
      formats: ['umd'],
      name: 'wp-focal-point',
      fileName: (format) => `wp-focal-point.${format}.js`
    }
  }
});