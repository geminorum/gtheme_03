### 3.21.2
* module/filters: dns prefetch support
* module/filters: preconnect domains support
* module/navigation: except default term on breadcrumbs
* partial/summery: custom for publication

### 3.21.1
* core/widget: :warning: passing new instance as base
* core/widget: custom title on the form
* module/banners: move up enqueue slick
* module/content: inline qrcode script
* module/options: general color scheme as info
* module/search: deprecated jquery method
* partial/band: :new: partial
* partial/entry: move highlight after header
* root/content: summary suffix based on posttype
* root/row: new default row for hover
* widget/custom html: optional bypass cache
* widget/pack grid: :new: widget
* widget/post row: check post publicly viewable
* widget/the term: :warning: correct order of args

### 3.21.0
* core/widget: avoid type url for relatives
* core/widget: custom html after title
* core/widget: support more defaults on updates
* module/editorial: comp with new version
* module/sidebar: new default sidebars
* module/template: custom logo helper

### 3.20.16
* core/widget: :warning: correct arg name
* module/editorial: rename the source method
* module/filters: deferred styles revised
* module/filters: reset body overflow on non js with preload
* module/image: custom ratio of placeholder
* module/menu: pass extra before/after args into core method
* module/template: alias for 404 as context
* widget/banner group: correct handling options
* widget/related posts: optional wrap as items

### 3.20.14
* module/content: handling dummy posts
* module/image: check if thumbnail attachment exists
* module/image: convert tags into thumbnails
* module/image: filtering thumbnail id
* module/shortcodes: :new: shortcode for content header
* widget/post featured: :new: widget

### 3.20.13
* module/bootstrap: customized logo for navbar brand
* module/content: contexted loop as rows
* module/content: l10n for entry published action
* module/content: optional trim title/meta on headers
* module/image: apply default filters on empty image results
* module/options: switch id helper
* module/settings: remove all posts node from adminbar
* module/terms: check for system tags before tax queries
* module/widget: form has thumbnail method
* partial/entry publication: list attachment as default
* partial/entry course: list lessons descending after content
* partial/page latest: default content for latest page
* row/latest: default content for latest
* widget/related posts: must has thumbnail option
* widget/search terms: optional prefix terms with taxonomy labels

### 3.20.12
* module/comments: better filtering form strings
* module/image: placeholder with aspect ratio

### 3.20.11
* root/all: general selector for containers

### 3.20.10
* module/banners: custom id on bootstrap carousel
* module/banners: explicit returns on banner helpers
* module/banners: more banner helpers
* module/editorial: general helper for html meta fields
* module/navigation: :warning: correct crumb!
* module/theme: wpcf7 scripts on non-contact page shortcodes
* root/index: breadcrumb on 404
* widget/banner group: :new: widget
* widget/post row: :new: widget
* widget/search terms: avoid filtred search query
* widget/search terms: display empty terms
* widget/search terms: search name and slugs

### 3.20.9
* module/editorial: better handling over/sub titles
* module/navigation: archive crumb revised
* module/navigation: filter for strings on content method
* module/options: group check in array
* module/search: customize label/submit texts on search forms
* module/search: customize placeholder on search forms
* module/sidebar: widgetized sidebars for primaries
* module/template: check for base before context on column classes
* partal/entry: publication: navigation by subject
* widget/search terms: singular name as title on multiple taxonomy setup

### 3.20.8
* module/bootstrap: logo + title on navbar brand
* module/content: filter for empty byline
* module/editorial: filter book summary fields
* module/navigation: filter navigation content
* module/search: avoid js if no actions available
* module/search: helper for search link
* module/terms: subject as main taxonomy for publication posttype
* partial/entry: default template for course posttype
* partial/entry: default template for lesson posttype
* partial/entry: meta summary for publication posttype

### 3.20.7
* module/editor: :warning: handling group fallbacks on editor styles
* module/sidebar: get count helper
* widget/recent posts: optional order by menu order
* widget/term posts: avoid cache on singluar posttype
* widget/term posts: optional order by menu order

### 3.20.6
* module/search: :new: form with action selector
* widget/recent posts: optional empty message
* widget/term posts: optional empty message

