<?php require_once __DIR__ . '/bootstrap-assets.php'; ?>
<link rel="stylesheet" href="/wccms/css/cms-navigation.css?v=1">
<!-- START sidebar -->
<!-- TruskaCMS ver 3.1.0 -->

<?php
$userlevel = $user_role['level'];

// Generate menu
$MENU = new Menu($userlevel);
$menu = $MENU->getMenu();
?>

<aside>
	<div id='sidebar' class='wccms-sidebar' aria-label='CMS navigation'>
		<ul class='sidebar-menu' id='nav-accordion'>
			<?php
			if ($menu) {
				foreach ($menu as $option) {
					$submenu = $MENU->getSubMenu($option['section']);
					$icon = ($option["icon"] != '0') ? $MENU->getIcon($option["icon"]) : 'fa fa-list-alt';

					if (count($submenu) > 0) { // Dropdown menu
						echo "<li id='wccms-menu-". $option['id'] ."' class='menu-i'>";
						echo "<a href='#wccms-submenu-".$option['id']."' class='wccms-sidebar-link' data-wccms-submenu aria-expanded='false' aria-controls='wccms-submenu-".$option['id']."'>";
						echo "<i class='".$icon."'></i> <span>".$option["title"]."</span>";
						echo "<i class='fa fa-chevron-down wccms-sidebar-chevron'></i></a>"; // Arrow icon
						echo "<ul class='wccms-sidebar-submenu' hidden id='wccms-submenu-".$option['id']."'>";

						foreach ($submenu as $suboption) {
							echo "<li id='wccms-item-". $suboption['id'] ."' class='submenu-i'>";
							echo "<a href='".$suboption["url"]."?frm=".$suboption["form"].$suboption["var1"]."' target='".$suboption["target"]."' class='wccms-sidebar-link'>".$suboption["title"]."</a>";
							echo "</li>";
						}

						echo "</ul></li>";
					} else { // Non-dropdown menu
						echo "<li id='wccms-menu-". $option['id'] ."' class='menu-i'>";
						echo "<a href='".$option["url"]."' class='wccms-sidebar-link'>";
						echo "<i class='".$icon."'></i><span>".$option["title"]."</span>";
						echo "</a></li>";
					}
				}
			}
			?>
		</ul>

		<?php
		echo "<div class='sidebarinfo'>";
			echo "<br>Site: " . getSiteName($prefs);
			echo "<br>User: " . $user["firstname"] . " " . $user["surname"];
			echo "<br>Username: " . $user["username"];
			echo "<br>Role: " . $user["userrole"];
			echo "<br>User IP: " . $_SERVER['REMOTE_ADDR'];
			echo "<hr>";

			if ($prefs['prefDropboxRequestURL']) {
				echo "<a href='" . $prefs['prefDropboxRequestURL'] . "' target='_blank'>DropBox File Request Link</a>";
				echo "<hr>";
			}

			echo "<img src='" . $baseURL . "/wccms/img/wite-canvas-logo-sq-no-tag-150.jpg' style='max-width:60px'><br>";
			echo "&copy; copyright witecanvas.com 2020 - " . date("Y") . "<br>";
			echo "Ver: 3.0.0<br>";
			echo "Bootstrap ver: " . htmlspecialchars(CMS_BOOTSTRAP_VERSION). "<br>";
		echo "</div>";
		?>
	</div>
</aside>


<script src="/wccms/js/cms-navigation.js?v=1" defer></script>
