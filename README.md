# gTheme 3

Theme Framework for WordPress

[![it's a geminorum project](http://img.shields.io/badge/it's_a-geminorum_project-lightgrey.svg?style=flat)](http://geminorum.ir/)
[![GitHub releases](https://img.shields.io/github/release/geminorum/gtheme_03.svg?style=flat)](https://github.com/geminorum/gtheme_03/releases)
[![GitHub tags](https://img.shields.io/github/tag/geminorum/gtheme_03.svg?style=flat)](https://github.com/geminorum/gtheme_03/tags)
[![GitHub issues](https://img.shields.io/github/issues/geminorum/gtheme_03.svg?style=flat)](https://github.com/geminorum/gtheme_03/issues)
[![Gratipay](http://img.shields.io/gratipay/geminorum.svg?style=flat)](https://gratipay.com/geminorum/)
[![Gitter](https://badges.gitter.im/Join Chat.svg)](https://gitter.im/geminorum/gtheme_03?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge)

### Included Packages
* [Bootstrap](http://getbootstrap.com/) v3.3.2 by Twitter, Inc.
* [Bootstrap 3 RTL Theme](https://github.com/morteza/bootstrap-rtl/) v3.3.1 by [Morteza Ansarinia](https://github.com/morteza)
* [FlexSlider 2](http://www.woothemes.com/flexslider/) ([RTL](https://github.com/layalk/FlexSlider/tree/rtl)) v2.2.0 by [WooThemes](http://www.woothemes.com/) / [Layal K](https://github.com/layalk) at [RTL This](http://rtl-this.com/tutorial/jquery-plugin-flexslider-now-rtl-support)
* [Font Awesome](http://fontawesome.io) v4.2.0 by [Dave Gandy](http://twitter.com/davegandy)
* [Genericons](http://genericons.com/) v3.3 by [Automattic](https://github.com/Automattic)
* [jQuery Superfish Menu Plugin](https://github.com/joeldbirch/superfish/) v1.7.5 by [Joel Birch](https://github.com/joeldbirch)
* [WP_Image](https://github.com/markoheijnen/WP_Image) class by [Marko Heijnen](http://markoheijnen.com/)
* [ZOOM](https://github.com/gurde/ZOOM) by [Robert Călin](http://gurde.com/)
* [FontDetect](https://github.com/JenniferSimonds/FontDetect) v3.0.1 by [Jennifer Simonds](http://www.atomicjetpacks.com/)

### Credits

* (some) Icons made by [Roy and Co](http://royand.co/) from [Flaticon](http://www.flaticon.com) is licensed by [Creative Commons BY 3.0](http://creativecommons.org/licenses/by/3.0/)

## References

not completed yet!

### Actions
* `before_post`
* `after_post`
* `template_body_top`
* `gtheme_do_header`
* `gtheme_do_after_header`
* `gtheme_do_before_footer`
* `gtheme_do_footer`
* `gtheme_content_404`

### Filters
	
	
## ChangeLog

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