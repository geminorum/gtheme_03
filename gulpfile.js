(function() {

  var
    gulp = require('gulp'),
    gutil = require('gulp-util'),
    plugins = require('gulp-load-plugins')(),
    parseChangelog = require('parse-changelog'),
    prettyjson = require('prettyjson'),
    extend = require('xtend'),
    yaml = require('js-yaml'),
    del = require('del'),
    fs = require('fs'),

    pkg = JSON.parse(fs.readFileSync('./package.json')),
    config = require('./gulpconfig.json'),

    env = config.env,
    banner = config.banner.join('\n');

  try {
    env = extend(config.env, yaml.safeLoad(fs.readFileSync('./environment.yml', {encoding: 'utf-8'}), {'json': true}));
  } catch (e) {
    gutil.log('no environment.yml loaded!');
  }

  gulp.task('pot', function() {
    return gulp.src(config.input.php)
    .pipe(plugins.excludeGitignore())
    .pipe(plugins.wpPot(config.pot))
    .pipe(gulp.dest(config.output.languages));
  });

  gulp.task('textdomain', function() {
    return gulp.src(config.input.php)
      .pipe(plugins.excludeGitignore())
      .pipe(plugins.checktextdomain(config.textdomain));
  });

  gulp.task('sass', function() {
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

  gulp.task('watch', function() {
    gulp.watch(config.input.sass, gulp.series('sass'));
  });

  gulp.task('bump:package', function(){
    return gulp.src('./package.json')
    .pipe(plugins.bump().on('error', gutil.log))
    .pipe(gulp.dest('.'));
  });

  gulp.task('bump:theme', function(){
    return gulp.src(config.pot.metadataFile)
    .pipe(plugins.bump().on('error', gutil.log))
    .pipe(gulp.dest('.'));
  });

  gulp.task('bump', gulp.series(
    'bump:package',
    'bump:theme',
    function(done) {
      gutil.log('Bumped!');
      done();
  }));

  gulp.task('default', function(done) {
    gutil.log('Hi, I\'m Gulp!');
    gutil.log("Sass is:\n"+require('node-sass').info);
    gutil.log("\n");
    console.log(prettyjson.render(pkg));
    gutil.log("\n");
    console.log(prettyjson.render(config));
    done();
  });
}());
