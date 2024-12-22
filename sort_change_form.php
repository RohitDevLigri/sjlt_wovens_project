<?php
require("dbconnect.php");
include 'navbar.php';

// Initialize the connection
$db = new Connection();
$conn = $db->getConnection();

// Fetch the most recent cut beam number
$latestBeamNo = 'No Data Available'; // Default value
try {
    $stmt = $conn->prepare("SELECT beam_no FROM beam_status ORDER BY beam_no DESC LIMIT 1");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result) {
        $latestBeamNo = $result['beam_no'];
    }
} catch (PDOException $e) {
    echo "Error fetching latest beam number: " . $e->getMessage();
}

$currentSort = 'No Data Available'; // Default value
try {
    $stmt = $conn->prepare("SELECT current_sort FROM knotting ORDER BY current_sort DESC LIMIT 1");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result) {
        $currentSort = $result['current_sort'];
    }
} catch (PDOException $e) {
    echo "Error fetching latest beam number: " . $e->getMessage();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Fetch form data
    $date = $_POST['date'];
    $shift = $_POST['shift'];
    $loom_id = $_POST['loom'];
    $current_sort = $_POST['current_sort'];
    $loom_stop_time = $_POST['loom_stop_time'];
    $process_start_time = $_POST['process_start_time'];
    $process_loom_start_date = $_POST['process_loom_start_date'];
    $loom_start_time = $_POST['loom_start_time'];
    $new_sort = $_POST['new_sort'];
    $cut_beam_metres = $_POST['cut_beam_metres'];
    $new_beam_no = $_POST['new_beam_no'];
    $cut_beam_no = $_POST['cut_beam_no'];

    try {
        // Begin transaction
        $conn->beginTransaction();

        // Insert into Sort Change Table
        $sortChangeQuery = "INSERT INTO sort_change (
            date, shift, loom_id, loom_date_shift, current_sort, loom_stop_time, 
            process_start_time, process, loom_start_date, loom_start_time, 
            new_beam_no, cut_beam_no, cut_beam_meter, new_sort
        ) VALUES (
            :date, :shift, :loom_id, CONCAT(:date, '_', :shift, '_', :loom_id), 
            :current_sort, :loom_stop_time, :process_start_time, :process, 
            :loom_start_date, :loom_start_time, :new_beam_no, :cut_beam_no, :cut_beam_metres, :new_sort
        )";
        $stmt = $conn->prepare($sortChangeQuery);
        $stmt->execute([
            ':date' => $date,
            ':shift' => $shift,
            ':loom_id' => $loom_id,
            ':current_sort' => $current_sort,
            ':loom_stop_time' => $loom_stop_time,
            ':process_start_time' => $process_start_time,
            ':process' => $process_loom_start_date,
            ':loom_start_date' => $process_loom_start_date,
            ':loom_start_time' => $loom_start_time,
            ':new_beam_no' => $new_beam_no,
            ':cut_beam_no' => $cut_beam_no,
            ':cut_beam_metres' => $cut_beam_metres,
            ':new_sort' => $new_sort
        ]);

        // Update Beam Status Table - Cut Beam No
        // if ($cut_beam_metres > 0) {
        //     $beamStatusQuery = "UPDATE beam_status SET 
        //         loom_id = 0, sort_id = 0, 
        //         cut_metres = :cut_beam_metres, status = 'Cut' 
        //         WHERE beam_no = :cut_beam_no";
        // } else {
        //     $beamStatusQuery = "UPDATE beam_status SET 
        //         loom_id = 0, sort_id = 0, 
        //         cut_metres = 0, full_metres = 0, status = 'Empty' 
        //         WHERE beam_no = :cut_beam_no";
        // }
        // $stmt = $conn->prepare($beamStatusQuery);
        // $stmt->execute([
        //     ':cut_beam_metres' => $cut_beam_metres,
        //     ':cut_beam_no' => $cut_beam_no
        // ]);

        // // Update Beam Status Table - New Beam No
        // $newBeamStatusQuery = "UPDATE beam_status SET 
        //     loom_id = :loom_id, sort_id = :new_sort, 
        //     status = 'Running in the beam' 
        //     WHERE beam_no = :new_beam_no";
        // $stmt = $conn->prepare($newBeamStatusQuery);
        // $stmt->execute([
        //     ':loom_id' => $loom_id,
        //     ':new_sort' => $new_sort,
        //     ':new_beam_no' => $new_beam_no
        // ]);

        // Commit transaction
        $conn->commit();
        echo "Sort Change Form Submitted Successfully!";
    } catch (PDOException $e) {
        // Rollback transaction in case of error
        $conn->rollBack();
        echo "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sort Change Form</title>
    <link rel="stylesheet" href="css/sort_change_form.css">
</head>
<body>
    <div class="container">
        <h2>Sort Change Form</h2>
        <!-- Display Success or Error Message -->
        <!-- <?php if ($message) echo $message; ?> -->
        <form action="sort_change_form.php" method="POST">
            <!-- Row 1 -->
            <div class="form-row">
                <div class="form-group">
                    <label for="date">Date:</label>
                    <input type="date" id="date" name="date" required>
                </div>
                <div class="form-group">
                    <label for="shift">Shift:</label>
                    <select id="shift" name="shift" required>
                        <option value="A">A</option>
                        <option value="B">B</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="loom">Loom:</label>
                    <select id="loom" name="loom" required>
                            <option value="1">Loom 1</option>
                            <option value="2">Loom 2</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="current_sort">Current Sort:</label>
                    <!-- <input type="text" id="current_sort" name="current_sort" readonly><br> -->
                    <input type="text" id="current_sort" name="current_sort" readonly value="<?php echo htmlspecialchars($currentSort); ?>">
                </div>
            </div>
            <!-- Row 2 -->
            <div class="form-row">
                <div class="form-group">
                    <label for="loom_stop_time">Loom Stop Time:</label>
                    <input type="time" id="loom_stop_time" name="loom_stop_time" required>
                </div>
                <div class="form-group">
                    <label for="process_start_time">Process Start Time:</label>
                    <input type="time" id="process_start_time" name="process_start_time" required>
                </div>
                <div class="form-group">
                    <label for="process_loom_start_date">Process Loom Start Date:</label>
                    <input type="date" id="process_loom_start_date" name="process_loom_start_date" required>
                </div>
                <div class="form-group">
                    <label for="loom_start_time">Loom Start Time:</label>
                    <input type="time" id="loom_start_time" name="loom_start_time" required>
                </div>
            </div>
            <!-- Row 3 -->
            <div class="form-row">
                <div class="form-group">
                    <label for="new_sort">New Sort:</label>
                    <select id="new_sort" name="new_sort" required>
                        <option value="Sort1">Sort 1</option>
                        <option value="Sort2">Sort 2</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="cut_beam_metres">Cut Beam Metres:</label>
                    <input type="number" id="cut_beam_metres" name="cut_beam_metres" required>
                </div>
                <div class="form-group">
                    <label for="new_beam_no">New Beam No:</label>
                    <input type="number" id="new_beam_no" name="new_beam_no" required>
                </div>
                <div class="form-group">
                    <label for="cut_beam_no">Cut Beam No:</label>
                    <input type="text" id="cut_beam_no" name="cut_beam_no" readonly value="<?php echo htmlspecialchars($latestBeamNo); ?>">
                </div>
            </div>
            <button type="submit" class="submit-btn">Submit</button>
        </form>
    </div>
</body>
</html>
