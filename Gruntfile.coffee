###
jshint node:true
###
module.exports = (grunt) ->
  require('load-grunt-config')(
    grunt
    {
      data: {
        dir: {
          assets: 'assets'
          archive_folder: 'v8ch-card-widget'
          dist: 'dist'
          dist_filename: 'v9ch-card-widget-0_1.zip'
          js: 'assets/js'
          vendor: 'vendor'
          views: 'views'
        }
      }
    }
  )
