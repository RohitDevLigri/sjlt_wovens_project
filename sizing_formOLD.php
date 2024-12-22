<?php
include 'dbconnect.php';
// include 'navbar.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['date'];
    $workOrder = $_POST['work_order'];
    $setNo = $_POST['set_no'];
    $metres = $_POST['metres'];
    $beamNos = $_POST['beam_no'];
    $beamMetres = $_POST['beam_metres'];

    // Insert into sizing table
    $sql1 = "INSERT INTO sizing (work_order, date, set_no, metres) VALUES (?, ?, ?, ?)";
    $stmt1 = $conn->prepare($sql1);
    $stmt1->execute([$workOrder, $date, $setNo, $metres]);

    // Insert into beam status
    foreach ($beamNos as $index => $beamNo) {
        $beamMetre = $beamMetres[$index];
        $sql2 = "INSERT INTO beam_status (Set_No, Beam_No, Full_Metres, Status) VALUES (?, ?, ?, 'Full')";
        $stmt2 = $conn->prepare($sql2);
        $stmt2->execute([$setNo, $beamNo, $beamMetre]);
    }

    echo "Form submitted successfully.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/sizing_form.css">
    <title>Sizing Form</title>
</head>
<body>
    <h2>Sizing Form</h2>
    <form action="sizing_form.php" method="POST">
        <label for="date">Date:</label>
        <input type="date" id="date" name="date" required><br><br>

        <label for="work_order">Work Order:</label>
        <select id="work_order" name="work_order" required>
            <!-- Populate dynamically -->
        </select><br><br>

        <label for="set_no">Set No:</label>
        <input type="number" id="set_no" name="set_no" required><br><br>

        <label for="yarn_count">Yarn Count:</label>
        <input type="number" id="yarn_count" name="yarn_count" required><br><br>

        <label for="fibre">Fibre:</label>
        <input type="text" id="fibre" name="fibre" required><br><br>

        <label for="lot_no">Lot No:</label>
        <input type="number" id="lot_no" name="lot_no" required><br><br>

        <label for="mill_name">Mill Name:</label>
        <input type="text" id="mill_name" name="mill_name" required><br><br>

        <label for="customer_name">Customer Name:</label>
        <input type="text" id="customer_name" name="customer_name" required><br><br>

        <label for="type">Type:</label>
        <input type="text" id="type" name="type" required><br><br>

        <label for="metres">Metres:</label>
        <input type="number" id="metres" name="metres" required><br><br>

        </h4>
        <div id="beamFieldsContainer" style="display: none;">
            <label for="beam_no">Beam No:</label>
            <input type="number" id="beam_no" name="beam_no[]"><br><br>
            <label for="beam_metres">Beam Metres:</label>
            <input type="number" id="beam_metres" name="beam_metres[]"><br><br>
        </div>

        <button type="button" onclick="toggleBeamFields()">Add</button><br><br>

        <button type="submit">Submit Form</button>
    </form>

    <script>
        function toggleBeamFields() {
            const container = document.getElementById('beamFieldsContainer');
            if (container.style.display === 'none') {
                container.style.display = 'block';
            } else {
                container.style.display = 'none';
            }
        }
    </script>
</body>
</html>