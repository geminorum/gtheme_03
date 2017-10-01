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
