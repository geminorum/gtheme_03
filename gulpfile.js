(function () {
  var gulp = require('gulp');
  var plugins = require('gulp-load-plugins')();
  var cssnano = require('cssnano');
  var autoprefixer = require('autoprefixer');
  var rtlcss = require('rtlcss');
  var parseChangelog = require('parse-changelog');
  var publishRelease = require('publish-release');
  var prettyjson = require('prettyjson');
  var extend = require('xtend');
  var yaml = require('js-yaml');
  var log = require('fancy-log');
  // var del = require('del');
  var fs = require('fs');

  var pkg = JSON.parse(fs.readFileSync('./package.json'));
  var config = require('./gulp.config.json');

  var env = config.env;
  // var banner = config.banner.join('\n');

  try {
    env = extend(config.env, yaml.safeLoad(fs.readFileSync('./environment.yml', { encoding: 'utf-8' }), { 'json': true }));
  } catch (e) {
    log.warn('no environment.yml loaded!');
  }

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

  gulp.task('dev:rtl', function () {
    return gulp.src(config.input.sass)
      .pipe(plugins.sourcemaps.init())
      .pipe(plugins.sass.sync(config.sass).on('error', plugins.sass.logError))
      .pipe(plugins.postcss([
        cssnano(config.cssnano.dev),
        autoprefixer(config.autoprefixer.dev)
      ]))
      .pipe(plugins.sourcemaps.write(config.output.sourcemaps))
      .pipe(plugins.size({ title: 'CSS:', showFiles: true }))
      .pipe(gulp.dest(config.output.css)).on('error', log.error)
      .pipe(plugins.postcss([rtlcss()]))
      .pipe(plugins.rename({ suffix: '-rtl' }))
      .pipe(plugins.size({ title: 'RTL:', showFiles: true }))
      .pipe(gulp.dest(config.output.css)).on('error', log.error)
      .pipe(plugins.changedInPlace())
      .pipe(plugins.debug({ title: 'Changed' }))
      .pipe(plugins.if(function (file) {
        if (file.extname !== '.map') return true;
      }, plugins.livereload()));
  });

  gulp.task('watch:styles', function () {
    plugins.livereload.listen();
    gulp.watch(config.input.sass, gulp.series('dev:rtl'));
  });

  gulp.task('ready:sass', function () {
    return gulp.src(config.input.sass)
      .pipe(plugins.sass(config.sass).on('error', plugins.sass.logError))
      .pipe(plugins.postcss([
        cssnano(config.cssnano.build),
        autoprefixer(config.autoprefixer.build)
      ]))
      .pipe(gulp.dest(config.output.css)).on('error', log.error);
  });

  // seperated because of stripping rtl directives in compression
  gulp.task('ready:rtl', function () {
    return gulp.src(config.input.sass)
      .pipe(plugins.sass(config.sass).on('error', plugins.sass.logError))
      .pipe(plugins.postcss([
        rtlcss(),
        cssnano(config.cssnano.build),
        autoprefixer(config.autoprefixer.build)
      ]))
      .pipe(plugins.rename({ suffix: '-rtl' }))
      .pipe(gulp.dest(config.output.css)).on('error', log.error);
  });

  gulp.task('ready', gulp.series(
    gulp.parallel('ready:sass', 'ready:rtl'),
    function (done) {
      log('Done!');
      done();
    }
  ));

  gulp.task('github:tag', function (done) {
    if (!env.github) {
      log.error('Error: missing required token for github');
      return done();
    }

    var changes = parseChangelog(fs.readFileSync('CHANGES.md', { encoding: 'utf-8' }), { title: false });
    var repo = /github\.com:?\/?([\w-]+)\/([\w-]+)/.exec(pkg.repository.url);
    var options = {
      token: env.github,
      tag: pkg.version,
      name: pkg.version,
      notes: changes.versions[0].rawNote,
      owner: repo[1],
      repo: repo[2],
      skipIfPublished: true,
      draft: true
    };

    var release = publishRelease(options, done);

    release.on('error', function (existingError) {
      if (existingError instanceof Error) {
        log.error(existingError.name + ': ' + existingError.message);
      } else {
        log.error(JSON.stringify(existingError));
      }
    });

    release.on('created-release', function () {
      log.info('Release created successfully at https://github.com/' +
        options.owner + '/' + options.repo + '/releases/tag/' + options.tag);
    });
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

  gulp.task('build', function (done) {
    log.info('No build process necessary!');
    done();
  });

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
