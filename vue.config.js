module.exports = {
  transpileDependencies: [
    'vuetify'
  ],
  publicPath: './',

  configureWebpack: {
    optimization: {
      splitChunks: false
    }
  }
}
