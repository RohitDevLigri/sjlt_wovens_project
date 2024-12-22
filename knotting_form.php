<?php
include 'dbconnect.php';
include 'navbar.php';

// Establish database connection using PDO
$db = new Connection();
$conn = $db->getConnection();

// Fetch looms and new beam numbers from the database
$looms = $conn->query("SELECT loom_no FROM knotting");
$new_beams = $conn->query("SELECT new_beam_no FROM sort_change ORDER BY id DESC");

// Initialize variables
$current_sort = 'Select a loom to display sort'; // Default value for initial page load
$current_cut_beam = 'N/A'; // Default value for cut beam
$loom_sorts = []; // Initialize loom sort mapping

// Fetch all loom sorts for JavaScript preload
$loom_sort_stmt = $conn->query("SELECT k.loom_no, sc.current_sort, sc.loom_id 
                                FROM sort_change sc 
                                JOIN knotting k ON sc.loom_id = k.loom_no");
$loom_sorts = $loom_sort_stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
$message = ''; // To display success or error messages
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['date'];
    $shift = $_POST['shift'];
    $loom_no = $_POST['loom_no'];
    $cut_beam_meters = $_POST['cut_beam_meters'];
    $new_beam_no = $_POST['new_beam_no'];

    // Fetch current sort and cut beam based on loom number
    $loom_stmt = $conn->prepare("SELECT sc.current_sort, sc.loom_id 
                                 FROM sort_change sc 
                                 JOIN knotting k ON sc.loom_id = k.loom_no 
                                 WHERE k.loom_no = :loom_no");
    $loom_stmt->execute(['loom_no' => $loom_no]);
    $loom_data = $loom_stmt->fetch(PDO::FETCH_ASSOC);

    $current_sort = $loom_data['current_sort'] ?? 'DefaultSort';
    $current_cut_beam = $loom_data['loom_id'] ?? 'DefaultBeam';

    // Insert into knotting table
    $sql = "INSERT INTO knotting (date, shift, loom_no, loom_date_shift, current_sort, cut_beam_no, cut_beam_meters, new_beam_no) 
            VALUES (:date, :shift, :loom_no, :loom_date_shift, :current_sort, :cut_beam_no, :cut_beam_meters, :new_beam_no)";
    $stmt = $conn->prepare($sql);
    $result = $stmt->execute([
        'date' => $date,
        'shift' => $shift,
        'loom_no' => $loom_no,
        'loom_date_shift' => "{$loom_no}_{$shift}_{$date}",
        'current_sort' => $current_sort,
        'cut_beam_no' => $current_cut_beam,
        'cut_beam_meters' => $cut_beam_meters,
        'new_beam_no' => $new_beam_no
    ]);

    if ($result) {
        echo "<p class='success'>Form submitted successfully!</p>";
    } else {
        echo "<p class='error'>Error submitting form.</p>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Knotting Form</title>
    <link rel="stylesheet" href="css/knotting_form.css">
    <script>
        // Preload loom sort and cut beam data into JavaScript
        const loomSorts = <?= json_encode($loom_sorts, JSON_HEX_TAG); ?>;

        // Create a mapping of loom numbers to current sorts and cut beams
        const loomDataMap = {};
        loomSorts.forEach(row => {
            loomDataMap[row.loom_no] = {
                current_sort: row.current_sort || 'No Sort Available',
                cut_beam: row.loom_id || 'N/A'
            };
        });

        // Update current sort and cut beam dynamically when loom is selected
        document.addEventListener('DOMContentLoaded', function () {
            const loomSelect = document.getElementById('loom_no');
            const currentSortSpan = document.getElementById('current_sort');
            const currentCutBeamSpan = document.getElementById('current_cut_beam');

            loomSelect.addEventListener('change', function () {
                const loomNo = this.value;
                if (loomNo && loomDataMap[loomNo]) {
                    currentSortSpan.textContent = loomDataMap[loomNo].current_sort;
                    currentCutBeamSpan.textContent = loomDataMap[loomNo].cut_beam;
                } else {
                    currentSortSpan.textContent = 'Select a loom to display sort';
                    currentCutBeamSpan.textContent = 'N/A';
                }
            });
        });
    </script>
</head>
    <body>
        <div class="container">
            <h2>Knotting Form</h2>
            <form  action="" method="POST">
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
                        <label for="loom_no">Loom:</label>
                        <select id="loom_no" name="loom_no" required>
                            <option value="select">select</option>
                                <?php while ($loom = $looms->fetch(PDO::FETCH_ASSOC)) { ?>
                                    <option value="<?= htmlspecialchars($loom['loom_no']) ?>"><?= htmlspecialchars($loom['loom_no']) ?></option>
                                <?php } ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="cut_beam_meters">Cut Beam Meters:</label>
                        <input type="number" id="cut_beam_meters" name="cut_beam_meters" required>
                    </div>
                </div>
                <!-- Row 2 -->
                <div class="form-row">
                   
                    <div class="form-group">
                        <label for="new_beam_no">New Beam No:</label>
                        <select id="new_beam_no" name="new_beam_no" required>
                            <?php while ($beam = $new_beams->fetch(PDO::FETCH_ASSOC)) { ?>
                                <option value="<?= htmlspecialchars($beam['new_beam_no']) ?>">
                                    <?= htmlspecialchars($beam['new_beam_no']) ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="sort">Sort:</label>
                        <span id="current_sort"><?= htmlspecialchars($current_sort) ?></span>
                    </div>
                    <div class="form-group">
                        <label for="cut_beam">Cut Beam:</label>
                        <span id="current_cut_beam"><?= htmlspecialchars($current_cut_beam) ?></span>
                    </div>
                    <div class="form-group">
                        <label for=""></label>
                    </div>
                </div>
                <button type="submit" class="submit-btn">Submit</button>
            </form>
        </div>    
    </body>
</html>
