<?php
require 'dbconnect_master.php';
$connection = new MasterConnection();
$conn = $connection->getConnection();

// Fetch all certification details
function fetchCertificationDetails($conn) {
    $stmt = $conn->prepare("SELECT * FROM certification_details ORDER BY s1_no ASC");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (isset($input['action'])) {
        if ($input['action'] === 'get_next_code') {
            // Fetch the next available s1_no
            $stmt = $conn->prepare("SELECT COALESCE(MAX(s1_no) + 1, 1) AS next_code FROM certification_details");
            $stmt->execute();
            $next_code = $stmt->fetch(PDO::FETCH_ASSOC)['next_code'];

            echo json_encode(['success' => true, 'next_code' => $next_code]);
            exit;
        }

        if ($input['action'] === 'add') {
            $certification_id = $input['certification_id'];

            $stmt = $conn->prepare("INSERT INTO certification_details (s1_no, certification_id)
                                    VALUES (:s1_no, :certification_id)");
            $stmt->bindParam(':s1_no', $input['s1_no']);
            $stmt->bindParam(':certification_id', $certification_id);
            $stmt->execute();

            echo json_encode(['success' => true, 's1_no' => $input['s1_no'], 'certification_id' => $certification_id]);
            exit;
        }

        if ($input['action'] === 'delete') {
            $s1_nos = $input['s1_nos'];
            $placeholders = implode(',', array_fill(0, count($s1_nos), '?'));
            $stmt = $conn->prepare("DELETE FROM certification_details WHERE s1_no IN ($placeholders)");
            $stmt->execute($s1_nos);

            echo json_encode(['success' => true]);
            exit;
        }
    }
}

// Fetch certification details for initial display
$certificationDetails = fetchCertificationDetails($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certification Details</title>
    <link rel="stylesheet" href="css/master_details.css">
    <?php include 'navbar.php'; ?>
</head>
<body>
    <main>
        <div class="container">
            <h1>Certification Details</h1>
            <button class="master_button" id="addRowBtn">Add</button>
            <button class="master_button" id="deleteRowBtn">Delete</button>
            <button type="button" onclick="window.location.href='dashboard.php'" class="buyerBtn">Back</button>
            <form id="masterForm">
                <table>
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="selectAll"></th>
                            <th>S1 No</th>
                            <th>Certification ID</th>
                        </tr>
                    </thead>
                    <tbody id="masterTableBody">
                        <?php foreach ($certificationDetails as $detail): ?>
                        <tr>
                            <td><input type="checkbox" class="rowCheckbox" value="<?= $detail['s1_no'] ?>"></td>
                            <td><?= $detail['s1_no'] ?></td>
                            <td><?= htmlspecialchars($detail['certification_id']) ?></td>
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
                fetch('certification_details.php', {
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
                                    <input type="text" name="certification_id" placeholder="Certification ID">
                                </div>
                            </td>
                            <td><button class="saveBtn">Save</button></td>
                        `;
                        masterTableBody.appendChild(newRow);
                        const saveBtn = newRow.querySelector('.saveBtn');
                        saveBtn.addEventListener('click', () => {
                            const certification_id = newRow.querySelector('input[name="certification_id"]').value;

                            fetch('certification_details.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({
                                    action: 'add',
                                    s1_no: data.next_code,
                                    certification_id
                                })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    showPopup('Data saved successfully!');
                                    newRow.innerHTML = `
                                        <td><input type="checkbox" class="rowCheckbox" value="${data.s1_no}"></td>
                                        <td>${data.s1_no}</td>
                                        <td>${data.certification_id}</td>
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

                fetch('certification_details.php', {
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
