const path = require('path')
const webpack = require('webpack')

module.exports = {
  entry: {
    check: './assets/js/src/check.js',
    market: './assets/js/src/market.js'
  },
  output: {
    filename: '[name].js',
    path: path.resolve(__dirname, 'assets', 'js', 'dist')
  },
  module: {
    rules: [
      {
        test: /\.js$/,
        enforce: 'pre',
        loader: 'eslint-loader',
        options: {
          emitWarning: false,
          quiet: true
        }
      },
      {
        test: /\.js$/,
        loader: 'babel-loader',
        options: {
          presets: ['es2015']
        }
      }
    ]
  },
  plugins: [
    new webpack.optimize.ModuleConcatenationPlugin(),
    new webpack.optimize.UglifyJsPlugin()
  ]
}
