<?php
include 'auth_check.php';
require_once 'dbconnect_master.php';
include 'navbar.php';
$connection = new MasterConnection();
$conn = $connection->getConnection();
$message = ''; // Initialize the message variable
$next_s1_no = 1; // Default value for s1_no if the table is empty
try {
    $stmt = $conn->query("SELECT MAX(s1_no) AS last_s1_no FROM sort_code");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result && $result['last_s1_no'] !== null) {
        $next_s1_no = $result['last_s1_no'] + 1;
    }
} catch (PDOException $e) {
    $message = '<div class="error">Error fetching s1_no: ' . htmlspecialchars($e->getMessage()) . '</div>';
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $sort_code = $_POST['sort_code'];
        $warp_count = $_POST['warp_count'];
        $warp_count_unit = $_POST['warp_count_unit'];
        $weft_count = $_POST['weft_count'];
        $weft_count_unit = $_POST['weft_count_unit'];
        $epi = $_POST['epi'];
        $ppi = $_POST['ppi'];
        $ply = $_POST['ply'];
        $weave = $_POST['weave'];
        $sql = "INSERT INTO sort_code (
            s1_no, sort_code, warp_count, warp_count_unit, 
            weft_count, weft_count_unit, epi, ppi, ply, weave
            ) VALUES (
            :s1_no, :sort_code, :warp_count, :warp_count_unit, 
            :weft_count, :weft_count_unit, :epi, :ppi, :ply, :weave
            )";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':s1_no' => $next_s1_no,
            ':sort_code' => $sort_code,
            ':warp_count' => $warp_count,
            ':warp_count_unit' => $warp_count_unit,
            ':weft_count' => $weft_count,
            ':weft_count_unit' => $weft_count_unit,
            ':epi' => $epi,
            ':ppi' => $ppi,
            ':ply' => $ply,
            ':weave' => $weave,
        ]);
        $message = '<div class="success">Data inserted successfully!</div>';
        // Update the next s1_no after successful insertion
        $next_s1_no++;
    } catch (PDOException $e) {
        $message = '<div class="error">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/create_agent_buyer_sort.css">
    <title>Sort Code</title>
</head>
    <body>
        <main>
            <div class="container">
                <h1>Sort Code</h1>
                <?php if (!empty($message)) echo $message; ?>
                <form method="POST" action="">
                    <div class="buttons-container">
                        <button type="button" onclick="window.location.href='back_page.php'">Back</button>
                        <button type="submit" class="buyerBtn">Submit</button>
                    </div>
                    <div class="row">
                        <div class="form-group">
                            <label for="sort_code">Sort Code</label>
                            <input type="number" name="sort_code" id="sort_code">
                        </div>
                        <div class="form-group">
                            <label for="weave">Weave</label>
                            <input type="text" name="weave" id="weave">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="form-group">
                            <label for="warp_count">Warp Count</label>
                            <input type="number" name="warp_count" id="warp_count">
                        </div>
                        <div class="form-group">
                            <label for="warp_count_unit">Warp Count Unit</label>
                            <input type="number" name="warp_count_unit" id="warp_count_unit">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="form-group">
                            <label for="weft_count">Weft Count</label>
                            <input type="number" name="weft_count" id="weft_count">
                        </div>
                        <div class="form-group">
                            <label for="weft_count_unit">Weft Count Unit</label>
                            <input type="number" name="weft_count_unit" id="weft_count_unit">
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group">
                            <label for="epi">Epi</label>
                            <input type="number" name="epi" id="epi">
                        </div>
                        <div class="form-group">
                            <label for="ppi">Ppi</label>
                            <input type="number" name="ppi" id="ppi">
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group">
                            <label for="ply">Ply</label>
                            <input type="number" name="ply" id="ply">
                        </div>
                        <div class="form-group">
                            <label for=""></label>
                        </div>
                    </div>
                </form>
            </div>
        </main>
    </body>
</html>