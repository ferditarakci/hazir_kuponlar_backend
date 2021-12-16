<!DOCTYPE html>
<html lang="tr" class="noAds">
	<head>
		<!-- Global -->
		<meta charset="utf-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=Edge" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no" />
		<meta name="google-site-verification" content="oGUOrBzn_hh45jP6O9U0PLhmDyIikSQLGUlxqdrMl34" />
		<link href="<?php echo SITE_URL ?>/assets/images/favicon.png" rel="icon" type="image/png" />

		<!-- Seo -->
		<title><?php echo $site['title'] ?></title>
<?php if (!empty($site['description'])) { ?>
		<meta name="description" content="<?php echo $site['description'] ?>" />
<?php } ?>
		<link rel="canonical" href="<?php echo SITE_URL . $site['url_path'] ?>" />

		<!-- Styles -->
		<link href="<?php echo SITE_URL ?>/assets/css/reset.css" rel="stylesheet" />
		<link href="<?php echo SITE_URL ?>/assets/css/generic.css<?php echo $version ?>" rel="stylesheet" />
		<link href="<?php echo SITE_URL ?>/assets/css/responsive.css<?php echo $version ?>" rel="stylesheet" />
		<link href="<?php echo SITE_URL ?>/assets/css/sweetalert2.css" rel="stylesheet" />
		<link href="https://fonts.googleapis.com/css?family=Open+Sans:400,600,700,800&display=swap&subset=latin-ext" rel="stylesheet" />

		<!-- Script -->
		<script>
			const site = {
				login: <?php if (isset($user['id'])) echo 'true'; else echo 'false'; ?>

			}
		</script>
		<script src="<?php echo SITE_URL ?>/assets/js/jquery-1.11.2.min.js"></script>
		<script src="<?php echo SITE_URL ?>/assets/js/template7.min.js"></script>
		<script src="<?php echo SITE_URL ?>/assets/js/jquery.tinyscrollbar.js"></script>
		<script src="<?php echo SITE_URL ?>/assets/js/sweetalert2.min.js"></script>
		<script src="<?php echo SITE_URL ?>/assets/js/common.js<?php echo $version ?>"></script>
<?php

require_once(__ROOT__ . '/inc/analytics.php');

?>
	</head>
	<body>

		<div id="outer-wrap">
			<div id="inner-wrap">
				<div id="site-wrap">

					<header id="header" class="clearfix">
						<!-- <div class="container top-bar">
							<div class="hkad w1070">
								<a href="#" rel="nofollow" target="_blank">
									<img alt="" src="images/1070x50_2.jpg" />
								</a>
							</div>
						</div> -->

						<div class="container bottom-bar">
							<h1 class="page-title hide"><?php echo $site['title'] ?></h1>
							<div class="logo">
								<a href="./"></a>
							</div>
							<nav class="main-nav">
								<ul class="nav-1 x6">
									<li<?php if ($site['url_path'] == '/' OR $site['url_path'] == '/banko-kuponlar') echo ' class="selected"' ?>><a href="<?php echo SITE_URL ?>/"><span>BANKO <span>KUPONLAR</span></span></a></li>
									<li<?php if ($site['url_path'] == '/golcu-kuponlar') echo ' class="selected"' ?>><a href="<?php echo SITE_URL ?>/golcu-kuponlar"><span>GOLCÜ <span>KUPONLAR</span></span></a></li>
									<li<?php if ($site['url_path'] == '/surpriz-kuponlar') echo ' class="selected"' ?>><a href="<?php echo SITE_URL ?>/surpriz-kuponlar"><span>SÜRPRİZ <span>KUPONLAR</span></span></a></li>
									<li<?php if ($site['url_path'] == '/sistem-3-4-kuponlari') echo ' class="selected"' ?>><a href="<?php echo SITE_URL ?>/sistem-3-4-kuponlari"><span>SİSTEM 3,4 <span>KUPONLARI</span></span></a></li>
									<li<?php if ($site['url_path'] == '/sistem-4-5-6-kuponlari') echo ' class="selected"' ?>><a href="<?php echo SITE_URL ?>/sistem-4-5-6-kuponlari"><span>SİSTEM 4,5,6 <span>KUPONLARI</span></span></a></li>
									<li<?php if ($site['url_path'] == '/tutan-kuponlar') echo ' class="selected"' ?>><a href="<?php echo SITE_URL ?>/tutan-kuponlar"><span>TUTAN <span>KUPONLAR</span></span></a></li>
								</ul>
							</nav>
						</div>

						<div class="container res-bar">
							<div class="grid_9">
								<div class="logo">
									<a href="./"></a>
								</div>
							</div>
							<div class="grid_3">
								<a href="#" class="nav-button" id="nav-button">
									<i class="lines"></i>
								</a>
							</div>
						</div>

						<div id="navigation">
							<div class="logo">
								<a href="./"></a>
							</div>
							<nav class="mobil-nav">
								<ul>
									<li<?php if ($site['url_path'] == '/' OR $site['url_path'] == '/banko-kuponlar') echo ' class="selected"' ?>><a href="<?php echo SITE_URL ?>/">BANKO KUPONLAR</a></li>
									<li<?php if ($site['url_path'] == '/golcu-kuponlar') echo ' class="selected"' ?>><a href="<?php echo SITE_URL ?>/golcu-kuponlar">GOLCÜ KUPONLAR</a></li>
									<li<?php if ($site['url_path'] == '/surpriz-kuponlar') echo ' class="selected"' ?>><a href="<?php echo SITE_URL ?>/surpriz-kuponlar">SÜRPRİZ KUPONLAR</a></li>
									<li<?php if ($site['url_path'] == '/sistem-3-4-kuponlari') echo ' class="selected"' ?>><a href="<?php echo SITE_URL ?>/sistem-3-4-kuponlari">SİSTEM 3,4 KUPONLARI</a></li>
									<li<?php if ($site['url_path'] == '/sistem-4-5-6-kuponlari') echo ' class="selected"' ?>><a href="<?php echo SITE_URL ?>/sistem-4-5-6-kuponlari">SİSTEM 4,5,6 KUPONLARI</a></li>
									<li<?php if ($site['url_path'] == '/tutan-kuponlar') echo ' class="selected"' ?>><a href="<?php echo SITE_URL ?>/tutan-kuponlar">TUTAN KUPONLAR</a></li>
									<li class="small<?php if ($site['url_path'] == '/blog') echo ' selected' ?>"><a href="<?php echo SITE_URL ?>/blog">Blog</a></li>
									<li class="small<?php if ($site['url_path'] == '/hakkimizda') echo ' selected' ?>"><a href="<?php echo SITE_URL ?>/hakkimizda">Hakkımızda</a></li>
									<li class="small<?php if ($site['url_path'] == '/iletisim') echo ' selected' ?>"><a href="<?php echo SITE_URL ?>/iletisim">İletişim</a></li>
								</ul>
							</nav>
							<div class="socials"></div>
						</div>
					</header>