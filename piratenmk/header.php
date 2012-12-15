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

<script type="text/javascript">
  function setCookie(c_name,value,exdays) {
    var exdate=new Date();
    exdate.setDate(exdate.getDate() + exdays);
    var c_value=escape(value) + ((exdays==null) ? "" : "; expires="+exdate.toUTCString());
    document.cookie=c_name + "=" + c_value;
  }
</script>

<?php wp_get_archives( 'type=monthly&format=link' ); ?>
<?php wp_head(); ?>
</head>

<body id="body">
<div id="wrap">
<div style="float: left;">
	<div id="heimathafen">
		<?php
			require( '1_heimathafen.php' );
		?>
	</div>


	<div id="oben">
		<div id="search">
			<form id="searchform" method="get" action="<?php bloginfo('siteurl')?>/">
					<input type="text" name="s" id="s" class="textbox" value="Website durchsuchen" onfocus="if(this.value==this.defaultValue)this.value='';" onblur="if(this.value=='')this.value=this.defaultValue;" />
					<input id="btnSearch" type="submit" name="submit" value="<?php _e('Go'); ?>" />
			</form>
		</div>
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
		<div id="top-box" style="margin-left: 24px; padding-bottom: 10px; display: <?php if($_COOKIE['show-top-box-2']=="0") echo "none"; else echo "block"; ?> ;">
<!--			<div style="float: left;">
				<h1 style="	color: darkorange;
						font-size: 22px;
						font-weight: bold;
						letter-spacing: 0.02em;
						line-height: 125%;
						margin-bottom: 15px;
						text-decoration: none;">Sei Pirat und unterstütze deinen Listen- und Direktkandidat:</h1>
			</div>
			<div style="text-align: right; margin-right: 40px; padding-top: 8px;">
				<button onclick="document.getElementById('top-box').style.display = 'none'; setCookie('show-top-box-2', 0, 100);">X</button> 
			</div>
			<br style="clear: both;">
			<p style="margin-bottom: 15px">Am 20. Januar 2013 ist Landtagswahl und da wir noch nicht im Landtag vertreten sind, sammeln wir auch in diesem Jahr wieder Unterstützerunterschriften für unsere Kandidaten, damit diese zur Wahl zugelassen werden.<br>
Sei Pirat, lade dir das Formular unseres Listenkandidaten Gilbert Oltmanns sowie deines Direktkandidaten herunter und schicke beide Formulare ausgefüllt an unseren Vorsitzenden Clemens John, Hamelmannstraße 12, 26129 Oldenburg. Um das richtige Formular für deinen Direktkandidaten zu finden, kannst du deinen Wahlkreis ganz einfach mit unserer <a href="https://piratenpartei-oldenburg.de/wahlkreisfinder/index.php" target="_blank">Wahlkreissuche</a> herausfinden.</p>

			<a href="http://wahl.piraten-nds.de/2012/08/14/gilbert-oltmanns/">
				<div style="display: block; float: left; margin-right: 10px; overflow:hidden; border: 0px solid #000000; width: 120px;">
					<img src="https://piratenpartei-oldenburg.de/cms/wp-content/uploads/2012/09/Gilbert-Oltmanns-768x1024.jpg" width="120px">
				</div>
			</a>

			<div style="display: block; float: left; margin-right: 10px; overflow:hidden; border: 0px solid #000000; width: 140px;">
				<h3>Listenkandidat Gilbert Oltmanns</h3>
				<p>Listenplatz <b>18</b></p><br>
				<p>50 Jahre alt, Bank- kaufmann a.D. und stellv. Vors. der Piraten OL.</p><br>
				<p><a href="http://piratenpartei-oldenburg.de/data/wahlen/landtagswahl/2013/formblatt_unterstuetzerunterschrift_ltwnds13_landesliste.pdf">Formular für die Unterstützerunter-<br>schrift herunterladen</a></p>
			</div>

			<a href="http://wiki.piratenpartei.de/Benutzer:HolgerL">
				<div style="display: block; float: left; margin-right: 10px; overflow:hidden; border: 0px solid #000000; width: 120px;">
					<img src="http://piratenpartei-oldenburg.de/cms/wp-content/uploads/2012/04/holger-225x300.jpg" width="120px">
				</div>
			</a>
			<div style="display: block; float: left; margin-right: 10px; overflow:hidden; border: 0px solid #000000; width: 140px;">
				<h3>Direktkandidat Holger Lubitz</h3>
				<p>Wahlkreis <b>62</b></p><br>
				<p>41 Jahre alt, Diplom-Informatiker und Pirat seit 2009.</p><br>
				<p><a href="http://piratenpartei-oldenburg.de/data/wahlen/landtagswahl/2013/formblatt_unterstuetzerunterschrift_ltwnds13_wk62.pdf">Formular für die Unterstützerunter-<br>schrift herunterladen</a></p>
			</div>

			<a href="http://wiki.piratenpartei.de/Benutzer:J%C3%B6rg_Kunze">
				<div style="display: block; float: left; margin-right: 10px; overflow:hidden; border: 0px solid #000000; width: 120px;">
					<img src="http://piratenpartei-oldenburg.de/cms/wp-content/uploads/2012/04/jörg-225x300.jpg" width="120px">
				</div>
			</a>
			<div style="display: block; float: left; margin-right: 10px; overflow:hidden; border: 0px solid #000000; width: 140px;">
				<h3>Direktkandidat Jörg-Hendrik Kunze</h3>
				<p>Wahlkreis <b>63</b></p><br>
				<p>37 Jahre alt, Diplom Verwaltungs-Betriebswirt und Pirat seit 2009.</p><br>
				<p><a href="http://piratenpartei-oldenburg.de/data/wahlen/landtagswahl/2013/formblatt_unterstuetzerunterschrift_ltwnds13_wk63.pdf">Formular für die Unterstützerunter-<br>schrift herunterladen</a></p>
			</div>
			<br style="clear: both;">-->
		</div>

		<div id="links">
			<div id="sidebar_left" class="sidebar">
				<?php require( 'sidebar_left.php' ); ?>
			</div>
		</div>
