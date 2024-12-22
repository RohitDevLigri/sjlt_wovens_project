<?php
include('auth_check.php');
include_once("navbar.php");
include_once("dbconnect.php"); // Include the database connection file

// Create an instance of the Connection class
$dbConnection = new Connection();
$conn = $dbConnection->getConnection(); // Get the connection

// Fetch loom data from the database
$loomOptions = '';
$NROptions = '';
$ShiftOptions = '';
$sql = "SELECT DISTINCT Machine, NR, Shift FROM production_report"; // Adjust table name if necessary

// Use PDO's prepare and execute methods
$stmt = $conn->prepare($sql);
$stmt->execute();

// Fetch data
if ($stmt->rowCount() > 0) {
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $loomOptions .= '<option value="' . htmlspecialchars($row['Machine'] ?? '') . '">' . htmlspecialchars($row['Machine'] ?? 'No Value') . '</option>';
        $NROptions .= '<option value="' . htmlspecialchars($row['NR'] ?? '') . '">' . htmlspecialchars($row['NR'] ?? 'No Value') . '</option>';
        $ShiftOptions .= '<option value="' . htmlspecialchars($row['Shift'] ?? '') . '">' . htmlspecialchars($row['Shift'] ?? 'No Value') . '</option>';

    }
} else {
    $loomOptions .= '<option value="">No Looms Available</option>';
}

// Close the connection (optional, as PDO closes it automatically on script end)
$conn = null; 
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="css/manualentry.css">
    <script src="font.js"></script>
    <script src="jquery.js"></script>
</head>
<body>
    <div class="main-content"> <!-- Main container for top and bottom margin adjustments -->
        <h3>Manual Entry of Loom Data</h3>
        <div class="testbox">
            <form class="testboxform" action="manualentry_submit.php" method="POST">
                <!-- row1 -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="date">Date</label>
                        <input type="date" name="date" id="date" required>
                    </div>
                    <div class="form-group">
                        <label for="machine">Loom</label>
                        <select name="machine" id="machine" required>
                            <?php echo $loomOptions; ?> <!-- Dynamically populated options -->
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="nr">NR</label>
                        <select name="nr" id="nr" required>
                            <?php echo $NROptions; ?> <!-- Dynamically populated options -->
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="shift">Shift</label>
                        <select name="shift" id="shift" required>
                            <?php echo $ShiftOptions; ?> <!-- Dynamically populated options -->
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="time">Time</label>
                        <input type="time" name="time" id="time" required>
                    </div>
                </div>
                <!-- row2 -->
                <div class="form-row">
                <div class="form-group">
                        <label for="picks">Picks</label>
                        <input type="number" name="picks" id="picks" required>
                    </div>
                    <div class="form-group">
                        <label for="length">Length</label>
                        <input type="number" name="length" id="length" required>
                    </div>
                    <div class="form-group">
                        <label for="percentage">Percentage</label>
                        <input type="number" name="percentage" id="percentage" required>
                    </div>
                    <div class="form-group">
                        <label for="Percentage">Weaver Percentage</label>
                        <input type="number" name="weaver_percentage" id="weaver_percentage" required>
                    </div>
                </div>
                <!-- row3 -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="stops">Stops</label>
                        <input type="number" name="stops" id="stops" required>
                    </div>
                    <div class="form-group">
                        <label for="stops_cmpx">Stops Cmpx</label>
                        <input type="number" name="stops_cmpx" id="stops_cmpx" required>
                    </div>
                    <div class="form-group">
                        <label for="stops_time">Stops Time</label>
                        <input type="number" name="stops_time" id="stops_time" required>
                    </div>
                    <div class="form-group">
                        <label for=""></label>
                    </div>
                </div>
                                <!-- row4 -->
                <div class="form-row">
                <div class="form-group">
                        <label for="filling">Filling</label>
                        <input type="number" name="filling" id="filling" required>
                    </div>
                    <div class="form-group">
                        <label for="filling_cmpx">Filling Cmpx</label>
                        <input type="number" name="filling_cmpx" id="filling_cmpx" required>
                    </div>
                    <div class="form-group">
                        <label for="filling_time">Filling Time</label>
                        <input type="number" name="filling_time" id="filling_time" required>
                    </div>
                    <div class="form-group">
                        <label for=""></label>
                    </div>
                </div>
                <!-- row5 -->
                <div class="form-row">

                    <div class="form-group">
                        <label for="warp">Warp</label>
                        <input type="number" name="warp" id="warp" required>
                    </div>
                    <div class="form-group">
                        <label for="warp_cmpx">Warp Cmpx</label>
                        <input type="number" name="warp_cmpx" id="warp_cmpx" required>
                    </div>
                    <div class="form-group">
                        <label for="warp_time">Warp Time</label>
                        <input type="number" name="warp_time" id="warp_time" required>
                    </div>
                    <div class="form-group">
                        <label for=""></label>
                    </div>
                </div>
                <!-- row6 -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="bobbin">bobbin</label>
                        <input type="number" name="bobbin" id="bobbin" required>
                    </div>
                    <div class="form-group">
                    <label for="bobbin_cmpx">bobbin Cmpx</label>
                        <input type="number" name="bobbin_cmpx" id="bobbin_cmpx" required>
                    </div>
                    <div class="form-group">
                        <label for="bobbin_time">bobbin Time</label>
                        <input type="number" name="bobbin_time" id="bobbin_time" required>
                    </div>
                    <div class="form-group">
                        <label for=""></label>
                    </div>
                </div>
                <!-- row7 -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="hand">hand</label>
                        <input type="number" name="hand" id="hand" required>
                    </div>
                    <div class="form-group">
                    <label for="hand_cmpx">hand Cmpx</label>
                        <input type="number" name="hand_cmpx" id="hand_cmpx" required>
                    </div>
                    <div class="form-group">
                        <label for="hand_time">hand Time</label>
                        <input type="number" name="hand_time" id="hand_time" required>
                    </div>
                    <div class="form-group">
                        <label for=""></label>
                    </div>
                </div>
                 <!-- row8 -->
                 <div class="form-row">
                    <div class="form-group">
                        <label for="other">Other</label>
                        <input type="number" name="other" id="other" required>
                    </div>
                    <div class="form-group">
                    <label for="other_cmpx">Other Cmpx</label>
                        <input type="number" name="other_cmpx" id="other_cmpx" required>
                    </div>
                    <div class="form-group">
                        <label for="other_time">Other Time</label>
                        <input type="number" name="other_time" id="other_time" required>
                    </div>
                    <div class="form-group">
                        <label for=""></label>
                    </div>
                </div>
                <!-- row9 -->
                 <div class="form-row">
                    <div class="form-group">
                        <label for="starts">Starts</label>
                        <input type="number" name="starts" id="starts" required>
                    </div>
                    <div class="form-group">
                    <label for="speed">Speed</label>
                        <input type="number" name="speed" id="speed" required>
                    </div>
                    <div class="form-group">
                        <label for=""></label>
                    </div>
                    <div class="form-group">
                        <label for=""></label>
                    </div>
                </div>
                    <input type="submit" name="mansubmit" value="Submit" class="submit-btn">
            </form>
        </div>
    </div>
</body>
</html>
