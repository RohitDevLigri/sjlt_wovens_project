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
    $sort_code = isset($_GET['sort_code']) ? $_GET['sort_code'] : '';
    $construction = isset($_GET['construction']) ? $_GET['construction'] : '';
    $machine = isset($_GET['machine']) ? $_GET['machine'] : '';
    $warp_set_no = isset($_GET['warp_set_no']) ? $_GET['warp_set_no'] : '';
    $weft_lot_no = isset($_GET['weft_lot_no']) ? $_GET['weft_lot_no'] : '';
    $picks = isset($_GET['picks']) ? $_GET['picks'] : '';
    $length = isset($_GET['length']) ? $_GET['length'] : '';
    $percentage = isset($_GET['percentage']) ? $_GET['percentage'] : '';
    $f_stops = isset($_GET['f_stops']) ? $_GET['f_stops'] : '';
    $fsph = isset($_GET['fsph']) ? $_GET['fsph'] : '';
    $atpfs = isset($_GET['atpfs']) ? $_GET['atpfs'] : '';
    $fcmpx = isset($_GET['fcmpx']) ? $_GET['fcmpx'] : '';
    $w_stops = isset($_GET['w_stops']) ? $_GET['w_stops'] : '';
    $wsph = isset($_GET['wsph']) ? $_GET['wsph'] : '';
    $atpws = isset($_GET['atpws']) ? $_GET['atpws'] : '';
    $wcmpx = isset($_GET['wcmpx']) ? $_GET['wcmpx'] : '';
    $b_stops = isset($_GET['b_stops']) ? $_GET['b_stops'] : '';
    $bsph = isset($_GET['bsph']) ? $_GET['bsph'] : '';
    $atpbs = isset($_GET['atpbs']) ? $_GET['atpbs'] : '';
    $bcmpx = isset($_GET['bcmpx']) ? $_GET['bcmpx'] : '';
    $speed = isset($_GET['speed']) ? $_GET['speed'] : '';

    // Check if any filter is applied
    $is_filter_applied = !empty($from_date) || !empty($to_date) || !empty($sort_code);

      // Initialize the result array
      $result = [];

      // Determine if any filter or sorting is applied
      $is_filter_applied = !empty($from_date) || !empty($to_date) || !empty($date) || !empty($sort_code) ||
      !empty($construction) || !empty($machine) || !empty($warp_set_no) || !empty($weft_lot_no) ||
      !empty($picks) || !empty($length) || !empty($percentage) || !empty($f_stops) ||
      !empty($fsph) || !empty($atpfs) || !empty($fcmpx) || !empty($w_stops) ||
      !empty($wsph) || !empty($atpws) || !empty($wcmpx) || !empty($b_stops) ||
      !empty($bsph) || !empty($atpbs) || !empty($bcmpx) || !empty($speed);
      
      // Initialize the result array
      $result = [];
      
      // If any filter is applied, prepare and execute the SQL query
      if ($is_filter_applied) {
        $sql = "SELECT * FROM Production_Report WHERE 1=1";
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
        
        if (!empty($sort_code)) {
            $sql .= " AND Sort_code = ?";
            $conditions[] = $sort_code;
        }
        
        if (!empty($construction)) {
            $sql .= " AND Construction = ?";
            $conditions[] = $construction;
        }

        if (!empty($machine)) {
            $sql .= " AND Machine = ?";
            $conditions[] = $machine;
        }
        
        if (!empty($warp_set_no)) {
            $sql .= " AND Warp_Set_No = ?";
            $conditions[] = $warp_set_no;
        }
        
        if (!empty($weft_lot_no)) {
            $sql .= " AND Weft_Lot_No = ?";
            $conditions[] = $weft_lot_no;
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
        
        if (!empty($f_stops)) {
            $sql .= " AND F_Stops = ?";
            $conditions[] = $f_stops;
        }

        if (!empty($fsph)) {
            $sql .= " AND Fsph = ?";
            $conditions[] = $fsph;
        }
        
        if (!empty($atpfs)) {
            $sql .= " AND Atpfs = ?";
            $conditions[] = $atpfs;
        }
        
        if (!empty($fcmpx)) {
            $sql .= " AND Fcmpx = ?";
            $conditions[] = $fcmpx;
        }

        if (!empty($w_stops)) {
            $sql .= " AND W_Stops = ?";
            $conditions[] = $w_stops;
        }

        if (!empty($wsph)) {
            $sql .= " AND Wsph = ?";
            $conditions[] = $wsph;
        }

        if (!empty($atpws)) {
            $sql .= " AND Atpws = ?";
            $conditions[] = $atpws;
        }
        
        if (!empty($wcmpx)) {
            $sql .= " AND Wcmpx = ?";
            $conditions[] = $wcmpx;
        }

        if (!empty($b_stops)) {
            $sql .= " AND B_Stops = ?";
            $conditions[] = $b_stops;
        }

        if (!empty($bsph)) {
            $sql .= " AND Bsph = ?";
            $conditions[] = $bsph;
        }
        
        if (!empty($atpbs)) {
            $sql .= " AND Atpbs = ?";
            $conditions[] = $atpbs;
        }

        if (!empty($bcmpx)) {
            $sql .= " AND Bcmpx = ?";
            $conditions[] = $bcmpx;
        }
        
        if (!empty($speed)) {
            $sql .= " AND Speed = ?";
            $conditions[] = $speed;
        }

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
        "SELECT DISTINCT Date FROM Production_Report",
        "SELECT DISTINCT Sort_code FROM Production_Report",
        "SELECT DISTINCT Construction FROM Production_Report",
        "SELECT DISTINCT Machine FROM Production_Report",
        "SELECT DISTINCT Warp_Set_No FROM Production_Report",
        "SELECT DISTINCT Weft_Lot_No FROM Production_Report",
        "SELECT DISTINCT Picks FROM Production_Report",
        "SELECT DISTINCT Length FROM Production_Report",
        "SELECT DISTINCT Percentage FROM Production_Report",
        "SELECT DISTINCT F_Stops FROM Production_Report",
        "SELECT DISTINCT Fsph FROM Production_Report",
        "SELECT DISTINCT Atpfs FROM Production_Report",
        "SELECT DISTINCT Fcmpx FROM Production_Report",
        "SELECT DISTINCT W_Stops FROM Production_Report",
        "SELECT DISTINCT Wsph FROM Production_Report",
        "SELECT DISTINCT Atpws FROM Production_Report",
        "SELECT DISTINCT Wcmpx FROM Production_Report",
        "SELECT DISTINCT B_Stops FROM Production_Report",
        "SELECT DISTINCT Bsph FROM Production_Report",
        "SELECT DISTINCT Atpbs FROM Production_Report",
        "SELECT DISTINCT Bcmpx FROM Production_Report",
        "SELECT DISTINCT Speed FROM Production_Report"
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
        $distinct_sort_code_result,
        $distinct_construction_result,
        $distinct_machine_result,
        $distinct_warp_set_no_result,
        $distinct_weft_lot_no_result,
        $distinct_picks_result,
        $distinct_length_result,
        $distinct_percentage_result,
        $distinct_f_stops_result,
        $distinct_fsph_result,
        $distinct_atpfs_result,
        $distinct_fcmpx_result,
        $distinct_w_stops_result,
        $distinct_wsph_result,
        $distinct_atpws_result,
        $distinct_wcmpx_result,
        $distinct_b_stops_result,
        $distinct_bsph_result,
        $distinct_atpbs_result,
        $distinct_bcmpx_result,
        $distinct_speed_result
    ) = $distinct_results;
    
    // Initialize sums and counts for totals and averages
    $total_picks = $total_length = $total_percentage = $total_f_stops = 0;
    $total_fsph = $total_atpfs = $total_fcmpx = 0;
    $total_w_stops = $total_wsph = $total_atpws = $total_wcmpx = 0;
    $total_b_stops = $total_bsph = $total_atpbs = $total_bcmpx = 0;
    $total_speed = 0;
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
        <title>Production Report Management</title>
        <link rel="stylesheet" href="css/view_production_report.css">
    </head>
    <body>
    <main>
        <section class="production-report">
            <h2>Production Report</h2>
            
            <form method="GET" action="" class="search-form">
                <!-- From Date Input -->
                <label for="from_date">From Date:</label>
                <input type="date" id="from_date" name="from_date" value="<?php echo htmlspecialchars($from_date); ?>">
                
                <!-- To Date Input -->
                <label for="to_date">To Date:</label>
                <input type="date" id="to_date" name="to_date" value="<?php echo htmlspecialchars($to_date); ?>">

                <!-- Sort Code Input -->
                <label for="sort_code">Sort Code:</label>
                <input type="text" id="sort_code" name="sort_code" value="<?php echo htmlspecialchars($sort_code); ?>">

                <!-- Search Button -->
                <button type="submit" class="btn-search">Search</button>
                
               <!-- Download Buttons -->
            <div class="dropdown">
                <button type="button" class="btn-download">Download <span class="arrow">&#x25BC;</span></button>
                <div class="dropdown-content">
                    <?php if ($is_filter_applied): ?>
                        <a href="download_excel.php?<?php echo http_build_query($_GET); ?>">Excel</a>
                        <a href="download_pdf.php?<?php echo http_build_query($_GET); ?>" target="_blank" rel="noopener noreferrer">PDF</a> <!-- Ensure target="_blank" is present -->
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
                                Sort Code
                                <div class="dropdown">
                                    <span class="dropdown-btn"><span class="arrow">&#x25BC;</span></span>
                                    <div class="dropdown-content">
                                        <?php if (!empty($distinct_sort_code_result)): ?>
                                            <?php foreach ($distinct_sort_code_result as $sort_row): // Use $sort_row here ?>
                                                <a href="?sort_code=<?php echo urlencode($sort_row['Sort_code']); ?>">
                                                    <?php echo handle_null($sort_row['Sort_code']); ?>
                                                </a>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <p>No data available</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </th>
                            <th>
                                Construction
                                <div class="dropdown">
                                    <span class="dropdown-btn"><span class="arrow">&#x25BC;</span></span>
                                    <div class="dropdown-content">
                                    <?php if (!empty($distinct_construction_result)): ?>
                                        <?php foreach ($distinct_construction_result as $construction): ?>
                                                <a href="?construction=<?php echo urlencode($construction['Construction']); ?>">
                                                    <?php echo handle_null($construction['Construction']); ?>
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
                                    <?php if (!empty($distinct_machine_result)): ?>
                                        <?php foreach($distinct_machine_result as $machine): ?>
                                                <a href="?machine=<?php echo urlencode($machine['Machine']); ?>">
                                                    <?php echo handle_null($machine['Machine']); ?>
                                                </a>
                                                <?php endforeach; ?>
                                        <?php else: ?>
                                            <p>No data available</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </th>
                            <th>
                                Warp Set No
                                <div class="dropdown">
                                    <span class="dropdown-btn"><span class="arrow">&#x25BC;</span></span>
                                    <div class="dropdown-content">
                                    <?php if (!empty($distinct_warp_set_no_result)): ?>
                                        <?php foreach($distinct_warp_set_no_result as $warp_row): ?>
                                                <a href="?warp_set_no=<?php echo urlencode($warp_row['Warp_Set_No']); ?>">
                                                    <?php echo handle_null($warp_row['Warp_Set_No']); ?>
                                                </a>
                                                <?php endforeach; ?>
                                        <?php else: ?>
                                            <p>No data available</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </th>
                            <th>
                                Weft Lot No
                                <div class="dropdown">
                                    <span class="dropdown-btn"><span class="arrow">&#x25BC;</span></span>
                                    <div class="dropdown-content">
                                    <?php if (!empty($distinct_weft_lot_no_result)): ?>
                                        <?php foreach($distinct_weft_lot_no_result as $weft_row): ?>
                                                <a href="?weft_lot_no=<?php echo urlencode($weft_row['Weft_Lot_No']); ?>">
                                                    <?php echo handle_null($weft_row['Weft_Lot_No']); ?>
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
                                        <?php foreach($distinct_picks_result as $picks_row): ?>
                                                <a href="?picks=<?php echo urlencode($picks_row['Picks']); ?>">
                                                    <?php echo handle_null($picks_row['Picks']); ?>
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
                                        <?php if (!empty($distinct_length_result)): ?>
                                            <?php foreach($distinct_length_result as $length_row): ?>
                                                <a href="?length=<?php echo urlencode($length_row['Length']); ?>">
                                                    <?php echo handle_null($length_row['Length']); ?>
                                                </a>
                                        <?php endforeach; ?>
                                        <?php else: ?>
                                            <p>No data available</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </th>
                            <th>
                                %
                                <div class="dropdown">
                                    <span class="dropdown-btn"><span class="arrow">&#x25BC;</span></span>
                                    <div class="dropdown-content">
                                        <?php if (!empty($distinct_percentage_result)): ?>
                                            <?php foreach($distinct_percentage_result as $percentage_row): ?>
                                                <a href="?percentage=<?php echo urlencode($percentage_row['Percentage']); ?>">
                                                    <?php echo handle_null($percentage_row['Percentage']); ?>
                                                </a>
                                        <?php endforeach; ?>
                                        <?php else: ?>
                                            <p>No data available</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </th>
                            <th>
                                F Stops
                                <div class="dropdown">
                                    <span class="dropdown-btn"><span class="arrow">&#x25BC;</span></span>
                                    <div class="dropdown-content">
                                        <?php if (!empty($distinct_f_stops_result)): ?>
                                            <?php foreach($distinct_f_stops_result as $f_stops_row): ?>
                                                <a href="?f_stops=<?php echo urlencode($f_stops_row['F_Stops']); ?>">
                                                    <?php echo handle_null($f_stops_row['F_Stops']); ?>
                                                </a>
                                        <?php endforeach; ?>
                                        <?php else: ?>
                                            <p>No data available</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </th>
                            <th>
                                FSPH
                                <div class="dropdown">
                                    <span class="dropdown-btn"><span class="arrow">&#x25BC;</span></span>
                                    <div class="dropdown-content">
                                        <?php if (!empty($distinct_fsph_result)): ?>
                                            <?php foreach($distinct_fsph_result as $fsph_row): ?>
                                                <a href="?fsph=<?php echo urlencode($fsph_row['Fsph']); ?>">
                                                    <?php echo handle_null($fsph_row['Fsph']); ?>
                                                </a>
                                        <?php endforeach; ?>
                                        <?php else: ?>
                                            <p>No data available</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </th>
                            <th>
                                ATPFS(see)
                                <div class="dropdown">
                                    <span class="dropdown-btn"><span class="arrow">&#x25BC;</span></span>
                                    <div class="dropdown-content">
                                        <?php if (!empty($distinct_atpfs_result)): ?>
                                            <?php foreach($distinct_atpfs_result as $atpfs_row): ?>
                                                <a href="?atpfs=<?php echo urlencode($atpfs_row['Atpfs']); ?>">
                                                    <?php echo handle_null($atpfs_row['Atpfs']); ?>
                                                </a>
                                        <?php endforeach; ?>
                                        <?php else: ?>
                                            <p>No data available</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </th>
                            <th>
                                FCMPX
                                <div class="dropdown">
                                    <span class="dropdown-btn"><span class="arrow">&#x25BC;</span></span>
                                    <div class="dropdown-content">
                                        <?php if (!empty($distinct_fcmpx_result)): ?>
                                            <?php foreach($distinct_fcmpx_result as $fcmpx_row): ?>
                                                <a href="?fcmpx=<?php echo urlencode($fcmpx_row['Fcmpx']); ?>">
                                                    <?php echo handle_null($fcmpx_row['Fcmpx']); ?>
                                                </a>
                                        <?php endforeach; ?>
                                        <?php else: ?>
                                            <p>No data available</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </th>
                            <th>
                                W Stops
                                <div class="dropdown">
                                    <span class="dropdown-btn"><span class="arrow">&#x25BC;</span></span>
                                    <div class="dropdown-content">
                                        <?php if (!empty($distinct_w_stops_result)): ?>
                                            <?php foreach($distinct_w_stops_result as $w_stops_row): ?>
                                                <a href="?w_stops=<?php echo urlencode($w_stops_row['W_Stops']); ?>">
                                                    <?php echo handle_null($w_stops_row['W_Stops']); ?>
                                                </a>
                                        <?php endforeach; ?>
                                        <?php else: ?>
                                            <p>No data available</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </th>
                            <th>
                                WSPH
                                <div class="dropdown">
                                    <span class="dropdown-btn"><span class="arrow">&#x25BC;</span></span>
                                    <div class="dropdown-content">
                                        <?php if (!empty($distinct_wsph_result)): ?>
                                            <?php foreach( $distinct_wsph_result as $wsph_row): ?>
                                                <a href="?wsph=<?php echo urlencode($wsph_row['Wsph']); ?>">
                                                    <?php echo handle_null($wsph_row['Wsph']); ?>
                                                </a>
                                        <?php endforeach; ?>
                                        <?php else: ?>
                                            <p>No data available</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </th>
                            <th>
                                ATPWS
                                <div class="dropdown">
                                    <span class="dropdown-btn"><span class="arrow">&#x25BC;</span></span>
                                    <div class="dropdown-content">
                                        <?php if (!empty($distinct_atpws_result)): ?>
                                            <?php foreach( $distinct_atpws_result as $atpws_row): ?>
                                                <a href="?atpws=<?php echo urlencode($atpws_row['Atpws']); ?>">
                                                    <?php echo handle_null($atpws_row['Atpws']); ?>
                                                </a>
                                        <?php endforeach; ?>
                                        <?php else: ?>
                                            <p>No data available</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </th>
                            <th>
                                WCMPX
                                <div class="dropdown">
                                    <span class="dropdown-btn"><span class="arrow">&#x25BC;</span></span>
                                    <div class="dropdown-content">
                                        <?php if (!empty($distinct_wcmpx_result)): ?>
                                            <?php foreach($distinct_wcmpx_result as $wcmpx_row ): ?>
                                                <a href="?Wcmpx=<?php echo urlencode($wcmpx_row['Wcmpx']); ?>">
                                                    <?php echo handle_null($wcmpx_row['Wcmpx']); ?>
                                                </a>
                                        <?php endforeach; ?>
                                        <?php else: ?>
                                            <p>No data available</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </th>
                            <th>
                                B Stops
                                <div class="dropdown">
                                    <span class="dropdown-btn"><span class="arrow">&#x25BC;</span></span>
                                    <div class="dropdown-content">
                                        <?php if (!empty($distinct_b_stops_result)): ?>
                                            <?php foreach($distinct_b_stops_result as $b_stops_row): ?>
                                                <a href="?b_stops=<?php echo urlencode($b_stops_row['B_Stops']); ?>">
                                                    <?php echo handle_null($b_stops_row['B_Stops']); ?>
                                                </a>
                                        <?php endforeach; ?>
                                        <?php else: ?>
                                            <p>No data available</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </th>
                            <th>
                                BSPH
                                <div class="dropdown">
                                    <span class="dropdown-btn"><span class="arrow">&#x25BC;</span></span>
                                    <div class="dropdown-content">
                                        <?php if (!empty($distinct_bsph_result)): ?>
                                            <?php foreach($distinct_bsph_result as $bsph_row): ?>
                                                <a href="?bsph=<?php echo urlencode($bsph_row['Bsph']); ?>">
                                                    <?php echo handle_null($bsph_row['Bsph']); ?>
                                                </a>
                                        <?php endforeach; ?>
                                        <?php else: ?>
                                            <p>No data available</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </th>
                            <th>
                                ATPBS
                                <div class="dropdown">
                                    <span class="dropdown-btn"><span class="arrow">&#x25BC;</span></span>
                                    <div class="dropdown-content">
                                        <?php if (!empty($distinct_atpbs_result)): ?>
                                            <?php foreach($distinct_atpbs_result as $atpbs_row): ?>
                                                <a href="?atpbs=<?php echo urlencode($atpbs_row['Atpbs']); ?>">
                                                    <?php echo handle_null($atpbs_row['Atpbs']); ?>
                                                </a>
                                        <?php endforeach; ?>
                                        <?php else: ?>
                                            <p>No data available</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </th>
                            <th>
                                BCMPX
                                <div class="dropdown">
                                    <span class="dropdown-btn"><span class="arrow">&#x25BC;</span></span>
                                    <div class="dropdown-content">
                                        <?php if (!empty($distinct_bcmpx_result)): ?>
                                            <?php foreach($distinct_bcmpx_result as $bcmpx_row): ?>
                                                <a href="?bcmpx=<?php echo urlencode($bcmpx_row['Bcmpx']); ?>">
                                                    <?php echo handle_null($bcmpx_row['Bcmpx']); ?>
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
                                            <?php foreach($distinct_speed_result as $speed_row): ?>
                                                <a href="?speed=<?php echo urlencode($speed_row['Speed']); ?>">
                                                    <?php echo handle_null($speed_row['Speed']); ?>
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
                                $total_picks = $total_length = $total_percentage = $total_f_stops = 0;
                                $total_fsph = $total_atpfs = $total_fcmpx = 0;
                                $total_w_stops = $total_wsph = $total_atpws = $total_wcmpx = 0;
                                $total_b_stops = $total_bsph = $total_atpbs = $total_bcmpx = 0;
                                $total_speed = 0;
                                $total_rows = 0;
                        ?>
                        <?php if (is_array($result) && count($result) > 0): ?>
                            <?php foreach ($result as $row): ?>
                                <tr>
                                    <td><?php echo format_date($row['Date']); ?></td>
                                    <td><?php echo handle_null($row['Sort_code']); ?></td>
                                    <td><?php echo handle_null($row['Construction']); ?></td>
                                    <td><?php echo handle_null($row['Machine']); ?></td>
                                    <td><?php echo handle_null($row['Warp_Set_No']); ?></td>
                                    <td><?php echo handle_null($row['Weft_Lot_No']); ?></td>
                                    <td><?php echo handle_null($row['Picks']); $total_picks += $row['Picks']; ?></td>
                                    <td><?php echo handle_null($row['Length']); $total_length += $row['Length']; ?></td>
                                    <td><?php echo handle_null($row['Percentage']); $total_percentage += $row['Percentage']; ?></td>
                                    <td><?php echo handle_null($row['F_Stops']); $total_f_stops += $row['F_Stops']; ?></td>
                                    <td><?php echo handle_null($row['Fsph']); $total_fsph += $row['Fsph']; ?></td>
                                    <td><?php echo handle_null($row['Atpfs']); $total_atpfs += $row['Atpfs']; ?></td>
                                    <td><?php echo handle_null($row['Fcmpx']); $total_fcmpx += $row['Fcmpx']; ?></td>
                                    <td><?php echo handle_null($row['W_Stops']); $total_w_stops += $row['W_Stops']; ?></td>
                                    <td><?php echo handle_null($row['Wsph']); $total_wsph += $row['Wsph']; ?></td>
                                    <td><?php echo handle_null($row['Atpws']); $total_atpws += $row['Atpws']; ?></td>
                                    <td><?php echo handle_null($row['Wcmpx']); $total_wcmpx += $row['Wcmpx']; ?></td>
                                    <td><?php echo handle_null($row['B_Stops']); $total_b_stops += $row['B_Stops']; ?></td>
                                    <td><?php echo handle_null($row['Bsph']); $total_bsph += $row['Bsph']; ?></td>
                                    <td><?php echo handle_null($row['Atpbs']); $total_atpbs += $row['Atpbs']; ?></td>
                                    <td><?php echo handle_null($row['Bcmpx']); $total_bcmpx += $row['Bcmpx']; ?></td>
                                    <td><?php echo handle_null($row['Speed']); $total_speed += $row['Speed']; ?></td>
                                </tr>
                                <?php $total_rows++; ?>
                            <?php endforeach; ?>
                            
                            <!-- Totals row -->
                            <tr>
                                <td colspan="5"></td> <!-- Empty cells before "Total" -->
                                <td>Total</td> <!-- Total text at the end -->
                                <td><?php echo $total_picks; ?></td> <!-- Sum of Picks -->
                                <td><?php echo $total_length; ?></td> <!-- Sum of Length -->

                                <!-- Check if $total_rows is greater than 0 before dividing -->
                                <td><?php echo $total_rows > 0 ? number_format($total_percentage / $total_rows, 2) : 0; ?></td> <!-- Average of Percentage -->
                                <td><?php echo $total_rows > 0 ? number_format($total_f_stops / $total_rows, 2) : 0; ?></td> <!-- Average of F_Stops -->
                                <td><?php echo $total_rows > 0 ? number_format($total_fsph / $total_rows, 2) : 0; ?></td> <!-- Average of Fsph -->
                                <td><?php echo $total_rows > 0 ? number_format($total_atpfs / $total_rows, 2) : 0; ?></td> <!-- Average of Atpfs -->
                                <td><?php echo $total_rows > 0 ? number_format($total_fcmpx / $total_rows, 2) : 0; ?></td> <!-- Average of Fcmpx -->
                                <td><?php echo $total_rows > 0 ? number_format($total_w_stops / $total_rows, 2) : 0; ?></td> <!-- Average of W_Stops -->
                                <td><?php echo $total_rows > 0 ? number_format($total_wsph / $total_rows, 2) : 0; ?></td> <!-- Average of Wsph -->
                                <td><?php echo $total_rows > 0 ? number_format($total_atpws / $total_rows, 2) : 0; ?></td> <!-- Average of Atpws -->
                                <td><?php echo $total_rows > 0 ? number_format($total_wcmpx / $total_rows, 2) : 0; ?></td> <!-- Average of Wcmpx -->
                                <td><?php echo $total_rows > 0 ? number_format($total_b_stops / $total_rows, 2) : 0; ?></td> <!-- Average of B_Stops -->
                                <td><?php echo $total_rows > 0 ? number_format($total_bsph / $total_rows, 2) : 0; ?></td> <!-- Average of Bsph -->
                                <td><?php echo $total_rows > 0 ? number_format($total_atpbs / $total_rows, 2) : 0; ?></td> <!-- Average of Atpbs -->
                                <td><?php echo $total_rows > 0 ? number_format($total_bcmpx / $total_rows, 2) : 0; ?></td> <!-- Average of Bcmpx -->
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
