<!-- START report-booking-time-inc --> 

<!-- TIME RECORDING MODAL -->
<div id="timeModal" class="modal fade" tabindex="-1" aria-labelledby="timeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="timeModalLabel">
                    <span class="static-text">Record Time and Costs for</span>
                    <span class="dynamic-name"></span>
                </h5>
                <p>User: <span class="dynamic-user"></span></p>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="timeForm">
                    <div class="row">
                        <!-- Column 1 -->
                        <div class="col-md-6">
                            <!-- Hidden data on form -->
                            <input type="hidden" name="productid" id="productid">
                            <input type="hidden" name="name" id="name">
                            <input type="hidden" name="user" id="user" value="">

                            <div class="mb-3">
                                <label for="date" class="form-label">Date</label>
                                <input type="date" class="form-control" id="date" name="date" value="<?= date('Y-m-d') ?>">
                            </div>
                            <div class="mb-3">
                                <label for="vet" class="form-label">Vet</label>
                                <select id="vet" name="vet" class="form-select"></select>
                            </div>
                            <div class="mb-3">
                                <label for="courier" class="form-label">Courier (NETT Cost)</label>
                                <input type="number" class="form-control" id="courier" name="courier" step="0.01" value="0.00">
                            </div>
                            <div class="mb-3">
                                <label for="tinymcetextarea" class="form-label">Notes</label>
                                <textarea id="tinymcetextarea" name="notes" rows="6" class="form-control"></textarea>
                            </div>
                        </div>
                        <!-- Column 2 -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="time_ov" class="form-label">Time OV (Minutes)</label>
                                <input type="number" class="form-control" id="time_ov" name="time_ov" value="0">
                            </div>
                            <div class="mb-3">
                                <label for="time_cso" class="form-label">Time CSO (Minutes)</label>
                                <input type="number" class="form-control" id="time_cso" name="time_cso" value="0">
                            </div>
                            <div class="mb-3">
                                <label for="travel_units" class="form-label">Travel (Units)</label>
                                <input type="number" class="form-control" id="travel_units" name="travel_units" value="0" min="0" step="0.01">
                            </div>
                            <div class="mb-3">
                                <label for="travel_miles" class="form-label">Travel (Miles)</label>
                                <input type="number" class="form-control" id="travel_miles" name="travel_miles" value="0">
                            </div>

                            <div class="mb-3">
                                <label for="certs" class="form-label">Certs (Number)</label>
                                <input type="number" class="form-control" id="certs" name="certs" value="0">
                            </div>
                             <div class="mb-3">
                                <label for="travel_miles" class="form-label">Tanker Certs</label>
                                <input type="number" class="form-control" id="tanker_cert" name="tanker_cert" inputmode="decimal" min="0" value="0">
                            </div>
                            <div class="mb-3">
                                <label for="sha_sa" class="form-label">SHA SA (Number)</label>
                                <input type="number" class="form-control" id="sha_sa" name="sha_sa" value="0">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveTimeData">Save</button>
            </div>
        </div>
    </div>
</div>

<!-- END TIME REORDING MODAL -->



<!-- START TIME REPORTING MODAL -->

<div id="timereportModal" class="modal fade" tabindex="-1" aria-labelledby="timereportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="timereportModalLabel">
                    <span class="static-text">Report of Time and Costs for</span>
                    <span class="dynamic-name"></span> 
                    <span class="dynamic-id"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table id="timereportTable" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Date</th>
                            <th>Vet</th>
                            <th class='text-end'>Time<br>OV</th>
                            <th class='text-end'>Time<br>CSO</th>
                            <th class='text-end'>Travel<br>Units</th>
                            <th class='text-end'>Travel<br>Miles</th>
                            <th class='text-end'>Certs</th>
                            <th class='text-end'>Tanker<br>Certs</th>
                            <th class='text-end'>SHA<br>SA</th>                           
                            <th class='text-end'>Courier</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- END TIME REPORTING MODAL -->



<!-- START TIME RECORDING SCRIPTS -->

