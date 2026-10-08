<!DOCTYPE html>
<head>
    <?php
        error_reporting(0); // Turn off error reporting
        include ('setting/main-top-files.php'); // Loads Session, Database Connections, functions, and more
        include("include/header-code.php"); // Loads Bootstrap 5

        // Requires `frm` and `id` parameters on the URL
        if (!$formnumber = securityCheck($_GET['frm'], 'number')) {
            die('Error in the form'); // Exit if invalid
        }
        if (!$recordnumber = securityCheck($_GET['id'], 'number')) {
            die('Error in the id'); // Exit if invalid
        }
    ?>
<?php require_once __DIR__ . '/include/bootstrap-css.php'; ?>
</head>

<body>
<?php require_once __DIR__ . '/include/dev-banner.php'; ?>
    <?php
    include("include/header.php");
    include("include/sidebar.php");
    ?>

    <section id="main-content">
        <section class="wrapper site-min-height">
            <div class="row">
                <div class="col-12">
                    <h1>North Park Vets Exports</h1>
                    
                    <h2 class="text-danger">ARCHIVE Import Batches</h2>
                    <p>Shows Last 20 Import Batches</p>
                    <select id="import-dropdown" class="form-control" onchange="loadModal(this)">
                        <option value="">Select Import Routine</option>
                        <?php
                        // Fetch the last 20 import routines
                        $query = "SELECT id, name, created FROM product_import_log ORDER BY created DESC LIMIT 20";
                        $result = $conn->query($query);

                        while ($row = $result->fetch_assoc()) {
                            $id = $row['id'];
                            $name = $row['name'];
                            $created = $row['created'];
                            echo "<option value='$id'>$id - $name [$created]</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>

            <!-- Modal -->
            <div id="archiveModal" class="modal fade" tabindex="-1" aria-labelledby="archiveModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="archiveModalLabel">Archive Import Batch</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div id="modal-content">
                                <!-- Records loaded dynamically via JavaScript -->
                            </div>
                            
                                <h1 class="text-danger">This will archive records</h1>
                                <H2 class="text-danger">ARE YOU SURE?</H2>
                            
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger" id="archive-button" onclick="archiveRecords()">Confirm Archive</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>

            <?php
            include("include/footer-code.php"); // Includes Bootstrap JS files
            include("include-tinymce.php");
            ?>
        </section>
    </section>


    <script>
    function loadModal(dropdown) {
        const id = dropdown.value;
        if (!id) return; // Exit if no ID is selected

        // Fetch data using AJAX
        fetch(`ajax_get_import_records.php?id=${id}`)
            .then(response => {
                if (!response.ok) throw new Error("Failed to fetch records");
                return response.json();
            })
            .then(data => {
                const modalTitle = document.getElementById("archiveModalLabel");
                modalTitle.textContent = `Archive Import Batch ${id}`;

                const totalRecords = data.total_records; // Records to be archived
                const archivedRecords = data.archived_records; // Already archived records
                const modalContent = document.getElementById("modal-content");

                let message = "";

                if (totalRecords === 0 && archivedRecords > 0) {
                    // Case: No records to archive, but some are already archived
                    message = `
                        <p class="text-danger"><strong>No records to Archive</strong></p>
                        <p>${archivedRecords} Records already Archived</p>
                    `;
                    modalContent.innerHTML = message;
                } else {
                    // Case: Some records will be archived
                    message = `<p class="text-danger"><strong>This will archive ${totalRecords} records.</strong></p>`;

                    if (archivedRecords > 0) {
                        message += `<p>${archivedRecords} Records already archived</p>`;
                    }

                    let tableRows = [];

                    if (totalRecords > 15) {
                        // Show first 5 records
                        tableRows.push(...data.records.slice(0, 5).map(record =>
                            `<tr><td>${record.id}</td><td>${record.name}</td><td>${record.created}</td></tr>`
                        ));

                        // Spacer row with dots
                        tableRows.push(`<tr><td colspan="3" class="text-center text-muted">. . . . . . . . . .</td></tr>`);

                        // Show last 5 records
                        tableRows.push(...data.records.slice(-5).map(record =>
                            `<tr><td>${record.id}</td><td>${record.name}</td><td>${record.created}</td></tr>`
                        ));
                    } else {
                        // Show all records if 15 or fewer
                        tableRows = data.records.map(record =>
                            `<tr><td>${record.id}</td><td>${record.name}</td><td>${record.created}</td></tr>`
                        );
                    }

                    // Update modal content
                    modalContent.innerHTML = `
                        ${message}
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Created</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${tableRows.join('')}
                            </tbody>
                        </table>
                    `;
                }

                // Open the modal
                const modal = new bootstrap.Modal(document.getElementById('archiveModal'));
                modal.show();
            })
            .catch(error => {
                alert("Error loading records: " + error.message);
            });
    }




        function archiveRecords() {
            const id = document.getElementById("import-dropdown").value;
            const button = document.getElementById("archive-button");
            button.disabled = true;

            // Archive records via AJAX
            fetch(`ajax_import_archive_batch.php`, {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `id=${id}`
            })
                .then(response => {
                    if (!response.ok) throw new Error("Failed to archive records");
                    return response.text();
                })
                .then(result => {
                    alert(result); // Alert success message
                    closeModal();
                    button.disabled = false;
                })
                .catch(error => {
                    alert("Error archiving records: " + error.message);
                    button.disabled = false;
                });
        }
    </script>
<?php require_once __DIR__ . '/include/bootstrap-js.php'; ?>
</body>
</html>

<!-- END import_archive_batchv4 -->
