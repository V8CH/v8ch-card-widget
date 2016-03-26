###
jshint node:true
###
module.exports = {
  plugin: {
    options: {
      archive: '<%= dir.dist %>/v8ch-card-widget.zip'
    }
    files: [
      {
        cwd: '<%= dir.vendor %>/plugin-updates'
        expand: true
        src: [ '**/*'
        ]
        dest: '<%= dir.archive_folder %>/<%= dir.vendor %>/plugin-updates'
      }
      {
        cwd: '<%= dir.js %>/'
        expand: true
        src: [ '**/*.js' ]
        dest: '<%= dir.archive_folder %>/<%= dir.js %>'
      }
      {
        cwd: '<%= dir.views %>/'
        expand: true
        src: [ '**/*.php' ]
        dest: '<%= dir.archive_folder %>/<%= dir.views %>'
      }
      {
        cwd: ''
        expand: true
        src: [
          'LICENSE'
          'readme.md'
          '*.php'
          'readme.txt'
        ]
        dest: '<%= dir.archive_folder %>'
      }
    ]
  }
}