<script>
    document.addEventListener('DOMContentLoaded', function () {

        // Populate Vet dropdown
        fetch('controllers/timeAdmin.php?action=getVetOptions')
            .then(response => {
                if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
                return response.json();
            })
            .then(data => {
                if (data.success && Array.isArray(data.data)) {
                    const vetDropdown = document.getElementById('vet');
                    vetDropdown.innerHTML = ''; // Clear existing options
                    data.data.forEach(item => {
                        const option = new Option(item.label, item.value);
                        vetDropdown.add(option);
                    });
                } else {
                    console.error("Unexpected response format:", data);
                    alert('Failed to load vet options.');
                }
            })
            .catch(error => console.error('Error fetching Vet options:', error));

        // Update modal dynamically
        const timeModal = document.getElementById('timeModal');
        timeModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget; // Button that triggered the modal

            // Extract data attributes
            const name = button.getAttribute('data-name');
            const userId = button.getAttribute('data-user'); // Correct user ID
            const productid = button.getAttribute('data-id'); // Correct Product ID for PO
            const vetId = button.getAttribute('data-vet'); // Vet ID

            // Debugging: Log the extracted values
            console.log('Name:', name);
            console.log('User ID:', userId);
            console.log('PO/ProdID:', productid);
            console.log('Vet ID:', vetId);

            // Update modal title and hidden fields
            const staticText = timeModal.querySelector('.modal-title .static-text');
            const dynamicName = timeModal.querySelector('.modal-title .dynamic-name');
            const dynamicUser = timeModal.querySelector('.dynamic-user');
            staticText.textContent = "Record Time and Costs for";
            dynamicName.textContent = name;
            dynamicUser.textContent = `User ID: ${userId}`; // Display user ID

            document.getElementById('name').value = name || ''; // Update hidden name field
            document.getElementById('productid').value = productid || ''; // Update hidden PO field
            document.getElementById('user').value = userId || ''; // Update hidden user field

            // Pre-select the vet in the dropdown
            const vetDropdown = document.getElementById('vet');
            if (vetId && vetDropdown.querySelector(`option[value="${vetId}"]`)) {
                vetDropdown.value = vetId; // Set the dropdown value
            } else {
                vetDropdown.value = ''; // Reset if no match found
            }
        });

        // Save data
        document.getElementById('saveTimeData').addEventListener('click', function () {
            const formData = new FormData(document.getElementById('timeForm'));

            // Debugging: Log the form data before sending
            for (const [key, value] of formData.entries()) {
                console.log(`${key}: ${value}`);
            }

            fetch('controllers/timeAdmin.php?action=saveTimeData', {
                method: 'POST',
                body: new FormData(document.getElementById('timeForm')),
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Data saved successfully.');
                        document.getElementById('timeForm').reset();
                        bootstrap.Modal.getInstance(document.getElementById('timeModal')).hide();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => console.error('Error saving data:', error));
        });
    });
</script>

<!-- END TIME RECORDING SCRIPTS -->


<!-- START TIME REPORTING SCRIPTS -->

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const timereportModal = document.getElementById('timereportModal');

        timereportModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const productId = button.getAttribute('data-id');
            const productName = button.getAttribute('data-name');

            timereportModal.querySelector('.dynamic-name').textContent = `${productName}`;
            timereportModal.querySelector('.dynamic-id').textContent = `[${productId}]`;

            fetch(`controllers/timeAdmin.php?action=getTimereport&productid=${productId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (!data.success) {
                        alert(`Error: ${data.message}`);
                        return;
                    }

                    const tableBody = timereportModal.querySelector('#timereportTable tbody');
                    tableBody.innerHTML = '';

                    data.data.forEach(row => {
                        const formattedDate = new Date(row.date).toLocaleDateString('en-GB');

                        const tableRow = `
                            <tr>
                                <td>${row.id}</td>
                                <td>${formattedDate}</td>
                                <td>${row.vet}</td>
                                <td class='text-end'>${row.time_ov}</td>
                                <td class='text-end'>${row.time_cso}</td>
                                <td class='text-end'>${row.travel_units}</td>
                                <td class='text-end'>${row.travel_miles}</td>
                                <td class='text-end'>${row.certs}</td>
                                <td class='text-end'>${row.tanker_cert}</td>
                                <td class='text-end'>${row.sha_sa}</td>
                                <td class='text-end'>&pound;${parseFloat(row.courier).toFixed(2)}</td>
                                <td>
                                    <button class="btn btn-link toggle-notes" data-notes-id="${row.id}">
                                        <i class="fa-regular fa-note-sticky text-success"></i>
                                    </button> &nbsp;&nbsp; 
                                    
                                    <a href="recordEditv5.php?frm=13&id=${row.id}" class="btn btn-link" target="_blank">
                                        <i class="fa-regular fa-pen-to-square"></i>
                                    </a>
                                </td>
                            </tr>
                            <tr class="notes-row" id="notes-${row.id}" style="display: none;">
                                <td colspan="11">${row.notes}</td>
                            </tr>`;
                        tableBody.insertAdjacentHTML('beforeend', tableRow);
                    });

                    attachNoteToggle();
                })
                .catch(error => alert('An error occurred: ' + error.message));
        });

        function attachNoteToggle() {
            document.querySelectorAll('.toggle-notes').forEach(button => {
                button.addEventListener('click', function () {
                    const notesId = this.getAttribute('data-notes-id');
                    const notesRow = document.getElementById(`notes-${notesId}`);
                    const icon = this.querySelector('i');

                    if (notesRow.style.display === 'none') {
                        notesRow.style.display = 'table-row';
                        icon.classList.remove('text-success');
                        icon.classList.add('text-danger');
                    } else {
                        notesRow.style.display = 'none';
                        icon.classList.remove('text-danger');
                        icon.classList.add('text-success');
                    }
                });
            });
        }
    });
</script>


<!-- END TIME REPORTING SCRIPTS -->


<!-- END report-booking-time-inc -->
