<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expandable Notes Row</title>
    <style>
        .notes-row {
            display: none;
            background-color: #f9f9f9;
        }
        .notes-content {
            padding: 10px;
            font-size: 0.9rem;
            color: #555;
        }
    </style>
<?php require_once __DIR__ . '/include/bootstrap-css.php'; ?>
</head>
<body>
<?php require_once __DIR__ . '/include/dev-banner.php'; ?>
    <table border="1" cellpadding="10" cellspacing="0">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>Sample Item</td>
                <td>
                    <button class="toggle-notes" data-notes-id="1">Show Notes</button>
                </td>
            </tr>
            <tr class="notes-row" id="notes-1">
                <td colspan="3" class="notes-content"><p>Another detailed note for this item.<p>
                <p>Another detailed note for this item.<p>
                    <h1>heading</h1>
                    <p><strong>bold text on 4th line</p></td>
            </tr>
            <tr>
                <td>2</td>
                <td>Another Item</td>
                <td>
                    <button class="toggle-notes" data-notes-id="2">Show Notes</button>
                </td>
            </tr>
            <tr class="notes-row" id="notes-2">
                <td colspan="3" class="notes-content"><p>Another detailed note for this item.<p>
                <p>Another detailed note for this item.<p>
                    <h1>heading</h1>
                    <p><strong>bold text on 4th line</p>


                </td>
            </tr>
        </tbody>
    </table>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.toggle-notes').forEach(button => {
                button.addEventListener('click', function () {
                    const notesId = this.getAttribute('data-notes-id');
                    const notesRow = document.getElementById('notes-' + notesId);

                    if (notesRow.style.display === 'none' || notesRow.style.display === '') {
                        notesRow.style.display = 'table-row';
                        this.textContent = 'Hide Notes';
                    } else {
                        notesRow.style.display = 'none';
                        this.textContent = 'Show Notes';
                    }
                });
            });
        });
    </script>
<?php require_once __DIR__ . '/include/bootstrap-js.php'; ?>
</body>
</html>
