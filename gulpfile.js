(function() {
  'use strict';

  // https://www.npmjs.com/package/gulp-iconfont

  var
    gulp = require('gulp'),
    sass = require('gulp-sass'), // https://github.com/dlmanning/gulp-sass
    nano = require('gulp-cssnano'), // https://github.com/ben-eb/gulp-cssnano
    sourcemaps = require('gulp-sourcemaps'),
    smushit = require('gulp-smushit'), // https://github.com/heldr/gulp-smushit
    excludeGitignore = require('gulp-exclude-gitignore'), // https://github.com/sboudrias/gulp-exclude-gitignore
    wpPot = require('gulp-wp-pot'), // https://github.com/rasmusbe/gulp-wp-pot
    fs = require('fs');

  var
    pkg = JSON.parse(fs.readFileSync('./package.json'));

  gulp.task('smushit', function() {

    return gulp.src('./images/raw/**/*.{jpg,png}')

      .pipe(smushit())

      .pipe(gulp.dest('./images'));
  });

  gulp.task('pot', function() {

    return gulp.src([
      'style.css',
      './**/*.php',
      '!./assets/**',
      '!./css/**',
      '!./fonts/**',
      '!./images/**',
      '!./js/**',
      '!./libs/**',
      '!./packages/**',
      '!./stylesheets/**'
    ])

      .pipe(excludeGitignore())

      .pipe(wpPot(pkg._pot))

      .pipe(gulp.dest('./languages/' + pkg.name + '.pot'));
  });

  gulp.task('sass', function() {

    return gulp.src('./stylesheets/*.scss')

      // .pipe(sourcemaps.init())

      .pipe(sass.sync({
        includePaths: 'components/bootstrap-sass/assets/stylesheets'
      }).on('error', sass.logError)).pipe(nano({
        // http://cssnano.co/optimisations/
        zindex: false,
        discardComments: {
          removeAll: true
        }
      }))

      // .pipe(sourcemaps.write('./maps'))

      .pipe(gulp.dest('./css'));
  });

  gulp.task('watch', function() {

    gulp.watch('./stylesheets/**', gulp.series('sass'));
  });

  gulp.task('default', function() {

    console.log('Hi, I\'m Gulp!');
    console.log("Sass is:\n" + require('node-sass').info);
  });
}());
