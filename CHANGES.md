### 3.14.2
* new default template: search advanced
* main/sidebar: fixed typo
* main/admin: deprecating is super admin
* module/options: new editor style format for entry-list
* module/options: editor style format p to blockquote on entry-quote
* module/sidebar: :new: widget: the term
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
