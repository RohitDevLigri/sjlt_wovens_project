<?php 
// Enable error reporting for debugging purposes
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the database connection
include_once 'dbconnect.php';

// Create a new Connection instance and get the PDO connection
$db = new Connection();
$dbo = $db->getConnection();

if (!$dbo) {
    die("Database connection failed.");
}

// Set timezone (adjust as necessary)
date_default_timezone_set('Asia/Kolkata'); // Example timezone

// Retrieve month and year from GET parameters, default to current month and year
$month = isset($_GET['month']) ? intval($_GET['month']) : intval(date("m"));
$year = isset($_GET['year']) ? intval($_GET['year']) : intval(date("Y"));

// Validate month and year
if ($month < 1 || $month > 12) {
    die("Invalid month specified.");
}
if ($year < 2000 || $year > 2100) { // Arbitrary range for validation
    die("Invalid year specified.");
}

// Format month and year for display
$MONTH = date('F', mktime(0, 0, 0, $month, 10)); // Full month name

// Calculate number of days in the specified month
$d = cal_days_in_month(CAL_GREGORIAN, $month, $year);

// Set first and last dates of the month
$first = date('Y-m-d', mktime(0, 0, 0, $month, 1, $year));
$last = date('Y-m-t', mktime(0, 0, 0, $month, 1, $year));

// Initialize arrays for dates and total production
$thisDate = [];
$total = [];

// Generate array of dates for the month
for ($i = 0; $i < $d; $i++) {
    $thisDate[] = date('Y-m-d', strtotime("+$i days", strtotime($first)));
}

// Retrieve the machine (LOOM) parameter from GET or POST
$machine = isset($_REQUEST['LOOM']) ? trim($_REQUEST['LOOM']) : '';

// Validate machine parameter
if (empty($machine)) {
    die("No machine specified. Please provide the 'LOOM' parameter.");
}

// Prepare the SQL query with named parameters for the whole month
$totalqueryA = "SELECT `Date`, SUM(`Length`) AS total_length 
                 FROM `production_report` 
                 WHERE `Date` BETWEEN :first AND :last AND `Machine` = :machine 
                 GROUP BY `Date`";
$totalstmtA = $dbo->prepare($totalqueryA);

// Bind parameters to prevent SQL injection
$totalstmtA->bindParam(':first', $first);
$totalstmtA->bindParam(':last', $last);
$totalstmtA->bindParam(':machine', $machine);

// Execute the query
$totalstmtA->execute();

// Fetch all results into an associative array
$results = $totalstmtA->fetchAll(PDO::FETCH_ASSOC);

// Initialize total array with zeros for each date
foreach ($thisDate as $date) {
    $total[$date] = 0; // Default to zero
}

// Fill the total array with actual values from results
foreach ($results as $row) {
    $total[$row['Date']] = floatval($row['total_length']);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Production Report Chart</title>
    <!-- Include Chart.js from CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Basic reset for body margin */
        body {
            margin: 0;
            padding: 20px;
            font-family: Arial, sans-serif;
        }

        /* Styling for the main heading */
        h2 {
            margin: 10px 0;
            font-size: 18px; /* Decreased font size */
        }

        /* Ensure the canvas fits well within the page */
        canvas {
            display: block; /* Make the canvas a block element */
            width: 100%; /* Ensure it fits the width of the container */
            max-width: 800px; /* Limit max width for larger screens */
            height: 250px; /* Set a smaller fixed height for compactness */
        }

        /* Center the return link */
        a {
            display: inline-block;
            margin: 15px 0;
            text-decoration: none;
            color: #007bff;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <h2>Production Report for <?php echo htmlspecialchars($MONTH . ' ' . $year); ?> - Machine: <?php echo htmlspecialchars($machine); ?></h2>
    <a href="dashboard.php">Return to Dashboard</a>

    <canvas id="myChart"></canvas>

    <script>
    // Prepare the data for Chart.js
    const xValues = <?php echo json_encode($thisDate); ?>; // Array of dates
    const yValues = <?php echo json_encode(array_values($total)); ?>; // Array of total lengths

    // Configuration for the chart title
    const chartTitle = "<?php echo addslashes($MONTH . ' ' . $year); ?> - Machine: <?php echo addslashes($machine); ?>";

    // Create the chart
    new Chart("myChart", {
        type: "line",
        data: {
            labels: xValues,
            datasets: [{
                label: "Total Production Length",
                fill: false,
                lineTension: 0.1,
                backgroundColor: "rgba(75,192,192,0.4)",
                borderColor: "rgba(75,192,192,1)",
                borderWidth: 1, // Decrease border width for a more compact look
                data: yValues
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: chartTitle,
                    font: {
                        size: 14 // Decrease font size for title
                    }
                },
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        font: {
                            size: 10 // Decrease font size for legend
                        }
                    }
                }
            },
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'Date',
                        font: {
                            size: 10 // Decrease font size for x-axis title
                        }
                    },
                    ticks: {
                        autoSkip: true,
                        maxTicksLimit: 10 // Limit the number of ticks displayed
                    }
                },
                y: {
                    title: {
                        display: true,
                        text: 'Total Length',
                        font: {
                            size: 10 // Decrease font size for y-axis title
                        }
                    },
                    beginAtZero: true,
                    suggestedMax: Math.max(...yValues) || 600 // Ensure a dynamic max value
                }
            }
        }
    });
    </script>

</body>
</html>
