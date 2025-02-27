<?php
require 'dbconnect_master.php';
$db = new Connection();
$conn = $db->getConnection();
// Fetch all agents
function fetchMasters($conn) {
    $stmt = $conn->prepare("SELECT * FROM buyer_details ORDER BY buyer_code ASC");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (isset($input['action'])) {
        if ($input['action'] === 'get_next_code') {
            // Fetch the next available agent_code
            $stmt = $conn->prepare("SELECT buyer_code + 1 AS next_code FROM buyer_details t1 
                                    WHERE NOT EXISTS (SELECT 1 FROM buyer_details t2 WHERE t2.buyer_code = t1.buyer_code + 1) 
                                    ORDER BY buyer_code ASC LIMIT 1");
            $stmt->execute();
            $next_code = $stmt->fetch(PDO::FETCH_ASSOC)['next_code'] ?? 1;

            echo json_encode(['success' => true, 'next_code' => $next_code]);
            exit;
        }
        if ($input['action'] === 'add') {
            $buyer_name = $input['buyer_name'];
            $invoice_address = $input['invoice_address'];
            $contact_no = $input['contact_no'];
            $contact_person = $input['contact_person'];
            $delivery_address = $input['delivery_address'];
            // Find the lowest available agent_code
            $stmt = $conn->prepare("SELECT buyer_code + 1 AS next_code FROM buyer_details t1 
                                    WHERE NOT EXISTS (SELECT 1 FROM buyer_details t2 WHERE t2.buyer_code = t1.buyer_code + 1) 
                                    ORDER BY buyer_code ASC LIMIT 1");
            $stmt->execute();
            $next_code = $stmt->fetch(PDO::FETCH_ASSOC)['next_code'] ?? 1;
            $stmt = $conn->prepare("INSERT INTO buyer_details (buyer_code, buyer_name, invoice_address, contact_no, contact_person, delivery_address)
                                    VALUES (:buyer_code, :buyer_name, :invoice_address, :contact_no, :contact_person, :delivery_address)");
            $stmt->bindParam(':buyer_code', $next_code);
            $stmt->bindParam(':buyer_name', $buyer_name);
            $stmt->bindParam(':invoice_address', $invoice_address);
            $stmt->bindParam(':contact_no', $contact_no);
            $stmt->bindParam(':contact_person', $contact_person);
            $stmt->bindParam(':delivery_address', $delivery_address);

            $stmt->execute();
            echo json_encode([
                'success' => true,
                'buyer_code' => $next_code,
                'buyer_name' => $buyer_name,
                'invoice_address' => $invoice_address,
                'contact_no' => $contact_no,
                'contact_person' => $contact_person,
                'delivery_address' => $delivery_address
            ]);
            exit;
        }
        if ($input['action'] === 'delete') {
            $buyer_codes = $input['buyer_codes'];
            $placeholders = implode(',', array_fill(0, count($buyer_codes), '?'));
            $stmt = $conn->prepare("DELETE FROM buyer_details WHERE buyer_code IN ($placeholders)");
            $stmt->execute($buyer_codes);
            echo json_encode(['success' => true]);
            exit;
        }
    }
}
// Fetch agents for display
$buyers = fetchMasters($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buyer Details</title>
    <link rel="stylesheet" href="css/master_details.css">
    <?php include 'navbar.php'; ?>
</head>
<body>
    <main>
        <div class="container">
            <h1>Buyer Details</h1>
            <button class="master_button" id="addRowBtn">Add</button>
            <button class="master_button" id="deleteRowBtn">Delete</button>
            <form id="masterForm">
                <table>
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="selectAll"></th>
                            <th>Buyer Code</th>
                            <th>Buyer Name</th>
                            <th>Invoice Address</th>
                            <th>Contact No</th>
                            <th>Contact Person</th>
                            <th>delivery Address</th>
                        </tr>
                    </thead>
                    <tbody id="masterTableBody">
                        <?php foreach ($buyers as $buyer): ?>
                        <tr>
                            <td><input type="checkbox" class="rowCheckbox" value="<?= $buyer['buyer_code'] ?>"></td>
                            <td><?= $buyer['buyer_code'] ?></td>
                            <td><?= $buyer['buyer_name'] ?></td>
                            <td><?= $buyer['invoice_address'] ?></td>
                            <td><?= $buyer['contact_no'] ?></td>
                            <td><?= $buyer['contact_person'] ?></td>
                            <td><?= $buyer['delivery_address'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </form>
            <!-- Popup Modal -->
            <div class="popup-overlay" id="popup">
                <div class="popup-content">
                    <h3 id="popup-message"></h3>
                    <button id="popup-close">OK</button>
                </div>
            </div>
        </div>
        <script>
            const addRowBtn = document.getElementById('addRowBtn');
            const deleteRowBtn = document.getElementById('deleteRowBtn');
            const masterTableBody = document.getElementById('masterTableBody');
            const selectAll = document.getElementById('selectAll');
            const popup = document.getElementById('popup');
            const popupMessage = document.getElementById('popup-message');
            const popupClose = document.getElementById('popup-close');
            let lastUsedCode = 0; // Store the last used agent code
            // Function to show popup with a message
            function showPopup(message) {
                popupMessage.textContent = message;
                popup.style.display = 'flex';
            }
            // Close popup
            popupClose.addEventListener('click', () => {
                popup.style.display = 'none';
            });
            addRowBtn.addEventListener('click', () => {
                fetch('buyer_details.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'get_next_code' })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const newRow = document.createElement('tr');
                        newRow.innerHTML = `
                            <td></td>
                    <td>${data.next_code}</td>
                    <td>
                        <div class="input-container">
                            <input type="text" name="buyer_name" placeholder="Buyer Name">
                            <div class="error-message" style="color: red; display: none;">Field required</div>
                        </div>
                    </td>
                    <td>
                        <div class="input-container">
                            <input type="text" name="invoice_address" placeholder="Invoice Address">
                            <div class="error-message" style="color: red; display: none;">Field required</div>
                        </div>
                    </td>
                    <td>
                        <div class="input-container">
                            <input type="text" name="contact_no" placeholder="Contact No">
                            <div class="error-message" style="color: red; display: none;">Field required</div>
                        </div>
                    </td>
                    <td>
                        <div class="input-container">
                            <input type="text" name="contact_person" placeholder="Contact Person">
                            <div class="error-message" style="color: red; display: none;">Field required</div>
                        </div>
                    </td>
                    <td>
                        <div class="input-container">
                            <input type="text" name="delivery_address" placeholder="Delivery_Address">
                            <div class="error-message" style="color: red; display: none;">Field required</div>
                        </div>
                    </td>
                    <td><button class="saveBtn" disabled>Save</button></td>
                `;
                    masterTableBody.appendChild(newRow);
                    const inputs = newRow.querySelectorAll('input');
                    const saveBtn = newRow.querySelector('.saveBtn');
                    // Function to check if all fields are filled
                    const checkFields = () => {
                        let isValid = true;
                        inputs.forEach(input => {
                            if (!input.value.trim()) {
                                isValid = false;
                                newRow.querySelector(`input[name="${input.name}"] + .error-message`).style.display = 'inline';
                            } else {
                                newRow.querySelector(`input[name="${input.name}"] + .error-message`).style.display = 'none';
                            }
                        });
                        // Enable or disable the save button based on validation
                        saveBtn.disabled = !isValid;
                    };
                        // Event listeners for input validation
                        inputs.forEach(input => {
                            input.addEventListener('input', checkFields);
                        });
                        // Save button click handler
                        saveBtn.addEventListener('click', (event) => {
                            event.preventDefault();  // Prevent page refresh on submit
                            // Validate again before saving
                            checkFields();
                            // If any field is invalid, do not proceed with saving
                            if (saveBtn.disabled) {
                                return; // Prevent saving if any field is empty
                            }
                            // Collect data to save
                            const buyer_name = inputs[0].value.trim();
                            const invoice_address = inputs[1].value.trim();
                            const contact_no = inputs[2].value.trim();
                            const contact_person = inputs[3].value.trim();
                            const delivery_address = inputs[4].value.trim();
                            const dataToSave = {
                                action: 'add',
                                buyer_name: buyer_name,
                                invoice_address: invoice_address,
                                contact_no: contact_no,
                                contact_person: contact_person,
                                delivery_address: delivery_address
                            };
                            // Save data via API request
                            fetch('buyer_details.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify(dataToSave)
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    showPopup('Data saved successfully!');
                                    newRow.innerHTML = `
                                        <td><input type="checkbox" class="rowCheckbox" value="${data.buyer_code}"></td>
                                        <td>${data.buyer_code}</td>
                                        <td>${data.buyer_name}</td>
                                        <td>${data.invoice_address}</td>
                                        <td>${data.contact_no}</td>
                                        <td>${data.contact_person}</td>
                                        <td>${data.delivery_address}</td>
                                    `;
                                }
                            })
                            .catch(error => showPopup('Error: ' + error.message));
                        });
                    }
                });
            });
            deleteRowBtn.addEventListener('click', () => {
                const selectedRows = Array.from(document.querySelectorAll('.rowCheckbox:checked'))
                    .map(checkbox => checkbox.value);
                if (selectedRows.length === 0) {
                    showPopup('No rows selected!');
                    return;
                }
                fetch('buyer_details.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'delete', buyer_codes: selectedRows })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showPopup('Data deleted successfully!');
                        location.reload();
                    }
                });
            });
            selectAll.addEventListener('change', () => {
                const checkboxes = document.querySelectorAll('.rowCheckbox');
                checkboxes.forEach(checkbox => checkbox.checked = selectAll.checked);
            });
        </script>
    </main>
</body>
</html>