### 3.20.5
* main/widget: :new: title image for widgets
* main/widget: avoid notice on empty page select
* main/widget: prep css classes
* main/widget: using html class for labels
* moduel/banners: wrapper div for bootstrap carousel
* module/banners: css class for group items
* module/banners: mandatory group name on carousel helpers
* module/banners: reseting the returned group items
* module/banners: slick carousel support for groups
* module/editorial: optional check for posttype on book cover
* module/editorial: wrapper for book isbn barcode
* module/editorial: wrapper for book meta summay
* module/filters: :warning: handling group fallbacks on screen/print styles
* module/filters: disable adminbar on print

### 3.20.4
* misc/bootstrap walker: applying core filter on mega-menu css classes
* module/banners: extra before/after on group helpers
* module/content: action hooks on header
* module/content: render single action with custom post
* module/editorial: course lessons list helper
* module/editorial: inquire question helper
* module/editorial: key fallback for label helper
* module/filters: :warning: handling group fallbacks on preload styles
* module/settings: action hook for settings legend
* module/sidebar: logic separation on widgets init
* module/sidebar: registering sidebars revised
* module/sidebar: widgetized sidebars for primaries
* module/terms: better handling main taxonomy for posttypes
* module/terms: optional parents only on the list
* widget/recent posts: avoid caching on singular

### 3.20.3
* module/bootstrap: separated navbar toggler
* module/content: bootstrap qrcode content action
* module/content: editorial published as content action
* module/content: entry actions revised
* module/content: rethinking title attr helper
* module/content: simplifying the logic on post action filters
* module/editorial: publication shortcode wrapper
* module/editorial: shortcode helper for courses/lessons
* module/filters: overflow handling on preload
* module/image: image tags are disabled by default
* module/navigation: switch to new meta helper for label
* module/pages: dynamic class for page links
* module/pages: filter to override page links
* module/theme: logic separation for custom logo support

### 3.20.2
* assest/css: better handling direction/group on css includes
* assets/child: textdomain for the child
* module/banners: better handling html tags
* module/banners: bootstrap carousel support for groups
* module/banners: helpers revised
* module/banners: optional return of banner html helper
* module/banners: paginated carousel
* module/bootstrap: additional markup within collapse
* module/bootstrap: rethinking mega-menus
* module/content: format in post class
* module/content: pocket button action
* module/content: post link/title helpers
* module/content: support for entry section as category action
* module/filters: additional post class
* module/image: skip core filter for img tags
* module/options: home url helper
* module/options: support for theme info on lang in iso639
* module/pages: link helper revised
* module/template: about helper
* module/template: filtering copyright text
* module/template: posttype as suffix to contexts on wrap open/close
* partial/entry: default content structure for event posttype
* partial/entry: default content structure for publication posttype
* partial/home: default dashboard with paginated carousel
* root/404: root part for 404
* root/bbpress: initial support
* root/content: page template for non-page systempages
* root/end: rethinking partials

### 3.20.1
* module/content: properly escape a2a link
* module/editorial: disable like button system tag
* widget/post terms: exclude uncategorized

### 3.20.0
* main/constants: people taxonomy constant
* module/attachment: customize image tag on media page
* module/bootstrap: comment callback for bs4
* module/comments: before/after action hooks for comments
* module/comments: skip form on print
* module/comments: title callback simple
* module/content: better anchors for comments link action
* module/content: byline as post action
* module/content: custom tag for wrap
* module/content: loop index class for posts
* module/content: more args on header
* module/content: print link for previews
* module/content: wrap title on page links
* module/editorial: highlight meta helper
* module/filters: stylesheet fallback for child themes
* module/image: area label for parent links
* module/image: default css class helper
* module/navigation: hide on print class
* module/shortcodes: :new: person picture shortcode
* module/shortcodes: content as custom caption on person images
* module/shortcodes: customize image tag on gallery slider
* module/template: custom wrap open/close
* module/template: new default avatar
* module/theme: more action hooks
* partial/entry: deafult like button support
* partial/home: fallback for blog with no homepage
* root/base: simplify wrapper/container divs
* root/content: better handling entries
* root/content: better handling entries
* root/content: better handling entries
* root/home: new default part
* root/row: inline sub-template
* widget/search terms: strip hashtags from criteria
* widget/the term: better handling tax conditionals
* widget/the term: more control over disabled constant

