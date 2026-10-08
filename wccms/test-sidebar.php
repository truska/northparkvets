<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Responsive Sidebar Test</title>

    <!-- Bootstrap 5 CSS -->
    <?php require_once __DIR__ . '/include/bootstrap-css.php'; ?>

    <!-- FontAwesome -->
    <script src="https://kit.fontawesome.com/752adbebf9.js" crossorigin="anonymous"></script>

    <style>
        /* 🎯 HEADER (Fixed at Top) */
        .header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 60px;
            background: #ffffff;
            color: #333;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            z-index: 1000;
        }

        /* 🎯 SIDEBAR */
        .sidebar {
            position: fixed;
            left: 0;
            top: 60px;
            height: calc(100% - 60px);
            width: 250px; /* Default expanded */
            background: #2c3e50;
            color: white;
            padding-top: 10px;
            transition: all 0.3s ease-in-out;
        }

        /* Sidebar Collapsed */
        .sidebar.collapsed {
            width: 60px;
        }

        /* Hide Text in Collapsed Mode */
        .sidebar.collapsed .nav-link span {
            display: none;
        }

        /* Keep Icons Visible */
        .sidebar.collapsed .nav-link i {
            text-align: center;
            width: 100%;
        }

        /* 🎯 Sidebar Links */
        .sidebar .nav-link {
            color: white;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            transition: all 0.3s ease-in-out;
        }

        /* Hover Effect */
        .sidebar .nav-link:hover {
            background: #34495e;
            color: #f1c40f;
        }

        /* Active State */
        .sidebar .nav-item.active .nav-link {
            background: #2980b9;
            color: white;
        }

        /* 🎯 SUBMENU */
        .sidebar .collapse {
            background: #3d4e5a;
            padding-left: 10px; /* Indent submenu */
        }

        /* Ensure dropdown aligns properly */
        .sidebar .collapse .nav-link {
            padding-left: 30px; /* Proper indentation */
            color: #dfe6e9;
        }

        /* Submenu Hover */
        .sidebar .collapse .nav-link:hover {
            background: #576574;
            color: #f1c40f;
        }

        /* 🎯 MAIN CONTENT */
        .main-content {
            margin-left: 250px;
            padding: 20px;
            transition: all 0.3s ease-in-out;
        }

        /* When Sidebar is Collapsed */
        .sidebar.collapsed + .main-content {
            margin-left: 60px;
        }

        /* 🎯 MOBILE: Sidebar Hidden by Default */
        @media (max-width: 768px) {
            .sidebar {
                left: -250px;
            }
            .sidebar.show {
                left: 0;
            }
            .main-content {
                margin-left: 0;
            }
        }

        /* 🎯 TOGGLE BUTTON */
        .btn-toggle {
            background: #e74c3c;
            border: none;
            color: white;
            font-size: 24px;
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            transition: all 0.3s ease-in-out;
        }

        .btn-toggle:hover {
            background: #c0392b;
        }

        .btn-toggle.active {
            background: #27ae60;
        }

        .btn-toggle.active i {
            transform: rotate(180deg);
        }

        /* Overlay for Mobile */
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 998;
            display: none;
        }

        .overlay.show {
            display: block;
        }
    </style>
<?php require_once __DIR__ . '/include/bootstrap-css.php'; ?>
</head>
<body>
<?php require_once __DIR__ . '/include/dev-banner.php'; ?>

    <!-- Overlay for mobile -->
    <div class="overlay" id="overlay"></div>

    <!-- HEADER -->
    <header class="header">
        <button class="btn-toggle" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>
        <a href="#" class="logo fw-bold">W<span>it</span>eCanvas</a>
    </header>

    <!-- SIDEBAR -->
    <aside id="sidebar" class="sidebar">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center" data-bs-toggle="collapse" href="#adminMenu" role="button">
                    <i class="fas fa-user-shield"></i>
                    <span class="ms-2">Admin</span>
                    <i class="fas fa-chevron-down ms-auto"></i>
                </a>
                <div class="collapse" id="adminMenu">
                    <ul class="nav flex-column">
                        <li class="nav-item"><a class="nav-link" href="#">Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link" href="#">Settings</a></li>
                    </ul>
                </div>
            </li>

            <li class="nav-item">
                <a class="nav-link d-flex align-items-center" data-bs-toggle="collapse" href="#contactForms" role="button">
                    <i class="fas fa-users"></i>
                    <span class="ms-2">Contact Forms</span>
                    <i class="fas fa-chevron-down ms-auto"></i>
                </a>
                <div class="collapse" id="contactForms">
                    <ul class="nav flex-column">
                        <li class="nav-item"><a class="nav-link" href="#">Edit Contact Forms</a></li>
                        <li class="nav-item"><a class="nav-link" href="#">New Contact Form</a></li>
                    </ul>
                </div>
            </li>
        </ul>
    </aside>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        <h1>Dashboard</h1>
        <p>Main content goes here...</p>
    </div>

    <!-- Bootstrap 5 JS -->
    <?php require_once __DIR__ . '/include/bootstrap-js.php'; ?>

    <!-- Sidebar Toggle Script -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            console.log("🚀 Sidebar toggle initialized.");

            const sidebar = document.querySelector("#sidebar");
            const toggleButton = document.querySelector("#sidebarToggle");
            const mainContent = document.querySelector(".main-content");
            const overlay = document.querySelector("#overlay");

            toggleButton.addEventListener("click", function () {
                if (window.innerWidth <= 768) {
                    sidebar.classList.toggle("show");
                    overlay.classList.toggle("show");
                } else {
                    sidebar.classList.toggle("collapsed");
                    mainContent.classList.toggle("expanded");
                }

                toggleButton.classList.toggle("active");
            });

            overlay.addEventListener("click", function () {
                sidebar.classList.remove("show");
                overlay.classList.remove("show");
                toggleButton.classList.remove("active");
            });
        });
    </script>

<?php require_once __DIR__ . '/include/bootstrap-js.php'; ?>
</body>
</html>
