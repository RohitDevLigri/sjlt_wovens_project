<?php
require 'dbconnect_master.php';
$connection = new MasterConnection();
$conn = $connection->getConnection();

// Fetch all inspection types
function fetchInspectionTypes($conn) {
    $stmt = $conn->prepare("SELECT * FROM inspection_type ORDER BY s1_no ASC");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (isset($input['action'])) {
        if ($input['action'] === 'get_next_code') {
            // Fetch the next available s1_no
            $stmt = $conn->prepare("SELECT COALESCE(MAX(s1_no) + 1, 1) AS next_code FROM inspection_type");
            $stmt->execute();
            $next_code = $stmt->fetch(PDO::FETCH_ASSOC)['next_code'];

            echo json_encode(['success' => true, 'next_code' => $next_code]);
            exit;
        }

        if ($input['action'] === 'add') {
            $inspection_type = $input['inspection_type'];

            $stmt = $conn->prepare("INSERT INTO inspection_type (s1_no, inspection_type)
                                    VALUES (:s1_no, :inspection_type)");
            $stmt->bindParam(':s1_no', $input['s1_no']);
            $stmt->bindParam(':inspection_type', $inspection_type);
            $stmt->execute();

            echo json_encode(['success' => true, 's1_no' => $input['s1_no'], 'inspection_type' => $inspection_type]);
            exit;
        }

        if ($input['action'] === 'delete') {
            $s1_nos = $input['s1_nos'];
            $placeholders = implode(',', array_fill(0, count($s1_nos), '?'));
            $stmt = $conn->prepare("DELETE FROM inspection_type WHERE s1_no IN ($placeholders)");
            $stmt->execute($s1_nos);

            echo json_encode(['success' => true]);
            exit;
        }
    }
}

// Fetch inspection types for initial display
$inspectionTypes = fetchInspectionTypes($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inspection Type</title>
    <link rel="stylesheet" href="css/master_details.css">
    <?php include 'navbar.php'; ?>
</head>
<body>
    <main>
        <div class="container">
            <h1>Inspection Type</h1>
            <button class="master_button" id="addRowBtn">Add</button>
            <button class="master_button" id="deleteRowBtn">Delete</button>
            <form id="masterForm">
                <table>
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="selectAll"></th>
                            <th>S1 No</th>
                            <th>Inspection Type</th>
                        </tr>
                    </thead>
                    <tbody id="masterTableBody">
                        <?php foreach ($inspectionTypes as $inspection): ?>
                        <tr>
                            <td><input type="checkbox" class="rowCheckbox" value="<?= $inspection['s1_no'] ?>"></td>
                            <td><?= $inspection['s1_no'] ?></td>
                            <td><?= htmlspecialchars($inspection['inspection_type']) ?></td>
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

            function showPopup(message) {
                popupMessage.textContent = message;
                popup.style.display = 'flex';
            }

            popupClose.addEventListener('click', () => {
                popup.style.display = 'none';
            });

            addRowBtn.addEventListener('click', () => {
                fetch('inspection_type.php', {
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
                                <input type="text" name="inspection_type" placeholder="Inspection Type">
                            </td>
                            <td><button class="saveBtn">Save</button></td>
                        `;
                        masterTableBody.appendChild(newRow);
                        const saveBtn = newRow.querySelector('.saveBtn');
                        saveBtn.addEventListener('click', () => {
                            const inspection_type = newRow.querySelector('input[name="inspection_type"]').value;

                            fetch('inspection_type.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({
                                    action: 'add',
                                    s1_no: data.next_code,
                                    inspection_type
                                })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    showPopup('Data saved successfully!');
                                    newRow.innerHTML = `
                                        <td><input type="checkbox" class="rowCheckbox" value="${data.s1_no}"></td>
                                        <td>${data.s1_no}</td>
                                        <td>${data.inspection_type}</td>
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

                fetch('inspection_type.php', {
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
