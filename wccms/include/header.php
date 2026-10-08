<?php
    // <!-- START header -->
    $user_role = $USER->getUserRole();
    $MENU = new Menu($user_role['level']);
    $menu = $MENU->getMenu();
?>
<style>
    /* Hide the horizontal navbar on desktop */
    @media (min-width: 992px) { 
        #navbarContent {
            display: none !important;
        }
    }

    @media (max-width: 991px) { 
        .dropdown-menu {
            position: static !important; /* Override Bootstrap’s positioning */
            display: none; /* Ensure dropdown is hidden by default */
            width: 100%; /* Full width for mobile */
        }

        .dropdown-menu.show {
            display: block !important; /* Show menu when toggled */
        }
    }

    /* Sidebar hidden state */
    #sidebar.collapsed {
        display: none !important;
    }

    /* Adjust content area when sidebar is hidden */
    @media (min-width: 992px) {
        #main-content {
            transition: margin-left 0.3s ease-in-out;
        }
    }
</style>
<header class="header white-bg">
    <nav class="navbar navbar-expand-lg ">
        <div class="container-fluid">

            <!-- ✅ Burger Button for Mobile Menu (Hidden on Desktop) -->
             <!--
            <button class="navbar-toggler d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            -->

            <!-- ✅ Sidebar Toggle (Keep on Desktop & Mobile) -->
            <div class="sidebar-toggle-box">
            <button class="navbar-toggler d-lg-block" type="button" id="sidebarToggle" aria-label="Toggle sidebar">
    <span class="navbar-toggler-icon"></span>
</button>
                <!--    <button class="btn btn-dark" id="sidebarToggle">
                        <i class="fa fa-bars"></i>
                    </button>
                -->
            </div>

            <!-- ✅ Site Logo -->
            <a href="dashboard.php" class="navbar-brand logo">w<span>IT</span>eCanvas</a>

            <h2 class="mx-auto d-flex justify-content-center text-dark"><?php echo $prefs["prefCompanyName"]; ?></h2>

            <!-- ✅ User Info Section (Always Visible) -->
            <div class="ms-auto d-flex align-items-center">
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <?php
                            if ($user['image'] == "") {
                                echo '<img class="logged-img-cms" alt="" src="img/avatar1_small.jpg" style="max-width:50px;">';
                            } else {
                                echo '<img class="logged-img-cms" alt="" src="../filestore/images/content/' . $user['image'] . '" style="max-width:50px;">';
                            }
                            ?>
                            <span class="username d-none d-lg-inline">Logged in as: <b><?php echo $user['firstname'] . " " . $user['surname']; ?></b></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="logout.php"><i class="fa fa-key"></i> Log Out</a></li>
                        </ul>
                    </li>
                </ul>
            </div>

            <!-- ✅ Collapsible Navbar Menu (Hidden on Desktop) -->
            <div class="collapse navbar-collapse d-lg-none" id="navbarContent">
                <ul class="navbar-nav ms-auto">
                    <?php
                    if ($menu) {
                        foreach ($menu as $option) {
                            $submenu = $MENU->getSubMenu($option['section']);
                            $icon = ($option["icon"] != '0') ? $MENU->getIcon($option["icon"]) : 'fa fa-list-alt';

                            if (count($submenu) > 0) {
                                echo "<li class='nav-item dropdown'>";
                                echo "<a class='nav-link dropdown-toggle' href='#submenu-".$option['id']."' role='button' data-bs-toggle='collapse' aria-expanded='false'>";
                                echo "<i class='".$icon."'></i> ".$option["title"]."</a>";
                                echo "<ul class='collapse dropdown-menu' id='submenu-".$option['id']."'>";
                                foreach ($submenu as $suboption) {
                                    echo "<li><a class='dropdown-item' href='".$suboption["url"]."?frm=".$suboption["form"].$suboption["var1"]."' target='".$suboption["target"]."'>".$suboption["title"]."</a></li>";
                                }
                                echo "</ul></li>";
                            } else {
                                echo "<li class='nav-item'><a class='nav-link' href='".$option["url"]."'><i class='".$icon."'></i> ".$option["title"]."</a></li>";
                            }
                        }
                    }
                    ?>
                </ul>
            </div>
        </div>
    </nav>
</header>


<?php // Navigation is handled once by cms-navigation.js. ?>
