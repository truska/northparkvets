<?php
// Function to generate sidebar menu (static for now)
function generateMenu() {
    echo '
        <li class="nav-item">
            <a class="nav-link d-flex align-items-center" href="#">
                <i class="fas fa-home"></i>
                <span class="ms-2">Admin</span>
            </a>
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
        </li>';
}

// Call the function
generateMenu();
?>