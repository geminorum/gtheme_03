<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

?><!doctype html><?php

gThemeWrap::htmlOpen();

?><head>
<meta http-equiv="content-type" content="text/html; charset=<?php bloginfo( 'charset' ); ?>" />
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
<meta name="HandheldFriendly" content="true" />
<?php

wp_head();

echo '</head><body ';

body_class();

echo '>';

do_action( 'template_body_top' );
