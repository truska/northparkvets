<?php
// START footer-code new 20250113 -->
?>

<!-- jQuery (Required by DataTables) -->
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

<!-- DataTables Core JS -->

<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<!-- DataTables Responsive JS -->
<script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.4.1/js/responsive.bootstrap5.min.js"></script>

<!-- DataTables Buttons Extensions (Export features) -->
 
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

<!-- Bootstrap 5 Core JS (Reload it at the end to ensure proper execution) -->
<!-- Bootstrap 5 Core JS -->
<?php require_once __DIR__ . '/bootstrap-js.php'; ?>

<script>
function getBootstrapVersion() {
    if (typeof bootstrap !== 'undefined') {
        // Bootstrap 5
        return bootstrap.Tooltip.VERSION;
    } else if (typeof $.fn.tooltip !== 'undefined') {
        // Bootstrap 3 or 4
        return $.fn.tooltip.Constructor.VERSION;
    }
    return 'Unknown';
}

fetch('save_bootstrap_version.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ version: getBootstrapVersion() })
});
</script>


<!-- Sidebar Menu JS -->
 <!--
<script src="<?php echo $baseURL; ?>/wccms/js/sidebar-menu.js"></script>
-->


<!-- Sidebar Initialization -->
 <!--
<script>
  $(document).ready(function () {
    // Collapse all menus and submenus on load
    $('.menu-i').removeClass('active');
    $('.sub-menu').hide();

    // Toggle menu on click
    $('.menu-i > a').on('click', function (e) {
      e.preventDefault(); // Prevent default link behavior
      e.stopPropagation(); // Stop event bubbling

      let menu = $(this).parent(); // Get the clicked menu item
      let submenu = menu.find('.sub-menu');

      if (menu.hasClass('active')) {
        // If the menu is active, collapse it
        menu.removeClass('active');
        submenu.slideUp();
      } else {
        // Collapse all other menus
        $('.menu-i').removeClass('active');
        $('.sub-menu').slideUp();

        // Expand the clicked menu
        menu.addClass('active');
        submenu.slideDown();
      }
    });

    // Prevent submenu clicks from closing the menu
    $('.sub-menu a').on('click', function (e) {
      e.stopPropagation(); // Stop event bubbling
    });
  });
</script>

-->

<!-- Optional: Initialize DataTables -->
<script>
  $(document).ready(function () {
    $('#example').DataTable({
      dom: 'Bfrtip', // Add Buttons to the UI
      buttons: ['copy', 'csv', 'excel', 'pdf', 'print']
    });
  });
</script>

<?php
// END footer-code -->
?>
