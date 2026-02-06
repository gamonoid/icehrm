const path = require("path");

module.exports = {
  entry: "./src/index.js",
  module: {
    rules: [
      {
        test: /\.css$/,
        use: ["style-loader", "css-loader"],
      },
      {
        test: /\.s[ac]ss$/i,
        use: ["style-loader", "css-loader", "sass-loader"],
      },
    ],
  },
  output: {
    filename: "bundle.js",
    path: path.join(__dirname, "dist"),
    libraryTarget: "umd",
    libraryExport: "default",
    library: "Quiz",
  },
};
