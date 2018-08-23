/**
 * This is the new entry point for JavaScript development. The idea is to migrate
 * the initial eject react-app to this webpack configuration.
 *
 * @since 3.19.0
 */
const path = require("path");
const webpack = require("webpack");
const MiniCssExtractPlugin = require("mini-css-extract-plugin");

/**
 * See https://www.npmjs.com/package/whatwg-fetch#usage
 * See https://github.com/taylorhakes/promise-polyfill
 *
 * @type {{entry: string[], output: {filename: string, path: *}}}
 */

module.exports = {
  entry: {
    bundle: "./src/Public/index.js",
    edit: "./src/Edit/index.js"
  },
  output: {
    filename: "[name].js",
    path: path.resolve(__dirname, "../../src/js/dist")
  },
  /*
   * Give precedence to our node_modules folder when resolving the same module.
   *
   * This solves duplicate issues with styled-components.
   *
   * @see https://www.styled-components.com/docs/faqs#why-am-i-getting-a-warning-about-several-instances-of-module-on-the-page
   */
  // resolve: {
  //   modules: [path.resolve(__dirname, "node_modules"), "node_modules"]
  // },
  devtool: "source-map",
  module: {
    rules: [
      {
        test: /\.js$/,
        exclude: /(node_modules(?!\/wordlift-for-schemaorg)|bower_components)/,
        use: {
          loader: "babel-loader?cacheDirectory",
          options: {
            presets: ["babel-preset-env"]
          }
        }
      },
      {
        test: /\.s?css$/,
        use: [
          // We use the MiniCssExtractPlugin for both production and development
          // since development happens inside of WordPress which loads the css
          // files anyway (using the enqueue_scripts hook).
          //
          // @see https://webpack.js.org/loaders/sass-loader/#extracting-style-sheets
          MiniCssExtractPlugin.loader,
          "css-loader",
          "sass-loader" // compiles Sass to CSS, using Node Sass by default
        ]
      }
    ]
  },
  plugins: [
    // @see https://webpack.js.org/loaders/sass-loader/#extracting-style-sheets
    new MiniCssExtractPlugin({
      filename: "[name].css"
    }),
    new webpack.DefinePlugin({
      "process.env": {
        NODE_ENV: JSON.stringify(process.env.NODE_ENV)
      }
    })
  ]
};
