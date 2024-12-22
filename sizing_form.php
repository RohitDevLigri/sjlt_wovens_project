<?php
include("navbar.php");
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'dbconnect.php'; // Include the Connection class

// Initialize the database connection
$database = new Connection();
$conn = $database->getConnection();

$message = ""; // Message to display to the user

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['date'];
    $workOrder = $_POST['work_order'];
    $setNo = $_POST['set_no'];
    $yarnCount = $_POST['yarn_count'];
    $fibre = $_POST['fibre'];
    $lotNo = $_POST['lot_no'];
    $millName = $_POST['mill_name'];
    $customerName = $_POST['customer_name'];
    $type = $_POST['type'];
    $metres = $_POST['metres'];
    $beamNos = $_POST['beam_no'];
    $beamMetres = $_POST['beam_metres'];

    try {
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

        $message = "<p style='color: green;'>Form submitted successfully.</p>";
    } catch (PDOException $e) {
        // Handle duplicate primary key or other errors
        if ($e->getCode() == 23000) { // 23000 is the SQLSTATE for integrity constraint violations
            $message = "<p style='color: red;'>Error: Set No Duplicate entry detected. Please check your inputs.</p>";
        } else {
            $message = "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
        }
    }
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
    <div class="container">
        <h2>Sizing Form</h2>
        
        <!-- Display Success or Error Message -->
        <?php if ($message) echo $message; ?>

        <form action="sizing_form.php" method="POST">
            <!-- Row 1 -->
            <div class="form-row">
                <div class="form-group">
                    <label for="date">Date:</label>
                    <input type="date" id="date" name="date" required>
                </div>
                <div class="form-group">
                    <label for="work_order">Work Order:</label>
                    <select id="work_order" name="work_order" required>
                        <option value="A">A</option>
                        <option value="B">B</option>
                        <option value="C">C</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="set_no">Set No:</label>
                    <input type="number" id="set_no" name="set_no" required>
                </div>
                <div class="form-group">
                    <label for="yarn_count">Yarn Count:</label>
                    <input type="number" id="yarn_count" name="yarn_count" required>
                </div>
            </div>

            <!-- Row 2 -->
            <div class="form-row">
                <div class="form-group">
                    <label for="fibre">Fibre:</label>
                    <input type="text" id="fibre" name="fibre" required>
                </div>
                <div class="form-group">
                    <label for="lot_no">Lot No:</label>
                    <input type="number" id="lot_no" name="lot_no" required>
                </div>
                <div class="form-group">
                    <label for="mill_name">Mill Name:</label>
                    <input type="text" id="mill_name" name="mill_name" required>
                </div>
                <div class="form-group">
                    <label for="customer_name">Customer Name:</label>
                    <input type="text" id="customer_name" name="customer_name" required>
                </div>
            </div>

            <!-- Row 3 -->
            <div class="form-row">
                <div class="form-group">
                    <label for="type">Type:</label>
                    <input type="text" id="type" name="type" required>
                </div>
                <div class="form-group">
                    <label for="metres">Metres:</label>
                    <input type="number" id="metres" name="metres" required>
                </div>
                <div class="form-group">
                   <button type="button" class ="add-btn" onclick="toggleBeamFields()">Add</button>
                </div>
                <div class="form-group">
                    <label for="space"> </label>
                </div>
            </div>

            <!-- Beam Fields -->
            <div id="beamFieldsContainer" style="display: none;"> <!-- Initially hidden -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="beam_no">Beam No:</label>
                        <input type="number" name="beam_no[]" placeholder="Beam No" >
                    </div>
                    <div class="form-group">
                        <label for="beam_metres">Beam Metres:</label>
                        <input type="number" name="beam_metres[]" placeholder="Beam Metres" >
                    </div>
                    <div class="form-group">
                        <label for="space"> </label>
                    </div>
                    <div class="form-group">
                        <label for="space"> </label>
                    </div>
                </div>
            </div>

            <button type="submit" class="submit-btn">Submit</button>
        </form>
    </div>

    <script>
        function toggleBeamFields() {
            const container = document.getElementById('beamFieldsContainer');
            if (container.style.display === 'none'  || container.style.display === '') {
                container.style.display = 'block';
            } else {
                container.style.display = 'none';
            }
        }
    </script>
</body>
</html>
