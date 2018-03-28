(function () {
  var gulp = require('gulp');
  var plugins = require('gulp-load-plugins')();
  // var parseChangelog = require('parse-changelog');
  var prettyjson = require('prettyjson');
  // var extend = require('xtend');
  // var yaml = require('js-yaml');
  var log = require('fancy-log');
  // var del = require('del');
  var fs = require('fs');

  var pkg = JSON.parse(fs.readFileSync('./package.json'));
  var config = require('./gulpconfig.json');

  // var env = config.env;
  // var banner = config.banner.join('\n');

  // try {
  //   env = extend(config.env, yaml.safeLoad(fs.readFileSync('./environment.yml', {encoding: 'utf-8'}), {'json': true}));
  // } catch (e) {
  //   log.warn('no environment.yml loaded!');
  // }

  gulp.task('pot', function () {
    return gulp.src(config.input.php)
      .pipe(plugins.excludeGitignore())
      .pipe(plugins.wpPot(config.pot))
      .pipe(gulp.dest(config.output.languages));
  });

  gulp.task('textdomain', function () {
    return gulp.src(config.input.php)
      .pipe(plugins.excludeGitignore())
      .pipe(plugins.checktextdomain(config.textdomain));
  });

  gulp.task('sass', function () {
    return gulp.src(config.input.sass)
      // .pipe(plugins.sourcemaps.init())
      .pipe(plugins.sass.sync(config.sass).on('error', plugins.sass.logError))
      .pipe(plugins.cssnano({
        zindex: false,
        discardComments: {
          removeAll: true
        }
      }))
      // .pipe(plugins.sourcemaps.write('./maps'))
      .pipe(gulp.dest(config.output.css));
  });

  gulp.task('watch', function () {
    gulp.watch(config.input.sass, gulp.series('sass'));
  });

  gulp.task('bump:package', function () {
    return gulp.src('./package.json')
      .pipe(plugins.bump().on('error', log.error))
      .pipe(gulp.dest('.'));
  });

  gulp.task('bump:theme', function () {
    return gulp.src(config.pot.metadataFile)
      .pipe(plugins.bump().on('error', log.error))
      .pipe(gulp.dest('.'));
  });

  gulp.task('bump', gulp.series(
    'bump:package',
    'bump:theme',
    function (done) {
      log('Bumped!');
      done();
    })
  );

  gulp.task('default', function (done) {
    log.info('Hi, I\'m Gulp!');
    log.info('Sass is:\n' + require('node-sass').info);
    log.info('\n');
    console.log(prettyjson.render(pkg));
    log.info('\n');
    console.log(prettyjson.render(config));
    done();
  });
}());
