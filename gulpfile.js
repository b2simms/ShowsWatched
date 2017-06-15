'use strict';
/******************************/
/********** PACKAGES **********/
/******************************/
const gulp            = require('gulp');
const pump            = require('pump');  // Error handling for Node Streams
const plumber         = require('gulp-plumber'); // Prevents watchers processes to stop when pipes break
const concat          = require('gulp-concat'); // Concatenates globs files into one file

var flatten = require('gulp-flatten');

var paths = {
  js: ['./app/**/*.js'],
  html: ['./app/pages/**/*.html']
};
var act = {
  concat: 'concatJS',
  copy: 'copyHTML'
}

/********************************/
/********** GULP TASKS **********/
/********************************/
gulp.task(act.concat, (done) => {
  pump([
    gulp.src(paths.js),
    plumber(),
    concat('app.js'),
    gulp.dest('./www')
  ], done);
});

/* Move all html from folder javascript/pages/<any> to www/templates */
gulp.task(act.copy, (done) => {
   pump([
    gulp.src(paths.html),
    plumber(),
    flatten(),
    gulp.dest('./www/templates')
   ], done);
});

gulp.task('watch', [act.concat, act.copy], function() {
  gulp.watch(paths.js, [act.concat]);
  gulp.watch(paths.html, [act.copy]);
});

gulp.task('default', ['watch']);