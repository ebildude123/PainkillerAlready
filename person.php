<?php

	require_once("config.php");
	require_once("class.person.php");
	
	if (isset($_GET["person"])) {
		try {
			$Person = new Person($con);
			$Person->initWithID($_GET["person"]);
		} catch (Exception $e) {
			die("No person found");
		}
	} else {
		die("No person found");
	}
	
	if (isset($_SERVER["HTTP_USER_AGENT"]) && (strpos($_SERVER["HTTP_USER_AGENT"], "MSIE") !== false)) header("X-UA-Compatible: IE=edge,chrome=1");
	
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<!-- Meta -->
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
		<meta name="description" content="<?php echo $Person->getOverview(); ?>">
		<link rel="canonical" href="<?php echo $domain . "person/" . $Person->getID(); ?>">
		<title><?php echo $Person->getName(); ?> &middot; Painkiller Already Archive</title>
		
		<!-- Icons -->
		<link rel="apple-touch-icon" sizes="57x57" href="<?php echo $domain; ?>apple-touch-icon-57x57.png">
		<link rel="apple-touch-icon" sizes="114x114" href="<?php echo $domain; ?>apple-touch-icon-114x114.png">
		<link rel="apple-touch-icon" sizes="72x72" href="<?php echo $domain; ?>apple-touch-icon-72x72.png">
		<link rel="apple-touch-icon" sizes="144x144" href="<?php echo $domain; ?>apple-touch-icon-144x144.png">
		<link rel="apple-touch-icon" sizes="60x60" href="<?php echo $domain; ?>apple-touch-icon-60x60.png">
		<link rel="apple-touch-icon" sizes="120x120" href="<?php echo $domain; ?>apple-touch-icon-120x120.png">
		<link rel="apple-touch-icon" sizes="76x76" href="<?php echo $domain; ?>apple-touch-icon-76x76.png">
		<link rel="apple-touch-icon" sizes="152x152" href="<?php echo $domain; ?>apple-touch-icon-152x152.png">
		<link rel="icon" sizes="196x196" type="image/png" href="<?php echo $domain; ?>favicon-196x196.png">
		<link rel="icon" sizes="160x160" type="image/png" href="<?php echo $domain; ?>favicon-160x160.png">
		<link rel="icon" sizes="96x96" type="image/png" href="<?php echo $domain; ?>favicon-96x96.png">
		<link rel="icon" sizes="32x32" type="image/png" href="<?php echo $domain; ?>favicon-32x32.png">
		<link rel="icon" sizes="16x16" type="image/png" href="<?php echo $domain; ?>favicon-16x16.png">
		<meta name="msapplication-TileColor" content="#ffffff">
		<meta name="msapplication-TileImage" content="<?php echo $domain; ?>mstile-144x144.png">
		
		<!-- Google+ -->
		<link rel="publisher" href="https://plus.google.com/107397414095793132493">
		
		<!-- Open Graph -->
		<meta property="og:image" content="<?php echo $domain; ?>img/people/<?php echo $Person->getID(); ?>a.png">
		<meta property="og:site_name" content="Painkiller Already Archive">
		<meta property="og:type" content="profile">
		<meta property="og:title" content="<?php echo $Person->getName(); ?>">
		<meta property="og:description" content="<?php echo $Person->getOverview(); ?>">
		<meta property="og:url" content="<?php echo $domain . "person/" . $Person->getID(); ?>">
		<meta property="fb:profile_id" content="<?php echo $Person->getFacebook(); ?>">
		<meta property="profile:first_name" content="<?php echo $Person->getFirstName(); ?>">
		<meta property="profile:last_name" content="<?php echo $Person->getLastName(); ?>">
		<meta property="profile:username" content="<?php echo $Person->getName(); ?>">
		<meta property="profile:gender" content="<?php echo ($Person->getGender() != "1" ? "female" : "male") ?>">
		
		<!-- CSS -->
		<link rel="stylesheet" type="text/css" href="<?php echo $domain; ?>css/main.css?ver=<?php echo $commit_count; ?>">
		<link rel="stylesheet" type="text/css" href="<?php echo $domain; ?>css/person.css?ver=<?php echo $commit_count; ?>">
		<link rel="stylesheet" type="text/css" href="<?php echo $domain; ?>css/fontawesome.css?ver=<?php echo $commit_count; ?>">
		
		<!-- IE8 -->
		<!--[if lt IE 9]>
			<link rel="stylesheet" href="<?php echo $domain; ?>css/fontawesome-ie7.css">
			<script src="//cdnjs.cloudflare.com/ajax/libs/html5shiv/3.7/html5shiv.js"></script>
			<script src="//cdnjs.cloudflare.com/ajax/libs/respond.js/1.4.2/respond.js"></script>
		<![endif]-->
	</head>
	<body>
		<aside class="sidebar">
			<nav id="sidebar">
				<div class="search-form"><input class="search-field" type="search" id="search-field" name="search" placeholder="Search"></div>
				<h3 id="episodes_title">Episodes</h3>
				<ul>
