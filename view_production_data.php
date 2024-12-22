<?php
    // Enable error reporting for debugging (disable in production)
    ini_set('display_errors', 1);
    error_reporting(E_ALL);

    // Include necessary files for authentication, navigation, and database connection
    include('auth_check.php');
    include('navbar.php');
    include('dbconnect.php');

    // Initialize database connection
    $db = new Connection();
    $conn = $db->getConnection();

    // Initialize variables for search filters and sorting
    $from_date = isset($_GET['from_date']) ? $_GET['from_date'] : '';
    $to_date = isset($_GET['to_date']) ? $_GET['to_date'] : '';
    $date = isset($_GET['date']) ? $_GET['date'] : '';
    $loomdate = isset($_GET['loomdate']) ? $_GET['loomdate'] : '';
    $machine = isset($_GET['machine']) ? $_GET['machine'] : '';
    $serial = isset($_GET['serial']) ? $_GET['serial'] : '';
    $nr = isset($_GET['nr']) ? $_GET['nr'] : '';
    $shift = isset($_GET['shift']) ? $_GET['shift'] : '';
    $time = isset($_GET['time']) ? $_GET['time'] : '';
    $picks = isset($_GET['picks']) ? $_GET['picks'] : '';
    $length = isset($_GET['length']) ? $_GET['length'] : '';
    $percentage = isset($_GET['percentage']) ? $_GET['percentage'] : '';
    $weaver_percentage = isset($_GET['weaver_percentage']) ? $_GET['weaver_percentage'] : '';
    $stops = isset($_GET['stops']) ? $_GET['stops'] : '';
    $stops_cmpx = isset($_GET['stops_cmpx']) ? $_GET['stops_cmpx'] : '';
    $stops_time = isset($_GET['stops_time']) ? $_GET['stops_time'] : '';
    $filling = isset($_GET['filling']) ? $_GET['filling'] : '';
    $filling_cmpx = isset($_GET['filling_cmpx']) ? $_GET['filling_cmpx'] : '';
    $filling_time = isset($_GET['filling_time']) ? $_GET['filling_time'] : '';
    $warp = isset($_GET['warp']) ? $_GET['warp'] : '';
    $warp_cmpx = isset($_GET['warp_cmpx']) ? $_GET['warp_cmpx'] : '';
    $warp_time = isset($_GET['warp_time']) ? $_GET['warp_time'] : '';
    $bobbin = isset($_GET['bobbin']) ? $_GET['bobbin'] : '';
    $bobbin_cmpx = isset($_GET['bobbin_cmpx']) ? $_GET['bobbin_cmpx'] : '';
    $bobbin_time = isset($_GET['bobbin_time']) ? $_GET['bobbin_time'] : '';
    $hand = isset($_GET['hand']) ? $_GET['hand'] : '';
    $hand_cmpx = isset($_GET['hand_cmpx']) ? $_GET['hand_cmpx'] : '';
    $hand_time = isset($_GET['hand_time']) ? $_GET['hand_time'] : '';
    $other = isset($_GET['other']) ? $_GET['other'] : '';
    $other_cmpx = isset($_GET['other_cmpx']) ? $_GET['other_cmpx'] : '';
    $other_time = isset($_GET['other_time']) ? $_GET['other_time'] : '';
    $starts = isset($_GET['starts']) ? $_GET['starts'] : '';
    $speed = isset($_GET['speed']) ? $_GET['speed'] : '';
    
    // Check if any filter is applied
    $is_filter_applied = !empty($from_date) || !empty($to_date) || !empty($serial);

      // Initialize the result array
      $result = [];

      // Determine if any filter or sorting is applied
      $is_filter_applied = !empty($from_date) 
                            || !empty($to_date) 
                            || !empty($date) 
                            || !empty($loomdate) 
                            || !empty($machine) 
                            || !empty($serial) 
                            || !empty($nr) 
                            || !empty($shift) 
                            || !empty($time) 
                            || !empty($picks) 
                            || !empty($length) 
                            || !empty($percentage) 
                            || !empty($weaver_percentage) 
                            || !empty($stops) 
                            || !empty($stops_cmpx) 
                            || !empty($stops_time) 
                            || !empty($filling) 
                            || !empty($filling_cmpx) 
                            || !empty($filling_time) 
                            || !empty($warp) 
                            || !empty($warp_cmpx) 
                            || !empty($warp_time) 
                            || !empty($bobbin) 
                            || !empty($bobbin_cmpx) 
                            || !empty($bobbin_time) 
                            || !empty($hand) 
                            || !empty($hand_cmpx) 
                            || !empty($hand_time) 
                            || !empty($other) 
                            || !empty($other_cmpx) 
                            || !empty($other_time) 
                            || !empty($starts) 
                            || !empty($speed);
      // Initialize the result array
      $result = [];
      
      // If any filter is applied, prepare and execute the SQL query
      if ($is_filter_applied) {
        $sql = "SELECT * FROM Productiondata WHERE 1=1";
        $conditions = [];

        // Build query conditions based on search filters
        if (!empty($from_date) && !empty($to_date)) {
            $sql .= " AND Date BETWEEN ? AND ?";
            $conditions[] = $from_date;
            $conditions[] = $to_date;
        } elseif (!empty($from_date)) {
            $sql .= " AND Date >= ?";
            $conditions[] = $from_date;
        } elseif (!empty($to_date)) {
            $sql .= " AND Date <= ?";
            $conditions[] = $to_date;
        }

        if (!empty($date)) {
            $sql .= " AND Date = ?";
            $conditions[] = $date;
        }
        
        if (!empty($loomdate)) {
            $sql .= " AND Loomdate = ?";
            $conditions[] = $loomdate;
        }
        
        if (!empty($machine)) {
            $sql .= " AND Machine = ?";
            $conditions[] = $machine;
        }
        
        if (!empty($serial)) {
            $sql .= " AND Serial = ?";
            $conditions[] = $serial;
        }
        
        if (!empty($nr)) {
            $sql .= " AND NR = ?";
            $conditions[] = $nr;
        }
        
        if (!empty($shift)) {
            $sql .= " AND Shift = ?";
            $conditions[] = $shift;
        }
        
        if (!empty($time)) {
            $sql .= " AND Time = ?";
            $conditions[] = $time;
        }
        
        if (!empty($picks)) {
            $sql .= " AND Picks = ?";
            $conditions[] = $picks;
        }
        
        if (!empty($length)) {
            $sql .= " AND Length = ?";
            $conditions[] = $length;
        }
        
        if (!empty($percentage)) {
            $sql .= " AND Percentage = ?";
            $conditions[] = $percentage;
        }
        
        if (!empty($weaver_percentage)) {
            $sql .= " AND Weaver_Percentage = ?";
            $conditions[] = $weaver_percentage;
        }
        
        if (!empty($stops)) {
            $sql .= " AND Stops = ?";
            $conditions[] = $stops;
        }
        
        if (!empty($stops_cmpx)) {
            $sql .= " AND StopsCMPX = ?";
            $conditions[] = $stops_cmpx;
        }
        
        if (!empty($stops_time)) {
            $sql .= " AND StopsTime = ?";
            $conditions[] = $stops_time;
        }
        
        if (!empty($filling)) {
            $sql .= " AND Filling = ?";
            $conditions[] = $filling;
        }
        
        if (!empty($filling_cmpx)) {
            $sql .= " AND FillingCMPX = ?";
            $conditions[] = $filling_cmpx;
        }
        
        if (!empty($filling_time)) {
            $sql .= " AND FillingTime = ?";
            $conditions[] = $filling_time;
        }
        
        if (!empty($warp)) {
            $sql .= " AND Warp = ?";
            $conditions[] = $warp;
        }
        
        if (!empty($warp_cmpx)) {
            $sql .= " AND WarpCMPX = ?";
            $conditions[] = $warp_cmpx;
        }
        
        if (!empty($warp_time)) {
            $sql .= " AND WarpTime = ?";
            $conditions[] = $warp_time;
        }
        
        if (!empty($bobbin)) {
            $sql .= " AND Bobbin = ?";
            $conditions[] = $bobbin;
        }
        
        if (!empty($bobbin_cmpx)) {
            $sql .= " AND BobbinCMPX = ?";
            $conditions[] = $bobbin_cmpx;
        }
        
        if (!empty($bobbin_time)) {
            $sql .= " AND BobbinTime = ?";
            $conditions[] = $bobbin_time;
        }
        
        if (!empty($hand)) {
            $sql .= " AND Hand = ?";
            $conditions[] = $hand;
        }
        
        if (!empty($hand_cmpx)) {
            $sql .= " AND HandCMPX = ?";
            $conditions[] = $hand_cmpx;
        }
        
        if (!empty($hand_time)) {
            $sql .= " AND HandTime = ?";
            $conditions[] = $hand_time;
        }
        
        if (!empty($other)) {
            $sql .= " AND Other = ?";
            $conditions[] = $other;
        }
        
        if (!empty($other_cmpx)) {
            $sql .= " AND OtherCMPX = ?";
            $conditions[] = $other_cmpx;
        }
        
        if (!empty($other_time)) {
            $sql .= " AND OtherTime = ?";
            $conditions[] = $other_time;
        }
        
        if (!empty($starts)) {
            $sql .= " AND Starts = ?";
            $conditions[] = $starts;
        }
        
        if (!empty($speed)) {
            $sql .= " AND Speed = ?";
            $conditions[] = $speed;
        }

        // Apply ordering by date in ascending order
        $sql .= " ORDER BY Date ASC";


        try {
            // Prepare the statement
            $stmt = $conn->prepare($sql);
            // Execute with parameters
            $stmt->execute($conditions);
            // Fetch results
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Handle SQL errors gracefully
            die("Fatal error: " . $e->getMessage());
        }
    }

    $distinct_queries = [
        "SELECT DISTINCT Date FROM productiondata",
        "SELECT DISTINCT Loomdate FROM productiondata",
        "SELECT DISTINCT Machine FROM productiondata",
        "SELECT DISTINCT Serial FROM productiondata",
        "SELECT DISTINCT NR FROM productiondata",
        "SELECT DISTINCT Shift FROM productiondata",
        "SELECT DISTINCT Time FROM productiondata",
        "SELECT DISTINCT Picks FROM productiondata",
        "SELECT DISTINCT Length FROM productiondata",
        "SELECT DISTINCT Percentage FROM productiondata",
        "SELECT DISTINCT Weaver_Percentage FROM productiondata",
        "SELECT DISTINCT Stops FROM productiondata",
        "SELECT DISTINCT StopsCMPX FROM productiondata",
        "SELECT DISTINCT StopsTime FROM productiondata",
        "SELECT DISTINCT Filling FROM productiondata",
        "SELECT DISTINCT FillingCMPX FROM productiondata",
        "SELECT DISTINCT FillingTime FROM productiondata",
        "SELECT DISTINCT Warp FROM productiondata",
        "SELECT DISTINCT WarpCMPX FROM productiondata",
        "SELECT DISTINCT WarpTime FROM productiondata",
        "SELECT DISTINCT Bobbin FROM productiondata",
        "SELECT DISTINCT BobbinCMPX FROM productiondata",
        "SELECT DISTINCT BobbinTime FROM productiondata",
        "SELECT DISTINCT Hand FROM productiondata",
        "SELECT DISTINCT HandCMPX FROM productiondata",
        "SELECT DISTINCT HandTime FROM productiondata",
        "SELECT DISTINCT Other FROM productiondata",
        "SELECT DISTINCT OtherCMPX FROM productiondata",
        "SELECT DISTINCT OtherTime FROM productiondata",
        "SELECT DISTINCT Starts FROM productiondata",
        "SELECT DISTINCT Speed FROM productiondata"
    ];
    
    // Fetch distinct values for dropdown filters
   

    // Initialize an array to hold all distinct results
    $distinct_results = [];
    foreach ($distinct_queries as $query) {
        $distinct_results[] = $conn->query($query);
    }

    // Assign each distinct result to a specific variable for clarity
    list(
        $distinct_dates_result,
        $distinct_loomdates_result,
        $distinct_machines_result,
        $distinct_serials_result,
        $distinct_nrs_result,
        $distinct_shifts_result,
        $distinct_times_result,
        $distinct_picks_result,
        $distinct_lengths_result,
        $distinct_percentages_result,
        $distinct_weaver_percentages_result,
        $distinct_stops_result,
        $distinct_stops_cmpx_result,
        $distinct_stops_time_result,
        $distinct_fillings_result,
        $distinct_filling_cmpx_result,
        $distinct_filling_time_result,
        $distinct_warps_result,
        $distinct_warp_cmpx_result,
        $distinct_warp_time_result,
        $distinct_bobbins_result,
        $distinct_bobbin_cmpx_result,
        $distinct_bobbin_time_result,
        $distinct_hands_result,
        $distinct_hand_cmpx_result,
        $distinct_hand_time_result,
        $distinct_others_result,
        $distinct_other_cmpx_result,
        $distinct_other_time_result,
        $distinct_starts_result,
        $distinct_speeds_result
    ) = $distinct_results;

    
    // Initialize sums and counts for totals and averages
    $total_picks = $total_length = $total_percentage = $total_weaver_percentage = 0;
    $total_stops = $total_stops_cmpx = $total_stops_time = 0;
    $total_filling = $total_filling_cmpx = $total_filling_time = 0;
    $total_warp = $total_warp_cmpx = $total_warp_time = 0;
    $total_bobbin = $total_bobbin_cmpx = $total_bobbin_time = 0;
    $total_hand = $total_hand_cmpx = $total_hand_time = 0;
    $total_other = $total_other_cmpx = $total_other_time = 0;
    $total_starts = $total_speed = 0;
    $total_rows = 0;
    
    // Function to format dates
    function format_date($date) {
        return date('d/m/Y', strtotime($date));
    }

    // Function to handle null or empty values
    function handle_null($value) {
        return $value === null || $value === '' ? 'N/A' : htmlspecialchars($value);
    }
