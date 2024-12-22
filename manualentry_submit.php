<?php
if (isset($_POST['mansubmit'])) {
    include_once 'dbconnect.php';

    // Capture POST request data
    $date = $_REQUEST['date'];
    $machine = $_REQUEST['machine'];
    $nr = $_REQUEST['nr'];
    $shift = $_REQUEST['shift'];
    $time = $_REQUEST['time'];
    $picks = $_REQUEST['picks'];
    $length = $_REQUEST['length'];
    $percentage = $_REQUEST['percentage'];
    $weaver_percentage = $_REQUEST['weaver_percentage'];
    $stops = $_REQUEST['stops'];
    $stops_cmpx = $_REQUEST['stops_cmpx'];
    $stops_time = $_REQUEST['stops_time'];
    $filling = $_REQUEST['filling'];
    $filling_cmpx = $_REQUEST['filling_cmpx'];
    $filling_time = $_REQUEST['filling_time'];
    $warp = $_REQUEST['warp'];
    $warp_cmpx = $_REQUEST['warp_cmpx'];
    $warp_time = $_REQUEST['warp_time'];
    $bobbin = $_REQUEST['bobbin'];
    $bobbin_cmpx = $_REQUEST['bobbin_cmpx'];
    $bobbin_time = $_REQUEST['bobbin_time'];
    $hand = $_REQUEST['hand'];
    $hand_cmpx = $_REQUEST['hand_cmpx'];
    $hand_time = $_REQUEST['hand_time'];
    $other = $_REQUEST['other'];
    $other_cmpx = $_REQUEST['other_cmpx'];
    $other_time = $_REQUEST['other_time'];
    $starts = $_REQUEST['starts'];
    $speed = $_REQUEST['speed'];

    // // Define the serial number based on the machine
    // $machine_serial_map = [
    //     "LOOM01" => "424112",
    //     "LOOM02" => "424113",
    //     // Add all other mappings here...
    // ];

    // $serial = isset($machine_serial_map[$machine]) ? $machine_serial_map[$machine] : "NULL";

    // Create a database connection
    $database = new Connection();
    $db = $database->getConnection();

    // Check if an entry already exists for the specified Date, Machine, and Sort_code
    $prev_query = "SELECT * FROM Production_report WHERE Date = :date AND Machine = :machine AND Sort_code = :sort_code"; 
    $result = $db->prepare($prev_query);
    $result->bindParam(':date', $date);
    $result->bindParam(':machine', $machine);
    $result->bindParam(':sort_code', $sort_code);
    $result->execute();

    if ($result->rowCount() > 0) {
        echo '<script>alert("Entry exists for the given date, machine, and sort code")</script>';
        echo "<script>window.top.location='manualentry.php'</script>";
        exit();
    } else {
        // Prepare the insert statement
        $query = "INSERT INTO Production_report 
            (Date, Machine,NR,Shift,Time, Picks, Length,Percentage,Weaver_Percentage, 
            Stops, StopsCMPX, StopsTime, Filling, FillingCMPX, FillingTime, 
            Warp, WarpCMPX, WarpTime, Bobbin, BobbinCMPX, BobbinTime, 
            Hand, HandCMPX, HandTime, Other, OtherCMPX, OtherTime,Starts, Speed
            ) 
            VALUES 
            (:date, :machine, :nr, :shift, :time, :picks, 
             :length, :percentage, :weaver_percentage, :stops, :stops_cmpx, :stops_time, 
             :filling, :filling_cmpx, :filling_time, :warp, :warp_cmpx, :warp_time,
             :bobbin, :bobbin_cmpx, :bobbin_time,:hand, :hand_cmpx, :hand_time,
             :other, :other_cmpx, :other_time, :starts, :speed
            )";

        $stmt = $db->prepare($query);
        
        // Bind parameters
        $stmt->bindParam(':date', $date);
        $stmt->bindParam(':machine', $machine);
        $stmt->bindParam(':nr', $nr);
        $stmt->bindParam(':shift', $shift);
        $stmt->bindParam(':time', $time);
        $stmt->bindParam(':picks', $picks);
        $stmt->bindParam(':length', $length);
        $stmt->bindParam(':percentage', $percentage);
        $stmt->bindParam(':weaver_percentage', $weaver_percentage);
        $stmt->bindParam(':stops', $stops);
        $stmt->bindParam(':stops_cmpx', $stops_cmpx);
        $stmt->bindParam(':stops_time', $stops_time);
        $stmt->bindParam(':filling', $filling);
        $stmt->bindParam(':filling_cmpx', $filling_cmpx);
        $stmt->bindParam(':filling_time', $filling_time);
        $stmt->bindParam(':warp', $warp);
        $stmt->bindParam(':warp_cmpx', $warp_cmpx);
        $stmt->bindParam(':warp_time', $warp_time);
        $stmt->bindParam(':bobbin', $bobbin);
        $stmt->bindParam(':bobbin_cmpx', $bobbin_cmpx);
        $stmt->bindParam(':bobbin_time', $bobbin_time);
        $stmt->bindParam(':hand', $hand);
        $stmt->bindParam(':hand_cmpx', $hand_cmpx);
        $stmt->bindParam(':hand_time', $hand_time);
        $stmt->bindParam(':other', $other);
        $stmt->bindParam(':other_cmpx', $other_cmpx);
        $stmt->bindParam(':other_time', $other_time);
        $stmt->bindParam(':starts', $starts);
        $stmt->bindParam(':speed', $speed);
        
        // Execute the insert
        $stmt->execute();
        
        echo '<script>alert("Entry inserted successfully")</script>';
        echo "<script>window.top.location='manualentry.php'</script>";
        exit();
    }
} else {
    echo "<script>window.top.location='home.php'</script>";
}
?>