### 3.19.5
* module/editor: more general styles
* module/editorial: list attachments
* widget/post terms: :new: widget
* widget/search terms: restrict to public taxonomies
* widget/search terms: support all taxonomies

### 3.19.4
* module/comments: comments form renderer
* module/comments: comments list renderer
* module/comments: comments title callback
* module/content: print posttypes option
* module/content: trim custom excerpts
* module/date: rethinking date method
* module/date: support for time ago script
* module/frontpage: extra ids for displayed
* module/frontpage: helper for latest post
* module/image: alt attr for term images
* module/navigation: support in same term nav
* module/shortcodes: :new: shortcode: releated posts
* module/sidebar: :new: widget: search terms
* module/template: custom link for site modified in copyright block
* module/terms: image helper for primary terms
* module/theme: disable bp styles by default
* widget/the term: before/after action hooks
* widget/the term: has image class
* widget/the term: link name/image
* widget/the term: name fallback in case of a custom title
* widget/the term: singular support

### 3.19.3
* missed bump

### 3.19.2
* module/content: drop support for printfriendly
* module/navigation: apply filters on breadcrumbs
* module/terms: more identifiers for primary terms
* root/content: partial for page entry only

### 3.19.1
* module/attachment: support for epub mime
* module/editor: dequeue block library theme
* module/shortcodes: skip empty id attr on captions
* module/social: apply filter on meta output
* module/wrap: new core action hook

### 3.19.0
* core/widget: skip linking empty titles
* module/attachments: csv/pdf template support via shortcode
* module/colors: separate arg for disable custom colors
* module/content: check for actions on pages
* module/content: custom link on headers
* module/content: entry pages navigation
* module/content: h2 title on singular headers only
* module/content: meta as title attr on headers
* module/content: post actions classes revised
* module/content: sub classes for header blocks
* module/content: template for read more
* module/editor: de-queue front-end block styles
* module/editorial: control over word-wrap in meta fields
* module/editorial: get meta wrapper
* module/editorial: properly falling back to the defaults
* module/embed: lead meta as excerpt
* module/filters: better passing direction into css loader
* module/filters: factor theme group in stylesheets
* module/filters: non-breakable space before excerpt more
* module/filters: passing theme group into css loader
* module/image: before/after for term image
* module/image: tweaks colum fallback
* module/image: tweaks colum only if empty
* module/navigation: better navigation on posts
* module/navigation: hide search form on zero results
* module/navigation: skip content navs on pages
* module/pages: display selected as page state
* module/search: properly escape search queries
* module/social: remove support for gplus
* module/terms: no related system tag

### 3.18.1
* module/editorial: explicitly check for module enabled

### 3.18.0
* module/colors: new module
* module/content: using post from atts in headers
* module/editor: support for block styles
* module/filters: disable deferred on debug
* module/terms: system tags on block editor
* module/theme: support for align wide
* module/theme: support for custom font sizes
* widget/the term: using prep desc helper

### 3.17.0
* root/content: link image to attachment page on singular
* partal/navbar: new default template
* core/third: renamed from misc
* module/bootstrap: seperate file for nav walker
* module/content: print link action
* module/content: editorial estimated action
* module/content: byline on header
* module/content: header default shortlink option
* module/content: header default title disable option
* module/content: addtoany script on footer
* module/content: default actions on footer
* module/content: check for dummy post on actions
* module/comments: cleanup comment callback
* module/editorial: optional return of shortcodes
* module/editorial: prefix for estimated
* module/filters: deferred styles with preload
* module/filters: print style enhancements
* module/filters: skip extra tags on print
* module/navigation: disable posttype breadcrumbs
* module/navigation: search form after search breadcrumb
* module/navigation: after breadcrumb action hook
* module/navigation: better handling primary terms on breadcrumb
* module/navigation: main taxonomy for posttype on breadcrumb
* module/options: fontdetect dropped
* module/settings: activation redirect disabled by default
* module/shortcodes: seperate file for page walker
* module/sidebar: seperate files for widgets
* module/theme: support for custom background/logo
* module/template: avoid on dummy posts
* module/template: main wrapper helpers
* module/template: site description helper
* module/template: copyright append site modified
* module/template: copyright append home in print
* module/template: copyright for amp
* module/terms: single term link helper
* module/terms: get with parents helper
* module/utilities: better prep for title/desc
* module/utilities: sanitize sep depricated
* module/utilities: is system page helper
* module/wrap: body close helper