?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Production Data Management</title>
        <link rel="stylesheet" href="css/view_production_data.css">
    </head>
    <body>
    <main>
        <section class="production-report">
            <h2>Production Data</h2>
            
            <form method="GET" action="" class="search-form">
                <!-- From Date Input -->
                <label for="from_date">From Date:</label>
                <input type="date" id="from_date" name="from_date" value="<?php echo htmlspecialchars($from_date); ?>">
                
                <!-- To Date Input -->
                <label for="to_date">To Date:</label>
                <input type="date" id="to_date" name="to_date" value="<?php echo htmlspecialchars($to_date); ?>">

                <!-- Sort Code Input -->
                <label for="sort_code">Serial:</label>
                <input type="text" id="serial" name="serial" value="<?php echo htmlspecialchars($serial); ?>">

                <!-- Search Button -->
                <button type="submit" class="btn-search">Search</button>
                
               <!-- Download Buttons -->
            <div class="dropdown">
                <button type="button" class="btn-download">Download <span class="arrow">&#x25BC;</span></button>
                <div class="dropdown-content">
                    <?php if ($is_filter_applied): ?>
                        <a href="download_excel_production_data.php?<?php echo http_build_query($_GET); ?>">Excel</a>
                        <a href="download_pdf_production_data.php?<?php echo http_build_query($_GET); ?>" target="_blank" rel="noopener noreferrer">PDF</a> <!-- Ensure target="_blank" is present -->
                    <?php else: ?>
                        <p style="color: red;">No filters applied. Please apply a filter to download data.</p>
                    <?php endif; ?>
                </div>
            </div>
            </form> 
            <div class="table-container">
                <div class="dropdown-container">
                    <table>
                    <thead>
                        <tr>
                            <th>
                                Date
                                    <div class="dropdown">
                                        <span class="dropdown-btn"><span class="arrow">&#x25BC;</span></span>
                                        <div class="dropdown-content">
                                            <?php if (!empty($distinct_dates_result)): ?>
                                                <?php foreach ($distinct_dates_result as $date_row): ?>
                                                    <a href="?date=<?php echo urlencode($date_row['Date']); ?>">
                                                        <?php echo format_date($date_row['Date']); ?>
                                                    </a>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <p>No data available</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </th>
                                <th>
                                    Loomdate
                                    <div class="dropdown">
                                        <span class="dropdown-btn"><span class="arrow">&#x25BC;</span></span>
                                        <div class="dropdown-content">
                                            <?php if (!empty($distinct_loomdates_result)): ?>
                                                <?php foreach ($distinct_loomdates_result as $loomdate_row): ?>
                                                    <a href="?loomdate=<?php echo urlencode($loomdate_row['Loomdate']); ?>">
                                                        <?php echo $loomdate_row['Loomdate']; ?>
                                                    </a>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <p>No data available</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </th>
                                <th>
                                    Machine
                                    <div class="dropdown">
                                        <span class="dropdown-btn"><span class="arrow">&#x25BC;</span></span>
                                        <div class="dropdown-content">
                                            <?php if (!empty($distinct_machines_result)): ?>
                                                <?php foreach ($distinct_machines_result as $machine_row): ?>
                                                    <a href="?machine=<?php echo urlencode($machine_row['Machine']); ?>">
                                                        <?php echo $machine_row['Machine']; ?>
                                                    </a>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <p>No data available</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </th>
                                <th>
                                    Serial
                                    <div class="dropdown">
                                        <span class="dropdown-btn"><span class="arrow">&#x25BC;</span></span>
                                        <div class="dropdown-content">
                                            <?php if (!empty($distinct_serials_result)): ?>
                                                <?php foreach ($distinct_serials_result as $serial_row): ?>
                                                    <a href="?serial=<?php echo urlencode($serial_row['Serial']); ?>">
                                                        <?php echo $serial_row['Serial']; ?>
                                                    </a>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <p>No data available</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </th>
                                <th>
                                    NR
                                    <div class="dropdown">
                                        <span class="dropdown-btn"><span class="arrow">&#x25BC;</span></span>
                                        <div class="dropdown-content">
                                            <?php if (!empty($distinct_nrs_result)): ?>
                                                <?php foreach ($distinct_nrs_result as $nr_row): ?>
                                                    <a href="?nr=<?php echo urlencode($nr_row['NR']); ?>">
                                                        <?php echo $nr_row['NR']; ?>
                                                    </a>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <p>No data available</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </th>
                                <th>
                                    Shift
                                    <div class="dropdown">
                                        <span class="dropdown-btn"><span class="arrow">&#x25BC;</span></span>
                                        <div class="dropdown-content">
                                            <?php if (!empty($distinct_shifts_result)): ?>
                                                <?php foreach ($distinct_shifts_result as $shift_row): ?>
                                                    <a href="?shift=<?php echo urlencode($shift_row['Shift']); ?>">
                                                        <?php echo $shift_row['Shift']; ?>
                                                    </a>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <p>No data available</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </th>
                                <th>
                                    Time
                                    <div class="dropdown">
                                        <span class="dropdown-btn"><span class="arrow">&#x25BC;</span></span>
                                        <div class="dropdown-content">
                                            <?php if (!empty($distinct_times_result)): ?>
                                                <?php foreach ($distinct_times_result as $time_row): ?>
                                                    <a href="?time=<?php echo urlencode($time_row['Time']); ?>">
                                                        <?php echo $time_row['Time']; ?>
                                                    </a>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <p>No data available</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </th>
                                <th>
                                    Picks
                                    <div class="dropdown">
                                        <span class="dropdown-btn"><span class="arrow">&#x25BC;</span></span>
                                        <div class="dropdown-content">
                                            <?php if (!empty($distinct_picks_result)): ?>
                                                <?php foreach ($distinct_picks_result as $picks_row): ?>
                                                    <a href="?picks=<?php echo urlencode($picks_row['Picks']); ?>">
                                                        <?php echo $picks_row['Picks']; ?>
                                                    </a>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <p>No data available</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </th>
                                <th>
                                    Length
                                    <div class="dropdown">
                                        <span class="dropdown-btn"><span class="arrow">&#x25BC;</span></span>
                                        <div class="dropdown-content">
                                            <?php if (!empty($distinct_lengths_result)): ?>
                                                <?php foreach ($distinct_lengths_result as $length_row): ?>
                                                    <a href="?length=<?php echo urlencode($length_row['Length']); ?>">
                                                        <?php echo $length_row['Length']; ?>
                                                    </a>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <p>No data available</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </th>
                                <th>
                                    Percentage
                                    <div class="dropdown">
                                        <span class="dropdown-btn"><span class="arrow">&#x25BC;</span></span>
                                        <div class="dropdown-content">
                                            <?php if (!empty($distinct_percentages_result)): ?>
                                                <?php foreach ($distinct_percentages_result as $percentage_row): ?>
                                                    <a href="?percentage=<?php echo urlencode($percentage_row['Percentage']); ?>">
                                                        <?php echo $percentage_row['Percentage']; ?>
                                                    </a>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <p>No data available</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </th>
                                <th>
                                    Weaver_Percentage
                                    <div class="dropdown">
                                        <span class="dropdown-btn"><span class="arrow">&#x25BC;</span></span>
                                        <div class="dropdown-content">
                                            <?php if (!empty($distinct_weaver_percentages_result)): ?>
                                                <?php foreach ($distinct_weaver_percentages_result as $weaver_percentage_row): ?>
                                                    <a href="?weaver_percentage=<?php echo urlencode($weaver_percentage_row['Weaver_Percentage']); ?>">
                                                        <?php echo $weaver_percentage_row['Weaver_Percentage']; ?>
                                                    </a>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <p>No data available</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </th>
                                <th>
                                    Stops
                                    <div class="dropdown">
                                        <span class="dropdown-btn"><span class="arrow">&#x25BC;</span></span>
                                        <div class="dropdown-content">
                                            <?php if (!empty($distinct_stops_result)): ?>
                                                <?php foreach ($distinct_stops_result as $stops_row): ?>
                                                    <a href="?stops=<?php echo urlencode($stops_row['Stops']); ?>">
                                                        <?php echo $stops_row['Stops']; ?>
                                                    </a>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <p>No data available</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </th>
                                <th>
                                StopsCMPX
                                <div class="dropdown">
                                    <span class="dropdown-btn"><span class="arrow">&#x25BC;</span></span>
                                    <div class="dropdown-content">
                                        <?php if (!empty($distinct_stops_cmpx_result)): ?>
                                            <?php foreach ($distinct_stops_cmpx_result as $stops_cmpx_row): ?>
                                                <a href="?stops_cmpx=<?php echo urlencode($stops_cmpx_row['StopsCMPX']); ?>">
                                                    <?php echo $stops_cmpx_row['StopsCMPX']; ?>
                                                </a>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <p>No data available</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </th>

                            <th>
                                StopsTime
                                <div class="dropdown">
                                    <span class="dropdown-btn"><span class="arrow">&#x25BC;</span></span>
                                    <div class="dropdown-content">
                                        <?php if (!empty($distinct_stops_time_result)): ?>
                                            <?php foreach ($distinct_stops_time_result as $stops_time_row): ?>
                                                <a href="?stops_time=<?php echo urlencode($stops_time_row['StopsTime']); ?>">
                                                    <?php echo $stops_time_row['StopsTime']; ?>
                                                </a>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <p>No data available</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </th>

                            <th>
                                Filling
                                <div class="dropdown">
                                    <span class="dropdown-btn"><span class="arrow">&#x25BC;</span></span>
                                    <div class="dropdown-content">
                                        <?php if (!empty($distinct_filling_result)): ?>
                                            <?php foreach ($distinct_filling_result as $filling_row): ?>
                                                <a href="?filling=<?php echo urlencode($filling_row['Filling']); ?>">
                                                    <?php echo $filling_row['Filling']; ?>
                                                </a>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <p>No data available</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </th>

                            <th>
                                FillingCMPX
                                <div class="dropdown">
                                    <span class="dropdown-btn"><span class="arrow">&#x25BC;</span></span>
                                    <div class="dropdown-content">
                                        <?php if (!empty($distinct_filling_cmpx_result)): ?>
                                            <?php foreach ($distinct_filling_cmpx_result as $filling_cmpx_row): ?>
                                                <a href="?filling_cmpx=<?php echo urlencode($filling_cmpx_row['FillingCMPX']); ?>">
                                                    <?php echo $filling_cmpx_row['FillingCMPX']; ?>
                                                </a>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <p>No data available</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </th>

                            <th>
                                FillingTime
                                <div class="dropdown">
                                    <span class="dropdown-btn"><span class="arrow">&#x25BC;</span></span>
                                    <div class="dropdown-content">
                                        <?php if (!empty($distinct_filling_time_result)): ?>
                                            <?php foreach ($distinct_filling_time_result as $filling_time_row): ?>
                                                <a href="?filling_time=<?php echo urlencode($filling_time_row['FillingTime']); ?>">
                                                    <?php echo $filling_time_row['FillingTime']; ?>
                                                </a>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <p>No data available</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </th>

                            <th>
                                Warp
                                <div class="dropdown">
                                    <span class="dropdown-btn"><span class="arrow">&#x25BC;</span></span>
                                    <div class="dropdown-content">
                                        <?php if (!empty($distinct_warp_result)): ?>
                                            <?php foreach ($distinct_warp_result as $warp_row): ?>
                                                <a href="?warp=<?php echo urlencode($warp_row['Warp']); ?>">
                                                    <?php echo $warp_row['Warp']; ?>
                                                </a>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <p>No data available</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </th>

                            <th>
                                WarpCMPX
                                <div class="dropdown">
                                    <span class="dropdown-btn"><span class="arrow">&#x25BC;</span></span>
                                    <div class="dropdown-content">
                                        <?php if (!empty($distinct_warp_cmpx_result)): ?>
                                            <?php foreach ($distinct_warp_cmpx_result as $warp_cmpx_row): ?>
                                                <a href="?warp_cmpx=<?php echo urlencode($warp_cmpx_row['WarpCMPX']); ?>">
                                                    <?php echo $warp_cmpx_row['WarpCMPX']; ?>
                                                </a>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <p>No data available</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </th>

                            <th>
                                WarpTime
                                <div class="dropdown">
                                    <span class="dropdown-btn"><span class="arrow">&#x25BC;</span></span>
                                    <div class="dropdown-content">
                                        <?php if (!empty($distinct_warp_time_result)): ?>
                                            <?php foreach ($distinct_warp_time_result as $warp_time_row): ?>
                                                <a href="?warp_time=<?php echo urlencode($warp_time_row['WarpTime']); ?>">
                                                    <?php echo $warp_time_row['WarpTime']; ?>
                                                </a>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <p>No data available</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </th>

                            <th>
                                Bobbin
                                <div class="dropdown">
                                    <span class="dropdown-btn"><span class="arrow">&#x25BC;</span></span>
                                    <div class="dropdown-content">
                                        <?php if (!empty($distinct_bobbin_result)): ?>
                                            <?php foreach ($distinct_bobbin_result as $bobbin_row): ?>
                                                <a href="?bobbin=<?php echo urlencode($bobbin_row['Bobbin']); ?>">
                                                    <?php echo $bobbin_row['Bobbin']; ?>
                                                </a>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <p>No data available</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </th>

                            <th>
                                BobbinCMPX
                                <div class="dropdown">
                                    <span class="dropdown-btn"><span class="arrow">&#x25BC;</span></span>
                                    <div class="dropdown-content">
                                        <?php if (!empty($distinct_bobbin_cmpx_result)): ?>
                                            <?php foreach ($distinct_bobbin_cmpx_result as $bobbin_cmpx_row): ?>
                                                <a href="?bobbin_cmpx=<?php echo urlencode($bobbin_cmpx_row['BobbinCMPX']); ?>">
                                                    <?php echo $bobbin_cmpx_row['BobbinCMPX']; ?>
                                                </a>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <p>No data available</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </th>

                            <th>
                                BobbinTime
                                <div class="dropdown">
                                    <span class="dropdown-btn"><span class="arrow">&#x25BC;</span></span>
                                    <div class="dropdown-content">
                                        <?php if (!empty($distinct_bobbin_time_result)): ?>
                                            <?php foreach ($distinct_bobbin_time_result as $bobbin_time_row): ?>
                                                <a href="?bobbin_time=<?php echo urlencode($bobbin_time_row['BobbinTime']); ?>">
                                                    <?php echo $bobbin_time_row['BobbinTime']; ?>
                                                </a>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <p>No data available</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </th>

                            <th>
                                Hand
                                <div class="dropdown">
                                    <span class="dropdown-btn"><span class="arrow">&#x25BC;</span></span>
                                    <div class="dropdown-content">
                                        <?php if (!empty($distinct_hand_result)): ?>
                                            <?php foreach ($distinct_hand_result as $hand_row): ?>
                                                <a href="?hand=<?php echo urlencode($hand_row['Hand']); ?>">
                                                    <?php echo $hand_row['Hand']; ?>
                                                </a>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <p>No data available</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </th>

                            <th>
                                HandCMPX
                                <div class="dropdown">
                                    <span class="dropdown-btn"><span class="arrow">&#x25BC;</span></span>
                                    <div class="dropdown-content">
                                        <?php if (!empty($distinct_hand_cmpx_result)): ?>
                                            <?php foreach ($distinct_hand_cmpx_result as $hand_cmpx_row): ?>
                                                <a href="?hand_cmpx=<?php echo urlencode($hand_cmpx_row['HandCMPX']); ?>">
                                                    <?php echo $hand_cmpx_row['HandCMPX']; ?>
                                                </a>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <p>No data available</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </th>

                            <th>
                                HandTime
                                <div class="dropdown">
                                    <span class="dropdown-btn"><span class="arrow">&#x25BC;</span></span>
                                    <div class="dropdown-content">
                                        <?php if (!empty($distinct_hand_time_result)): ?>
                                            <?php foreach ($distinct_hand_time_result as $hand_time_row): ?>
                                                <a href="?hand_time=<?php echo urlencode($hand_time_row['HandTime']); ?>">
                                                    <?php echo $hand_time_row['HandTime']; ?>
                                                </a>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <p>No data available</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </th>

                            <th>
                                Other
                                <div class="dropdown">
                                    <span class="dropdown-btn"><span class="arrow">&#x25BC;</span></span>
                                    <div class="dropdown-content">
                                        <?php if (!empty($distinct_other_result)): ?>
                                            <?php foreach ($distinct_other_result as $other_row): ?>
                                                <a href="?other=<?php echo urlencode($other_row['Other']); ?>">
                                                    <?php echo $other_row['Other']; ?>
                                                </a>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <p>No data available</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </th>

                            <th>
                                OtherCMPX
                                <div class="dropdown">
                                    <span class="dropdown-btn"><span class="arrow">&#x25BC;</span></span>
                                    <div class="dropdown-content">
                                        <?php if (!empty($distinct_other_cmpx_result)): ?>
                                            <?php foreach ($distinct_other_cmpx_result as $other_cmpx_row): ?>
                                                <a href="?other_cmpx=<?php echo urlencode($other_cmpx_row['OtherCMPX']); ?>">
                                                    <?php echo $other_cmpx_row['OtherCMPX']; ?>
                                                </a>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <p>No data available</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </th>

                            <th>
                                OtherTime
                                <div class="dropdown">
                                    <span class="dropdown-btn"><span class="arrow">&#x25BC;</span></span>
                                    <div class="dropdown-content">
                                        <?php if (!empty($distinct_other_time_result)): ?>
                                            <?php foreach ($distinct_other_time_result as $other_time_row): ?>
                                                <a href="?other_time=<?php echo urlencode($other_time_row['OtherTime']); ?>">
                                                    <?php echo $other_time_row['OtherTime']; ?>
                                                </a>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <p>No data available</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </th>

                            <th>
                                Starts
                                <div class="dropdown">
                                    <span class="dropdown-btn"><span class="arrow">&#x25BC;</span></span>
                                    <div class="dropdown-content">
                                        <?php if (!empty($distinct_starts_result)): ?>
                                            <?php foreach ($distinct_starts_result as $starts_row): ?>
                                                <a href="?starts=<?php echo urlencode($starts_row['Starts']); ?>">
                                                    <?php echo $starts_row['Starts']; ?>
                                                </a>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <p>No data available</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </th>

                            <th>
                                Speed
                                <div class="dropdown">
                                    <span class="dropdown-btn"><span class="arrow">&#x25BC;</span></span>
                                    <div class="dropdown-content">
                                        <?php if (!empty($distinct_speed_result)): ?>
                                            <?php foreach ($distinct_speed_result as $speed_row): ?>
                                                <a href="?speed=<?php echo urlencode($speed_row['Speed']); ?>">
                                                    <?php echo $speed_row['Speed']; ?>
                                                </a>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <p>No data available</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <?php if ($is_filter_applied): ?>
                        <?php if (!empty($result)): ?>
                    <tbody>
                        <?php
                            // Initialize sums and counts
                            $total_picks = $total_length = $total_percentage = $total_weaver_percentage = 0;
                            $total_stops = $total_stopscmpx = $total_stopstime = 0;
                            $total_filling = $total_fillingcmpx = $total_fillingtime = 0;
                            $total_warp = $total_warpcmpx = $total_warptime = 0;
                            $total_bobbin = $total_bobbincmpx = $total_bobbintime = 0;
                            $total_hand = $total_handcmpx = $total_handtime = 0;
                            $total_other = $total_othercmpx = $total_othertime = 0;
                            $total_starts = $total_speed = 0;
                            $total_rows = 0;
                        ?>
                        <?php if (is_array($result) && count($result) > 0): ?>
                            <?php foreach ($result as $row): ?>
                                <tr>
                                    <td><?php echo format_date($row['Date']); ?></td>
                                    <td><?php echo handle_null($row['Loomdate']); ?></td>
                                    <td><?php echo handle_null($row['Machine']); ?></td>
                                    <td><?php echo handle_null($row['Serial']); ?></td>
                                    <td><?php echo handle_null($row['NR']); ?></td>
                                    <td><?php echo handle_null($row['Shift']); ?></td>
                                    <td><?php echo handle_null($row['Time']); ?></td>
                                    <td><?php echo handle_null($row['Picks']); $total_picks += $row['Picks']; ?></td>
                                    <td><?php echo handle_null($row['Length']); $total_length += $row['Length']; ?></td>
                                    <td><?php echo handle_null($row['Percentage']); $total_percentage += $row['Percentage']; ?></td>
                                    <td><?php echo handle_null($row['Weaver_Percentage']); $total_weaver_percentage += $row['Weaver_Percentage']; ?></td>
                                    <td><?php echo handle_null($row['Stops']); $total_stops += $row['Stops']; ?></td>
                                    <td><?php echo handle_null($row['StopsCMPX']); $total_stopscmpx += $row['StopsCMPX']; ?></td>
                                    <td><?php echo handle_null($row['StopsTime']); $total_stopstime += $row['StopsTime']; ?></td>
                                    <td><?php echo handle_null($row['Filling']); $total_filling += $row['Filling']; ?></td>
                                    <td><?php echo handle_null($row['FillingCMPX']); $total_fillingcmpx += $row['FillingCMPX']; ?></td>
                                    <td><?php echo handle_null($row['FillingTime']); $total_fillingtime += $row['FillingTime']; ?></td>
                                    <td><?php echo handle_null($row['Warp']); $total_warp += $row['Warp']; ?></td>
                                    <td><?php echo handle_null($row['WarpCMPX']); $total_warpcmpx += $row['WarpCMPX']; ?></td>
                                    <td><?php echo handle_null($row['WarpTime']); $total_warptime += $row['WarpTime']; ?></td>
                                    <td><?php echo handle_null($row['Bobbin']); $total_bobbin += $row['Bobbin']; ?></td>
                                    <td><?php echo handle_null($row['BobbinCMPX']); $total_bobbincmpx += $row['BobbinCMPX']; ?></td>
                                    <td><?php echo handle_null($row['BobbinTime']); $total_bobbintime += $row['BobbinTime']; ?></td>
                                    <td><?php echo handle_null($row['Hand']); $total_hand += $row['Hand']; ?></td>
                                    <td><?php echo handle_null($row['HandCMPX']); $total_handcmpx += $row['HandCMPX']; ?></td>
                                    <td><?php echo handle_null($row['HandTime']); $total_handtime += $row['HandTime']; ?></td>
                                    <td><?php echo handle_null($row['Other']); $total_other += $row['Other']; ?></td>
                                    <td><?php echo handle_null($row['OtherCMPX']); $total_othercmpx += $row['OtherCMPX']; ?></td>
                                    <td><?php echo handle_null($row['OtherTime']); $total_othertime += $row['OtherTime']; ?></td>
                                    <td><?php echo handle_null($row['Starts']); $total_starts += $row['Starts']; ?></td>
                                    <td><?php echo handle_null($row['Speed']); $total_speed += $row['Speed']; ?></td>
                                </tr>
                                <?php $total_rows++; ?>
                            <?php endforeach; ?>
                            
                            <!-- Totals row -->
                            <tr>
                                <td colspan="6"></td> <!-- Empty cells before "Total" -->
                                <td>Total</td> <!-- Total text at the end -->

                                <td><?php echo $total_picks; ?></td> <!-- Sum of Picks -->
                                <td><?php echo $total_length; ?></td> <!-- Sum of Length -->

                                <!-- Check if $total_rows is greater than 0 before dividing for averages -->
                                <td><?php echo $total_rows > 0 ? number_format($total_percentage / $total_rows, 2) : 0; ?></td> <!-- Average of Percentage -->
                                <td><?php echo $total_rows > 0 ? number_format($total_weaver_percentage / $total_rows, 2) : 0; ?></td> <!-- Average of Weaver_Percentage -->
                                <td><?php echo $total_rows > 0 ? number_format($total_stops / $total_rows, 2) : 0; ?></td> <!-- Average of Stops -->
                                <td><?php echo $total_rows > 0 ? number_format($total_stopscmpx / $total_rows, 2) : 0; ?></td> <!-- Average of StopsCMPX -->
                                <td><?php echo $total_rows > 0 ? number_format($total_stopstime / $total_rows, 2) : 0; ?></td> <!-- Average of StopsTime -->
                                <td><?php echo $total_rows > 0 ? number_format($total_filling / $total_rows, 2) : 0; ?></td> <!-- Average of Filling -->
                                <td><?php echo $total_rows > 0 ? number_format($total_fillingcmpx / $total_rows, 2) : 0; ?></td> <!-- Average of FillingCMPX -->
                                <td><?php echo $total_rows > 0 ? number_format($total_fillingtime / $total_rows, 2) : 0; ?></td> <!-- Average of FillingTime -->
                                <td><?php echo $total_rows > 0 ? number_format($total_warp / $total_rows, 2) : 0; ?></td> <!-- Average of Warp -->
                                <td><?php echo $total_rows > 0 ? number_format($total_warpcmpx / $total_rows, 2) : 0; ?></td> <!-- Average of WarpCMPX -->
                                <td><?php echo $total_rows > 0 ? number_format($total_warptime / $total_rows, 2) : 0; ?></td> <!-- Average of WarpTime -->
                                <td><?php echo $total_rows > 0 ? number_format($total_bobbin / $total_rows, 2) : 0; ?></td> <!-- Average of Bobbin -->
                                <td><?php echo $total_rows > 0 ? number_format($total_bobbincmpx / $total_rows, 2) : 0; ?></td> <!-- Average of BobbinCMPX -->
                                <td><?php echo $total_rows > 0 ? number_format($total_bobbintime / $total_rows, 2) : 0; ?></td> <!-- Average of BobbinTime -->
                                <td><?php echo $total_rows > 0 ? number_format($total_hand / $total_rows, 2) : 0; ?></td> <!-- Average of Hand -->
                                <td><?php echo $total_rows > 0 ? number_format($total_handcmpx / $total_rows, 2) : 0; ?></td> <!-- Average of HandCMPX -->
                                <td><?php echo $total_rows > 0 ? number_format($total_handtime / $total_rows, 2) : 0; ?></td> <!-- Average of HandTime -->
                                <td><?php echo $total_rows > 0 ? number_format($total_other / $total_rows, 2) : 0; ?></td> <!-- Average of Other -->
                                <td><?php echo $total_rows > 0 ? number_format($total_othercmpx / $total_rows, 2) : 0; ?></td> <!-- Average of OtherCMPX -->
                                <td><?php echo $total_rows > 0 ? number_format($total_othertime / $total_rows, 2) : 0; ?></td> <!-- Average of OtherTime -->
                                <td><?php echo $total_rows > 0 ? number_format($total_starts / $total_rows, 2) : 0; ?></td> <!-- Average of Starts -->
                                <td><?php echo $total_rows > 0 ? number_format($total_speed / $total_rows, 2) : 0; ?></td> <!-- Average of Speed -->
                            </tr>
                        <?php else: ?>
                            <tr>
                                <td colspan="23">No results found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <p>No data to display. Please apply search filters or sorting criteria to view the data.</p>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>
    <script>
        function downloadFile(type, event) {
            event.preventDefault();  // Prevents the default anchor action
            if (type === 'excel') {
                window.location.href = 'download_excel.php';  // Adjust the URL
            } 
            else if (type === 'pdf') {
                window.location.href = 'download_pdf.php';  // Adjust the URL
            }
        }
        <?php endif; ?>
    </script>
    </body>
    </html>