<?php
	foreach ($Podcast->getEpisodes() as $episode) {
?>
					<li data-episode="<?php echo $episode["Identifier"]; ?>">
						<a href="<?php echo $domain; ?>episode/<?php echo $episode["Number"]; ?>">#<?php echo $episode["Number"]; ?></a>
					</li>
<?php
	}
?>
				</ul>
			</nav>
		</aside>
		<section class="main">
			<header>
				<a href="#" class="toggle-menu icon-reorder"></a>
				<h1>Painkiller Already Archive</h1>
			</header>
			<div id="container">
				<div id="image">
					<img id="person-image" alt="<?php echo $Person->getName(); ?>" title="<?php echo $Person->getName(); ?>" src="<?php echo $domain; ?>img/people/<?php echo $Person->getID(); ?>a.png" />
				</div>
				<div id="details">
					<div id="overview">
						<h2 class="detail-header"><?php echo $Person->getName(); ?></h2>
						<p id="person-overview"><?php echo $Person->getOverview(); ?></p>
					</div>
					<div id="social-icons">
						<h2 class="detail-header">Social</h2>
<?php
	if ($Person->getYouTube() != "") {
?><a href="https://www.youtube.com/channel/<?php echo $Person->getYouTube(); ?>"><img alt="YouTube" title="YouTube" src="<?php echo $domain; ?>img/youtube.png"></a><?php
	}
	if ($Person->getTwitch() != "") {
?><a href="http://www.twitch.tv/<?php echo $Person->getTwitch(); ?>"><img alt="Twitch" title="Twitch" src="<?php echo $domain; ?>img/twitch.png"></a><?php
	}
	if ($Person->getFacebook() != "") {
?><a href="https://www.facebook.com/<?php echo $Person->getFacebook(); ?>"><img alt="Facebook" title="Facebook" src="<?php echo $domain; ?>img/facebook.png"></a><?php
	}
	if ($Person->getTwitter() != "") {
?><a href="https://twitter.com/account/redirect_by_id/<?php echo $Person->getTwitter(); ?>"><img alt="Twitter" title="Twitter" src="<?php echo $domain; ?>img/twitter.png"></a><?php
	}
	if ($Person->getReddit() != "") {
?><a href="http://www.reddit.com/user/<?php echo $Person->getReddit(); ?>"><img alt="reddit" title="reddit" src="<?php echo $domain; ?>img/reddit.png"></a><?php
	}
	if ($Person->getGooglePlus() != "") {
?><a href="https://plus.google.com/<?php echo $Person->getGooglePlus(); ?>"><img alt="Google Plus" title="Google Plus" src="<?php echo $domain; ?>img/googleplus.png"></a><?php
	}
?>
					</div>
				</div>
				<div style="clear:both;"></div>
				<div id="youtube_videos">
					<h2>YouTube Videos</h2>
				</div>
			</div>
		</section>
		<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
		<script type="text/javascript">var domain = "<?php echo $domain; ?>";</script>
		<script type="text/javascript" src="<?php echo $domain; ?>js/main.js?ver=<?php echo $commit_count; ?>"></script>
		<!--[if lt IE 9]>
			<script type="text/javascript">
				$(document).ready(function(){
					$(".toggle-menu").click(function(){
						$(".main").css({"display": "none"});
						$(".main").css({"display": "block"});
					});
				});
			</script>
		<![endif]-->
	</body>
</html>