### 3.16.7
* root/single: checking for single and singular
* module/content: passing context into wrap close
* module/content: before/after content actions
* module/content: prevent linking header when no link
* module/editor: check for false string on settings
* module/editor: more default styles
* module/editorial: insert person picture by system tags
* module/editorial: attachment list wrapper
* module/editorial: publication list wrapper
* module/editorial: download prefix for attachment list
* module/filters: type option for auto-paginate
* module/navigation: better breadcrumb conditionals
* module/search: new simple form with no button
* module/social: check for empty titles
* module/social: check for empty post author
* module/sidebar: check for sidebar support
* module/sidebar: default sidebar for after singular entry

### 3.16.6
* root/head: moved up charset meta
* root/start: moved navbar to partials
* root/systempage: specified by constant
* module/admin: filter default template title
* module/bootstrap: cleanup menu attrs
* module/bootstrap: navbar brand helper
* module/bootstrap: bs4 navbar helper
* module/comments: check options for cookies checkbox
* module/comments: check if posttype support comments
* module/comments: check if theme supports comments
* module/editorial: wrapper for estimated module
* module/editorial: custom post for like module
* module/filters: link custom stylesheets
* module/filters: using rtl version for parent stylesheets
* module/image: check if theme supports images
* module/menu: strip core classes from menu item
* module/menu: network nav helper
* module/navigation: passing query into paginate
* module/navigation: check if theme supports breadcrumbs
* module/social: default image disabled
* module/template: check if theme supports sidebars
* module/theme: fewer hooks for aminbar
* module/theme: buddypress no style
* module/wrap: disable social via action hooks
* module/wrap: system page constant on template include

### 3.16.5
* module/pages: refactoring
* module/pages: not found page
* module/settings: overview page
* module/terms: assign cap option
* module/terms: core class for system tag metabox
* module/terms: split primary helper
* module/wrap: constants for signup/activate pages
* module/wrap: drop ie conditionals

### 3.16.4
* module/comments: better autocomplete on cooment form
* module/comments: cookie confirm on comment form
* module/content: initial support for amp
* module/counts: correct defaults upon saving
* module/counts: default over info priority

### 3.16.3
* root/singular: new template for system pages
* assets/scripts: fixed jquery error
* module/filters: more tags on theme color
* module/navigation: breadcrumb support for selected posttypes
* module/navigation: check for empty crumbs
* module/social: skip meta tags on signup/activate pages
* module/social: fewer action calls
* module/template: check tax suppport for given post
* module/template: new telephone link helper

### 3.16.2
* root: breaking templates into partials
* module/content: bypass byline on pages
* module/pages: custom url for link helper
* module/image: refactoring image methods
* module/image: passing default class on image method
* module/sidebar: sanitize term name before display
* module/theme: removing core's auto locale styles
* module/theme: skipping singular script
* module/template: sanitize term name before display

### 3.16.1
* module/content: :new: byline helper
* module/content: simple header for embed/twitter feed
* module/feed: better handling restricted
* module/filters: :new: handling restricted on rest
* module/embed: :new: overwrite response data
* module/options: :new: theme group ui
* module/sidebar: widget core updated
* module/banners: tag start/end for group items

### 3.16.0
* root: new generic template parts
* root: header/footer for signup/activate pages
* root/content: correct context for singular wraps
* module/core: more default core methods
* module/core: load modules on activate page
* module/banners: moved tab html into module class
* module/content: deprecate use of genericons
* module/content: method renamed to avoid confilict
* module/comments: method renamed to avoid confilict
* module/counts: using defaults by default!
* module/editorial: :new: wrapper for like button
* module/editorial: reflist upgrade
* module/filters: cleanup excerpt more
* module/filters: skip empty excerpt link
* module/filter: handling multiple classes on body
* module/image: disable post thumbnail fallback
* module/image: get image revised
* module/image: more control over tags and ui
* module/image: check system tags for `hide-image-single`
* module/menu: caching menus
* module/navigation: almost rewrite!
* module/pages: using defaults by default!
* module/settings: correct current url for flush
* module/social: delay open graph tags
* module/shortcodes: gallery scripts revised
* module/shortcodes: extra arg for post gallery filter
* module/sidebar: :new: widget: custom html
* module/sidebar: more form helpers
* module/shortcodes: using minified gallery script
* module/wrap: static renamed to avoid confilict

