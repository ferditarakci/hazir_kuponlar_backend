<?php

// HEADER BANNER
if ($show_ads) {
	$ads_result = $sql->query("select * from tk_banners where active=1 AND banner_name like 'mobile-top%' AND name!='' order by banner_name");

	if ($sql->numRows($ads_result) > 0) {
		echo '<section class="hkads mb clearfix">
		<div class="container_12">
			<div class="grid_12">';

		while ($row = $sql->fetchRow($ads_result, MYSQL_ASSOC)) {
			if ($row["iframe"] != "") {
				//echo "<div class=\"tkad\">".$row["iframe"]."</div>";
			} else {
				echo '<div class="hkad">
					<a href="' . ads_redirect($row["url"], SITE_URL . '/tk.php?id=' . ($row["id"] + 1000)) . '" rel="nofollow" target="_blank">
						<img alt="' . $row["name"] . '" src="' . SITE_URL . '/img/affiliate/' . $row["picture"] . '" />
					</a>
				</div>';
			}
		}

		echo '</div>
		</div>
	</section>';
	}
}
