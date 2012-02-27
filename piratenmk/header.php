<?php
	require('./cms/wp-blog-header.php');
?><?php echo '<'.'?xml version="1.0" encoding="' . get_bloginfo( 'charset' ) . '"?'.'>' . "\n"; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="de" xml:lang="de">
<head>



	<title><?php bloginfo('name'); ?><?php wp_title(); ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php bloginfo( 'charset' ); ?>" />
	<meta name="generator" content="WordPress <?php bloginfo( 'version' ); ?>" />
	<meta http-equiv="imagetoolbar" content="no"/>
	<meta name="language" content="de" />
	<meta name="publisher" content="Piratenpartei Deutschland - PIRATEN" />
	<link rel="stylesheet" href="<?php bloginfo( 'stylesheet_url' ); ?>" media="screen"/>
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
	<link rel="alternate" type="application/rss+xml" href="<?php bloginfo( 'rss2_url' ) ?>" title="<?php echo wp_specialchars( get_bloginfo('name'), 1 ) . " letzte Beiträge" ?>" />
	<link rel="alternate" type="application/rss+xml" href="<?php bloginfo( 'comments_rss2_url' ) ?>" title="<?php echo wp_specialchars( get_bloginfo('name'), 1 ) . " letzte Kommentare" ?>" />

		<link rel="stylesheet" type="text/css" href="<?php bloginfo('template_url'); ?>/sf/css/superfish.css" media="screen">
		<script type="text/javascript" src="<?php bloginfo('template_url'); ?>/sf/js/jquery-1.2.6.min.js"></script>
		<script type="text/javascript" src="<?php bloginfo('template_url'); ?>/sf/js/hoverIntent.js"></script>
		<script type="text/javascript" src="<?php bloginfo('template_url'); ?>/sf/js/superfish.js"></script>
<script type="text/javascript">
$(document).ready(function() {
        $('ul.sf-menu').superfish();
});
</script>

<?php wp_get_archives( 'type=monthly&format=link' ); ?>
<?php wp_head(); ?>
</head>

<body id="body">
<div id="wrap">
	<div id="heimathafen">
		<?php
			require( '1_heimathafen.php' );
		?>
	</div>

<div id="search">
<form id="searchform" method="get" action="<?php bloginfo('siteurl')?>/">
		<input type="text" name="s" id="s" class="textbox" value="Website durchsuchen" onfocus="if(this.value==this.defaultValue)this.value='';" onblur="if(this.value=='')this.value=this.defaultValue;" />
		<input id="btnSearch" type="submit" name="submit" value="<?php _e('Go'); ?>" />
</form>
</div>

	<div id="oben">

	</div>
	<div id="toplinks">
		<div class="toplinkstext" style="padding-top: 4px;">
<!--			<ul class="links-menu">
<li><a href="./">Startseite</a></li>
<li><a href="./warum-piraten">Unsere Ziele</a></li>
<li><a href="./forum">Mitmachen</a></li>
<li><a href="http://wiki.piratenpartei.de/Oldenburg/Stammtisch">Stammtisch</a></li>
<li><a href="http://wiki.piratenpartei.de/Oldenburg/Arbeitstreffen">Arbeitstreffen</a></li>
<li><a href="./vorstand">Vorstand</a></li>
<li><a href="./?page_id=350">Hinter den Kulissen</a></li>
<li><a href="./?page_id=348">Mitmachen</a></li>
<li><a href="http://wiki.piratenpartei.de/Oldenburg/Kommunalwahl_2011">Wahl 2011</a></li>
<li><a href="./?cat=124">Ratsarbeit</a></li>
<li><a href="./?page_id=365">Programm</a></li>
<li><a href="./?page_id=367">Kontakt (Bürger und Presse)</a></li>
<li><a href="./?page_id=681">A-Z</a></li>

			</ul>-->
				<?php require( '2_menu.php' ); ?>
		</div>
	</div>

	<div id="container">
<!--		<div style="margin-left: 24px; margin-bottom: 10px;">
			<h1 style="    color: darkorange;
    font-size: 22px;
    font-weight: bold;
    letter-spacing: 0.02em;
    line-height: 125%;
    margin-bottom: 15px;
    text-decoration: none;">Dein Team für die Kommunalwahl am 11. September 2011:</h1>
			<div style="

display: block; float: left; margin-right: 10px; overflow:hidden; border: 1px solid #000000; width: 120px;">
				<img src="http://farm7.static.flickr.com/6144/5927165082_596609599d_m.jpg" width="120px">
				<p style="text-align: center;">Markus Elsken<br>Wahlbereich 1</p>
			</div>

			<a href="http://wiki.piratenpartei.de/Benutzer:Medienfloh"><div style="display: block; float: left; margin-right: 10px; overflow:hidden; border: 1px solid #000000; width: 120px;">
				<img src="http://farm7.static.flickr.com/6145/5927166154_fb1dca55ab_m.jpg" width="120px">
				<p style="text-align: center;">Florian Schuster<br>Wahlbereich 2</p>
			</div></a>
			<div style="display: block; float: left; margin-right: 10px; overflow:hidden; border: 1px solid #000000; width: 120px;">
				<img src="http://farm7.static.flickr.com/6124/5926602433_86b127f3c8_m.jpg" width="120px">
				<p style="text-align: center;">Jan-Martin Meyer<br>Wahlbereich 3</p>
			</div>
			<a href="https://wiki.piratenpartei.de/Benutzer:Tverrbjelke"><div style="display: block; float: left; margin-right: 10px; overflow:hidden; border: 1px solid #000000; width: 120px;">
				<img src="http://farm7.static.flickr.com/6005/5926604619_61dd850d4c_m.jpg" width="120px">
				<p style="text-align: center;">Andreas Hüwel<br>Wahlbereich 4</p>
			</div></a>
			<a href="https://wiki.piratenpartei.de/Benutzer:HolgerL"><div style="display: block; float: left; margin-right: 10px; overflow:hidden; border: 1px solid #000000; width: 120px;">
				<img src="http://farm7.static.flickr.com/6139/6018147365_7d7b195801_m.jpg" width="120px">
				<p style="text-align: center;">Holger Lubitz<br>Wahlbereich 5</p>
			</div></a>
			<a href="https://wiki.piratenpartei.de/Benutzer:Floh1111"><div style="display: block; float: left; margin-right: 10px; overflow:hidden; border: 1px solid #000000; width: 120px;">
				<img src="http://farm7.static.flickr.com/6016/5927164498_77fd968cb9_m.jpg" width="120px">
				<p style="text-align: center;">Clemens John<br>Wahlbereich 6</p>
			</div></a>
			<a href="https://wiki.piratenpartei.de/Benutzer:TimNiemeyer"><div style="display: block; overflow:hidden; border: 1px solid #000000; width: 120px;">
				<img src="http://farm7.static.flickr.com/6017/5927164066_caca008b6d_m.jpg" width="120px">
				<p style="text-align: center;">Tim Niemeyer<br>Wahlbereich 6</p>
			</div></a>
		</div>-->

		<div id="links">
			<div id="sidebar_left" class="sidebar">
				<?php require( 'sidebar_left.php' ); ?>
			</div>
		</div>