### 3.15.1
* module/options: :wrench: de-centering options

### 3.15.0
* module/editorial: setup post data for issue row callback
* module/editorial: reset post data for issue row callback
* module/comments: :warning: fixed disappearing comments!
* module/comments: :warning: current time for human time diff
* module/navigation: :new: network home as first crumb
* module/image: :new: support for taxonomy image sizes
* module/image: :new: term image helper
* module/social: :new: support title/image for terms
* module/sidebar: :new: term image on term widget

### 3.14.11
* module/counts: saving zeros
* module/editorial: upgrade meta source helper
* module/editorial: upgrade issue posts helper
* module/image: support editorial tweaks admin thumb column

### 3.14.10
* core/wordpress: :new: core class
* module/sidebar: switch to transient
* module/sidebar: fallback for widget title helper
* module/sidebar: hide if empty desc for the term widget
* module/sidebar: fewer checks for the term widget
* module/sidebar: passing control optins into parent class
* module/sidebar: form taxonomy list based on posttype
* module/sidebar: strings revised
* module/sidebar: removed old alloption deletion

### 3.14.9
* module/cache: default ttl up to 12 hours
* module/content: body/post css classes revised
* module/embed: :new: initial support
* module/filters: document title parts
* module/settings: new admin title class in 4.8
* module/terms: :new: empty before last month as bulk action on system tags
* module/terms: primary terms ui revised
* module/terms: tax labels for user with cap only
* module/theme: remove post formats support from posts
* module/theme: global content width setting
* root/homepage: :new: template

### 3.14.8
* module/attachment: :new: download helper
* module/attachment: :new: media helper
* module/attachment: image helper merged into media
* module/attachment: back link for no title parent
* module/attachment: caption using core helper
* module/attachment: switch to post object for current attachment
* module/content: version suffix for printfriendly css url
* root/content: :new: default for attachments
* root/content: check for poster system tag
* root/content: footer for non singular

### 3.14.7
* module/content: entry actions now list by default
* module/content: more entry actions
* module/content: print-friendly revised
* module/content: add-to-any revised
* module/editorial: support for modified module
* module/menu: class based on location
* module/navigation: people archive crumb
* module/terms: list helper
* module/terms: has helper
* root/content-image: default template

### 3.14.6
* root: new template for full with page, supporting posts & pages
* assets/styles: minify html updated
* main/modulecore: get terms updated
* module/attachment: check for no caption first
* module/bootstrap: support for mega menu with template part
* module/bootstrap: filter changes for nav menu walker
* module/content: post actions revised
* module/editorial: comp with gEditorial v3.10.0
* module/frontpage: current post in the must excludes
* module/filters: using get post for slug as body css class
* module/navigation: 404 in breadcrumb
* module/options: default date formats
* module/tamplate: correct func for term link
* module/terms: comp with new geditorial tweaks
* module/terms: new link primary term helper

### 3.14.5
* module/admin: not setting the default author by default
* module/attachment: apply typography filters on captions
* module/comments: respect show avatars option
* module/feed: exclude posts from rss by system tags
* module/feed: preparing feed content for twitter/list
* module/feed: using separator on feed metas
* module/filters: overwrite author display name
* module/options: translate separators
* module/options: more default system tags
* module/terms: custom callback for system tags meta box

### 3.14.4
* module/comments: removed strip trackbacks in favor of comment list arg
* module/content: callback post actions
* module/editorial: skip passing before/after for ref-list shortcode
* module/options: more default style formats

### 3.14.3
* module/sidebar: constant to hide term info widget
* module/image: correct id for label for attr

### 3.14.2
* new default template: search advanced
* module/admin: deprecating is super admin
* module/options: new editor style format for entry-list
* module/options: editor style format p to blockquote on entry-quote
* module/sidebar: :new: widget: the term
* module/sidebar: fixed typo
* module/navigation: breadcrumbs methods revised

