<?php require 'dbconnect_master.php'; 
$db = new Connection(); 
$conn = $db->getConnection(); 

function fetchBuyers($conn) { 
    $stmt = $conn->prepare("SELECT * FROM buyer_details ORDER BY s1_no ASC"); 
    $stmt->execute(); 
    return $stmt->fetchAll(PDO::FETCH_ASSOC); 
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') { 
    $input = json_decode(file_get_contents('php://input'), true); 

    if (isset($input['action'])) { 
        if ($input['action'] === 'get_next_code') { 
            $stmt = $conn->prepare("SELECT COALESCE(MAX(s1_no) + 1, 1) AS next_code FROM buyer_details"); 
            $stmt->execute(); 
            $next_code = $stmt->fetch(PDO::FETCH_ASSOC)['next_code']; 
            echo json_encode(['success' => true, 'next_code' => $next_code]); 
            exit; 
        } 

        if ($input['action'] === 'add') {
            $stmt = $conn->prepare("INSERT INTO buyer_details (s1_no, buyer_code, buyer_name, invoice_address, contact_no, contact_person, delivery_address) VALUES (:s1_no, :buyer_code, :buyer_name, :invoice_address, :contact_no, :contact_person, :delivery_address)");
            $stmt->bindParam(':s1_no', $input['s1_no']);
            $stmt->bindParam(':buyer_code', $input['buyer_code']); // Now using user-entered buyer_code
            $stmt->bindParam(':buyer_name', $input['buyer_name']);
            $stmt->bindParam(':invoice_address', $input['invoice_address']);
            $stmt->bindParam(':contact_no', $input['contact_no']);
            $stmt->bindParam(':contact_person', $input['contact_person']);
            $stmt->bindParam(':delivery_address', $input['delivery_address']);
            $stmt->execute();
        
            echo json_encode(['success' => true]);
        }
        

        if ($input['action'] === 'delete') { 
            $s1_nos = $input['s1_nos']; 
            $placeholders = implode(',', array_fill(0, count($s1_nos), '?')); 
            $stmt = $conn->prepare("DELETE FROM buyer_details WHERE s1_no IN ($placeholders)"); 
            $stmt->execute($s1_nos); 

            echo json_encode(['success' => true]); 
            exit; 
        } 
    } 
} 

$buyers = fetchBuyers($conn); 
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
                        <th>S1 No</th>
                        <th>Buyer Code</th>
                        <th>Buyer Name</th>
                        <th>Invoice Address</th>
                        <th>Contact No</th>
                        <th>Contact Person</th>
                        <th>Delivery Address</th>
                    </tr>
                </thead>
                <tbody id="masterTableBody">
                    <?php foreach ($buyers as $buyer): ?>
                    <tr>
                        <td><input type="checkbox" class="rowCheckbox" value="<?= $buyer['s1_no'] ?>"></td>
                        <td><?= $buyer['s1_no'] ?></td>
                        <td><?= $buyer['buyer_code'] ?></td>
                        <td><?= htmlspecialchars($buyer['buyer_name']) ?></td>
                        <td><?= htmlspecialchars($buyer['invoice_address']) ?></td>
                        <td><?= htmlspecialchars($buyer['contact_no']) ?></td>
                        <td><?= htmlspecialchars($buyer['contact_person']) ?></td>
                        <td><?= htmlspecialchars($buyer['delivery_address']) ?></td>
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

        function showPopup(message) {
            popupMessage.textContent = message;
            popup.style.display = 'flex';
        }

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
                                <input type="number" name="buyer_code" placeholder="Enter Buyer Code">
                                <div class="error-message" style="color: red; display: none;">Field required</div>
                            </div>
                        </td>
                        <td>
                            <div class="input-container">
                                <input type="text" name="buyer_name" placeholder="Buyer Name">
                            </div>
                        </td>
                        <td>
                            <div class="input-container">
                                <input type="text" name="invoice_address" placeholder="Invoice Address">
                            </div>
                        </td>
                        <td>
                            <div class="input-container">
                                <input type="text" name="contact_no" placeholder="Contact No">
                            </div>
                        </td>
                        <td>
                            <div class="input-container">
                                <input type="text" name="contact_person" placeholder="Contact Person">
                            </div>
                        </td>
                        <td>
                            <div class="input-container">
                                <input type="text" name="delivery_address" placeholder="Delivery Address">
                            </div>
                        </td>
                        <td><button class="saveBtn">Save</button></td>
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
                    saveBtn.addEventListener('click', () => {
                        const buyer_code = newRow.querySelector('input[name="buyer_code"]').value;
                        const buyer_name = newRow.querySelector('input[name="buyer_name"]').value;
                        const invoice_address = newRow.querySelector('input[name="invoice_address"]').value;
                        const contact_no = newRow.querySelector('input[name="contact_no"]').value;
                        const contact_person = newRow.querySelector('input[name="contact_person"]').value;
                        const delivery_address = newRow.querySelector('input[name="delivery_address"]').value;

                        fetch('buyer_details.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                action: 'add',
                                s1_no: data.next_code,
                                buyer_code,
                                buyer_name,
                                invoice_address,
                                contact_no,
                                contact_person,
                                delivery_address
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                showPopup('Data saved successfully!');
                                newRow.innerHTML = `
                                    <td><input type="checkbox" class="rowCheckbox" value="${data.s1_no}"></td>
                                    <td>${data.s1_no}</td>
                                    <td>${buyer_code}</td> <!-- Displaying entered buyer code -->
                                    <td>${buyer_name}</td>
                                    <td>${invoice_address}</td>
                                    <td>${contact_no}</td>
                                    <td>${contact_person}</td>
                                    <td>${delivery_address}</td>
                                `;
                            } else {
                                showPopup('Failed to save data.');
                            }
                        })
                        .catch(() => showPopup('An error occurred while saving data.'));
                    });
                } else {
                    showPopup('Failed to generate next code.');
                }
            })
            .catch(() => showPopup('An error occurred while generating next code.'));
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
                body: JSON.stringify({ action: 'delete', s1_nos: selectedRows })
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