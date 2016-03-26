###
jshint node:true
###
module.exports = {
  dist: {
    files: [
      {
        dot: true
        src: [
          '<%= dir.dist %>/**/*'
        ]
      }
    ]
  }
}