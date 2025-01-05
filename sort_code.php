<?php
require 'dbconnect_master.php';
$connection = new MasterConnection();
$conn = $connection->getConnection();
// Fetch all agents
function fetchMasters($conn) {
    $stmt = $conn->prepare("SELECT * FROM sort_code ORDER BY sort_code ASC");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (isset($input['action'])) {
        if ($input['action'] === 'get_next_code') {
            // Fetch the next available agent_code
            $stmt = $conn->prepare("SELECT sort_code + 1 AS next_code FROM sort_code t1 
                                    WHERE NOT EXISTS (SELECT 1 FROM sort_code t2 WHERE t2.sort_code = t1.sort_code + 1) 
                                    ORDER BY sort_code ASC LIMIT 1");
            $stmt->execute();
            $next_code = $stmt->fetch(PDO::FETCH_ASSOC)['next_code'] ?? 1;

            echo json_encode(['success' => true, 'next_code' => $next_code]);
            exit;
        }
        if ($input['action'] === 'add') {
            $sort_code = $input['sort_code'];
            $warp_count = $input['warp_count'];
            $weft_count = $input['weft_count'];
            $epi = $input['epi'];
            $ppi = $input['ppi'];
            $ply = $input['ply'];
            $weave = $input['weave'];
            // Find the lowest available sort_code
            $stmt = $conn->prepare("SELECT sort_code + 1 AS next_code FROM sort_code t1 
                                    WHERE NOT EXISTS (SELECT 1 FROM sort_code t2 WHERE t2.sort_code = t1.sort_code + 1) 
                                    ORDER BY sort_code ASC LIMIT 1");
            $stmt->execute();
            $next_code = $stmt->fetch(PDO::FETCH_ASSOC)['next_code'] ?? 1;
            $stmt = $conn->prepare("INSERT INTO sort_code (sort_code, warp_count, weft_count, epi, ppi, ply, weave)
                                    VALUES (:sort_code, :warp_count, :weft_count, :epi, :ppi, :ply, :weave)");
            $stmt->bindParam(':sort_code', $next_code);
            $stmt->bindParam(':warp_count', $warp_count);
            $stmt->bindParam(':weft_count', $weft_count);
            $stmt->bindParam(':epi', $epi);
            $stmt->bindParam(':ppi', $ppi);
            $stmt->bindParam(':ply', $ply);
            $stmt->bindParam(':weave', $weave);

            $stmt->execute();
            echo json_encode([
                'success' => true,
                'sort_code' => $next_code,
                'warp_count' => $warp_count,
                'weft_count' => $weft_count,
                'epi' => $epi,
                'ppi' => $ppi,
                'ply' => $ply,
                'weave' => $weave
            ]);
            exit;
        }
        if ($input['action'] === 'delete') {
            $sort_codes = $input['sort_codes'];
            $placeholders = implode(',', array_fill(0, count($sort_codes), '?'));
            $stmt = $conn->prepare("DELETE FROM sort_code WHERE sort_code IN ($placeholders)");
            $stmt->execute($sort_codes);
            echo json_encode(['success' => true]);
            exit;
        }
    }
}
// Fetch agents for display
$sorts = fetchMasters($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sort Code</title>
    <link rel="stylesheet" href="css/master_details.css">
    <?php include 'navbar.php'; ?>
</head>
<body>
    <main>
        <div class="container">
            <h1>Sort Code</h1>
            <button class="master_button" id="addRowBtn">Add</button>
            <button class="master_button" id="deleteRowBtn">Delete</button>
            <form id="masterForm">
                <table>
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="selectAll"></th>
                            <th>Sort Code</th>
                            <th>Warp Count</th>
                            <th>Weft Count</th>
                            <th>EPI</th>
                            <th>PPI</th>
                            <th>PLY</th>
                            <th>Weave</th>
                        </tr>
                    </thead>
                    <tbody id="masterTableBody">
                        <?php foreach ($sorts as $sort): ?>
                        <tr>
                            <td><input type="checkbox" class="rowCheckbox" value="<?= $sort['sort_code'] ?>"></td>
                            <td><?= $sort['sort_code'] ?></td>
                            <td><?= $sort['warp_count'] ?></td>
                            <td><?= $sort['weft_count'] ?></td>
                            <td><?= $sort['epi'] ?></td>
                            <td><?= $sort['ppi'] ?></td>
                            <td><?= $sort['ply'] ?></td>
                            <td><?=$sort['weave']?></td>
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
                fetch('sort_code.php', {
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
                            <input type="number" name="warp_count" placeholder="Warp Count">
                            <div class="error-message" style="color: red; display: none;">Field required</div>
                        </div>
                    </td>
                    <td>
                        <div class="input-container">
                            <input type="number" name="weft_count" placeholder="Weft Count">
                            <div class="error-message" style="color: red; display: none;">Field required</div>
                        </div>
                    </td>
                    <td>
                        <div class="input-container">
                            <input type="number" name="epi" placeholder="Epi">
                            <div class="error-message" style="color: red; display: none;">Field required</div>
                        </div>
                    </td>
                    <td>
                        <div class="input-container">
                            <input type="number" name="ppi" placeholder="Ppi">
                            <div class="error-message" style="color: red; display: none;">Field required</div>
                        </div>
                    </td>
                    <td>
                        <div class="input-container">
                            <input type="number" name="ply" placeholder="Ply">
                            <div class="error-message" style="color: red; display: none;">Field required</div>
                        </div>
                    </td>
                    <td>
                        <div class="input-container">
                            <input type="text" name="weave" placeholder="Weave">
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
                            const warp_count = inputs[0].value.trim();
                            const weft_count = inputs[1].value.trim();
                            const epi = inputs[2].value.trim();
                            const ppi = inputs[3].value.trim();
                            const ply = inputs[4].value.trim();
                            const weave =inputs[5].value.trim();
                            const dataToSave = {
                                action: 'add',
                                warp_count: warp_count,
                                weft_count: weft_count,
                                epi: epi,
                                ppi: ppi,
                                ply: ply,
                                weave: weave
                            };
                            // Save data via API request
                            fetch('sort_code.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify(dataToSave)
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    showPopup('Data saved successfully!');
                                    newRow.innerHTML = `
                                        <td><input type="checkbox" class="rowCheckbox" value="${data.sort_code}"></td>
                                        <td>${data.sort_code}</td>
                                        <td>${data.warp_count}</td>
                                        <td>${data.weft_count}</td>
                                        <td>${data.epi}</td>
                                        <td>${data.ppi}</td>
                                        <td>${data.ply}</td>
                                        <td>${data.weave}</td>
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
                fetch('sort_code.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'delete', sort_codes: selectedRows })
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