### 3.14.1
* main/editor: cleanup default buttons, [see](https://make.wordpress.org/core/?p=20431)
* module/editorial: :warning: correct callback checking
* module/image: skip empty alt on html tags
* module/image: cleanup old thumbnail id
* module/menu: discarding whitespace in menu/page lists, [see](https://make.wordpress.org/core/?p=20577)

### 3.14.0
* assets/styles: strip utf bom
* assets/styles: normalize charset def
* core/html: method renamed, fixed fatal on PHP5.6
* main/modulecore: throw & catch exceptions for ajax/installing
* main/modulecore: shortcode helper
* script/all: apply jquery migrate notices
* module/attachment: back link helper
* module/attachment: check if image
* module/editorial: comp with the latest version
* module/feed: refactoring feed content enhancements
* module/comments: new helpers
* module/content: not found message helper
* module/content: method refactoring!
* module/content: wrap open/close helpers
* module/terms: install button for default primary terms
* module/social: large image on twitter cards
* module/shortcodes: rewriting `[caption]` shortcode
* module/image: disable new core default image size
* module/image: rewriting img tag instead of figure

### 3.13.0
* moved to [Semantic Versioning](http://semver.org/)
* restructure inc folder
* setting up gulp
* first draft: copy disabled
* assets: better loading styles, [see](https://make.wordpress.org/core/2016/03/08/enhanced-script-loader-in-wordpress-4-5/)
* script: [Autosize](http://www.jacklmoore.com/autosize/) updated to v3.0.15
* module/attachment: image helper
* module/comments: using theme strings
* module/terms: hook term installer to load edit tags page
* module/bootstrap: mega menu support for menu walker
* module/image: support [gNetwork](https://github.com/geminorum/gnetwork/) [Media](https://github.com/geminorum/gnetwork/wiki/Modules-Media) Object Sizes
* module/filters: theme color meta tag
* module/filters: skip on admin
* module/content: row helper
* module/wrap: head/body helpers
* module/pages: pre page creator api
* module/pages: nav menu for pre pages
* module/pages: drop def arg on pages api
* module/pages: getting default by path on pages api
* module/sidebar: list empty terms
* module/sidebar: exclude current post from the list

### 0.3.12
* social: default [OGp](http://ogp.me/) type to `website`
* content: correct title attr for shortlinks

### 0.3.11
* modulecore: single checkbox desc as title
* navigation: passing custom max num pages to next/pre helper
* counts: new module
* settings: legend info
* filters: switched to document title with fallback to the old one
* comments: correct anchor for after posting comments

### 0.3.10
* all: safe json encode
* bootstrap: navbar class helper

### 0.3.9
* all: deprecate directory separator
* utilities: check user cap before flush

### 0.3.8
* images: also register title on additional sizes
* editorial: word wrap on meta overtitle & subtitle

### 0.3.7
* content: using word wrap only via header helper
* filters: body class: active sidebars / prefix the uri / check for debug display
* shortcodes: fixed slider gallery not including ids

### 0.3.6
* sidebar: class api for all widgets

### 0.3.5
* recent posts now supports other posttypes
* new module: editorial helper
* new module: attachment helper
* new widget: term posts
* sidebar: title link api for all widgets
* terms: editor cap for system tags assignments

### 0.3.4
* better flush handling for cache module
* new module: bootstrap helper
* new module: date helper
* css class in nav menus
* default template base order changed to : header/start/template(w/o:sidebar)/end/footer
* adding responsive class to image tags
* primary terms api
* better handling widgets
* support for [gEditorial](https://github.com/geminorum/geditorial) tweaks module
* image holder

### 0.3.3
* cleanup almost all

### 0.3.2
* sass!

### 0.3.1
* wp_title is now on wp_head action, [see](https://make.wordpress.org/core/2014/10/29/title-tags-in-4-1/).
* new nav menu argument handling
* support system tags from gtheme_02
* new method for cached WP_Query
* fixed module arguments not loading
* inculing [WP_Image](https://github.com/markoheijnen/WP_Image)
* loaded fonts as body class via [FontDetect](https://github.com/JenniferSimonds/FontDetect)

### 0.3.0
* init
