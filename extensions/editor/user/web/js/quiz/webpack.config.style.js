const path = require("path");
const MiniCssExtractPlugin = require("mini-css-extract-plugin");

module.exports = {
  entry: "./src/index.js",
  module: {
    rules: [
      {
        test: /\.css$/,
        use: [MiniCssExtractPlugin.loader, "css-loader"],
      },
      {
        test: /\.s[ac]ss$/i,
        use: [MiniCssExtractPlugin.loader, "css-loader", "sass-loader"],
      },
    ],
  },
  plugins: [
    new MiniCssExtractPlugin({
      filename: "styles.css", // This is the name of the separate CSS file.
    }),
  ],
  output: {
    filename: "bundle.js",
    path: path.join(__dirname, "dist"),
    libraryTarget: "umd",
    libraryExport: "default",
    library: "Quiz",
  },
};
