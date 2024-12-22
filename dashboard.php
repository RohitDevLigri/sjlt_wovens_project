<?php  
// Enable error reporting for debugging purposes
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include necessary files
include('auth_check.php');  // Authentication check
include('navbar.php');      // Navigation bar
include('dbconnect.php');   // Database connection

try {
    // Instantiate the Connection class
    $db = new Connection();
    $dbo = $db->getConnection(); // Retrieve the PDO connection

    // Check if the connection was successful
    if (!$dbo) {
        throw new Exception("Database connection failed.");
    }

    // Set timezone (adjust as necessary)
    date_default_timezone_set('Asia/Kolkata'); // Example timezone

    // Get the current month and year
    $currentMonth = date('m');
    $currentYear = date('Y');

    // Calculate the first and last day of the current month
    $firstDayCurrentMonth = date('Y-m-01');
    $lastDayCurrentMonth = date('Y-m-t');

    // Calculate the first and last day for the last 6 months
    $firstDaySixMonthsAgo = date('Y-m-01', strtotime('-5 months'));
    $lastDaySixMonthsAgo = date('Y-m-t', strtotime('-5 months'));
    $lastDayCurrentMonth = date('Y-m-t'); // Reaffirm last day of current month

    // Fetch total production length and average efficiency for the current month
    $queryTotal = "SELECT SUM(`Length`) AS TotalLength, AVG(`Percentage`) AS AvgEfficiency 
                   FROM `Production_report` 
                   WHERE `Date` BETWEEN :firstDayCurrentMonth AND :lastDayCurrentMonth";
    $stmtTotal = $dbo->prepare($queryTotal);
    $stmtTotal->bindParam(':firstDayCurrentMonth', $firstDayCurrentMonth);
    $stmtTotal->bindParam(':lastDayCurrentMonth', $lastDayCurrentMonth);
    $stmtTotal->execute();
    $stmtTotal->setFetchMode(PDO::FETCH_ASSOC);
    $resultTotal = $stmtTotal->fetch();

    // Define $totalLength and $totalEfficiency
    $totalLength = isset($resultTotal['TotalLength']) && !is_null($resultTotal['TotalLength']) ? round($resultTotal['TotalLength'], 2) : 0;
    $totalEfficiency = isset($resultTotal['AvgEfficiency']) && !is_null($resultTotal['AvgEfficiency']) ? round($resultTotal['AvgEfficiency'], 2) : 0;

    // Fetch production details for all looms for the current month
    $queryLooms = "SELECT `Machine`, SUM(`Length`) AS TotalLength, AVG(`Percentage`) AS AvgEfficiency 
                   FROM `Production_report` 
                   WHERE `Date` BETWEEN :firstDayCurrentMonth AND :lastDayCurrentMonth 
                   GROUP BY `Machine`";
    $stmtLooms = $dbo->prepare($queryLooms);
    $stmtLooms->bindParam(':firstDayCurrentMonth', $firstDayCurrentMonth);
    $stmtLooms->bindParam(':lastDayCurrentMonth', $lastDayCurrentMonth);
    $stmtLooms->execute();
    $stmtLooms->setFetchMode(PDO::FETCH_ASSOC);
    $resultLooms = $stmtLooms->fetchAll();

    // Create an associative array for easy lookup, ensuring consistent casing
    $productionMap = [];
    foreach ($resultLooms as $row) {
        $machine = strtoupper($row['Machine']); // Ensure uppercase
        $productionMap[$machine] = [
            'TotalLength' => round($row['TotalLength'], 2),
            'AvgEfficiency' => round($row['AvgEfficiency'], 2)
        ];
    }

    // Prepare data for the loom chart
    $loomsData = [];
    $lengthsData = [];
    foreach ($productionMap as $loom => $data) {
        $loomsData[] = $loom;
        $lengthsData[] = $data['TotalLength'];
    }

    // Fetch daily production data for the current month
    $queryDailyTotal = "SELECT DATE(`Date`) AS Day, SUM(`Length`) AS TotalLength 
                        FROM `Production_report` 
                        WHERE `Date` BETWEEN :firstDayCurrentMonth AND :lastDayCurrentMonth
                        GROUP BY Day
                        ORDER BY Day ASC";
    $stmtDailyTotal = $dbo->prepare($queryDailyTotal);
    $stmtDailyTotal->bindParam(':firstDayCurrentMonth', $firstDayCurrentMonth);
    $stmtDailyTotal->bindParam(':lastDayCurrentMonth', $lastDayCurrentMonth);
    $stmtDailyTotal->execute();
    $stmtDailyTotal->setFetchMode(PDO::FETCH_ASSOC);
    $dailyData = $stmtDailyTotal->fetchAll();

    // Prepare arrays for date-wise chart data
    $dates = [];
    $dailyLengths = [];
    foreach ($dailyData as $data) {
        // Convert 'YYYY-MM-DD' to 'DD' format for x-axis labels
        $dateLabel = date('d', strtotime($data['Day']));
        $dates[] = $dateLabel;
        $dailyLengths[] = round($data['TotalLength'], 2);
    }

    // Fetch monthly production data for the last 6 months
    $queryMonthlyTotal = "SELECT DATE_FORMAT(`Date`, '%M %Y') AS MonthYear, SUM(`Length`) AS TotalLength 
                          FROM `Production_report` 
                          WHERE `Date` BETWEEN :firstDaySixMonthsAgo AND :lastDayCurrentMonth
                          GROUP BY MonthYear
                          ORDER BY `Date` ASC";
    $stmtMonthlyTotal = $dbo->prepare($queryMonthlyTotal);
    $stmtMonthlyTotal->bindParam(':firstDaySixMonthsAgo', $firstDaySixMonthsAgo);
    $stmtMonthlyTotal->bindParam(':lastDayCurrentMonth', $lastDayCurrentMonth);
    $stmtMonthlyTotal->execute();
    $stmtMonthlyTotal->setFetchMode(PDO::FETCH_ASSOC);
    $monthlyData = $stmtMonthlyTotal->fetchAll();

    // Prepare arrays for month-wise chart data
    $months = [];
    $monthlyLengths = [];
    foreach ($monthlyData as $data) {
        $months[] = $data['MonthYear']; // e.g., "May 2024"
        $monthlyLengths[] = round($data['TotalLength'], 2);
    }

} catch (PDOException $e) {
    die("Database error: " . htmlspecialchars($e->getMessage()));
} catch (Exception $e) {
    die("Error: " . htmlspecialchars($e->getMessage()));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Loom Production Dashboard</title>
    <!-- Link to the external dashboard.css file -->
    <link rel="stylesheet" href="css/dashboard.css">
    <!-- Include necessary scripts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <!-- Chart.js Library -->
    <script src="jquery.js"></script> <!-- jQuery Library -->
    <script src="font.js"></script> <!-- Any additional scripts -->
</head>
<body>
    <!-- Main Content -->
    <div class="main-content">
        <h2>Loom Production Details for <?php echo date('F Y', strtotime($firstDayCurrentMonth)); ?></h2>
        <p>Total production length: <?php echo htmlspecialchars($totalLength); ?> m</p>
        <p>Total production efficiency: <?php echo htmlspecialchars($totalEfficiency); ?>%</p>
    </div>

    <!-- Dashboard Looms -->
    <div class="dashboard">
        <?php
            // Define the list of looms you want to display
            $looms = [
                'LOOM01', 'LOOM02', 'LOOM03', 'LOOM04', 'LOOM05', 'LOOM06',
                'LOOM07', 'LOOM08', 'LOOM09', 'LOOM10', 'LOOM11', 'LOOM12',
                'LOOM13', 'LOOM14', 'LOOM15', 'LOOM16', 'LOOM17', 'LOOM18',
                'LOOM19', 'LOOM20', 'LOOM21', 'LOOM22', 'LOOM23', 'LOOM24',
                'LOOM25', 'LOOM26', 'LOOM27', 'LOOM28', 'LOOM29', 'LOOM30',
                'LOOM31', 'LOOM32', 'LOOM33', 'LOOM34', 'LOOM35', 'LOOM36',
                'LOOM37', 'LOOM38', 'LOOM39', 'LOOM40', 'LOOM41', 'LOOM42',
                'LOOM43', 'LOOM44', 'LOOM45', 'LOOM46', 'LOOM47', 'LOOM48'
            ];

            // Iterate through each loom to create form and display production details
            foreach ($looms as $loom) {
                echo '<div class="c">';
                echo '<form action="loomchart.php" method="post">';
                echo '<input type="hidden" name="LOOM" value="' . htmlspecialchars($loom) . '">';
                echo '<input type="submit" value="' . htmlspecialchars($loom) . '" name="LOOM">';
                echo '</form>';

                if (isset($productionMap[$loom])) {
                    $length = htmlspecialchars($productionMap[$loom]['TotalLength']);
                    $percentage = htmlspecialchars($productionMap[$loom]['AvgEfficiency']);
                    echo "<p>{$length} m<br>{$percentage}%</p>";
                } else {
                    // Display 0 for looms without data
                    echo "<p>0 m<br>0%</p>";
                }

                echo '</div>';
            }
        ?>
    </div>

    <!-- Sort Details Section (Optional) -->
    <div class="sortdetails">
        <!-- Additional content can be added here if needed -->
    </div>

    <!-- Canvas for the Monthly Production Chart -->
    <div class="chart-container">
        <canvas id="monthlyChart"></canvas>
    </div>

    <!-- Canvas for the Daily Production Chart -->
    <div class="chart-container">
        <canvas id="dailyChart"></canvas>
    </div>

    <!-- JavaScript for Charts -->
    <script>
        // Prepare data for month-wise Chart.js
        const monthLabels = <?php echo json_encode($months); ?>; // Month labels (e.g., "May 2024")
        const monthLengths = <?php echo json_encode($monthlyLengths); ?>; // Total lengths per month

        // Create the Monthly Production Chart
        new Chart("monthlyChart", {
            type: "bar", // Bar chart
            data: {
                labels: monthLabels,
                datasets: [{
                    label: "Production Length (m)",
                    backgroundColor: "rgba(54, 162, 235, 0.5)",
                    borderColor: "rgba(54, 162, 235, 1)",
                    borderWidth: 1,
                    data: monthLengths
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Monthly Production Length'
                    },
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.parsed.y + ' m';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Production Length (m)'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Months'
                        }
                    }
                }
            }
        });

        // Prepare data for date-wise Chart.js
        const dateLabels = <?php echo json_encode($dates); ?>; // Date labels (e.g., "01", "02", ..., "31")
        const dateLengths = <?php echo json_encode($dailyLengths); ?>; // Total lengths per date

        // Create the Daily Production Chart
        new Chart("dailyChart", {
            type: "line", // Line chart
            data: {
                labels: dateLabels,
                datasets: [{
                    label: "Production Length (m)",
                    backgroundColor: "rgba(255, 99, 132, 0.2)",
                    borderColor: "rgba(255, 99, 132, 1)",
                    borderWidth: 2,
                    fill: true,
                    data: dateLengths
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Daily Production Length for <?php echo date('F Y', strtotime($firstDayCurrentMonth)); ?>'
                    },
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.parsed.y + ' m';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Production Length (m)'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Dates'
                        }
                    }
                }
            }
        });
    </script>

    <!-- JavaScript for Navigation Effects -->
    <script>
        // jQuery for navigation effects (if applicable)
        $('.feat-btn').click(function(){
            $('nav ul .feat-show').toggleClass("show");
            $('nav ul .first').toggleClass("rotate");
        });
        $('.serv-btn').click(function(){
            $('nav ul .serv-show').toggleClass("show1");
            $('nav ul .second').toggleClass("rotate");
        });
        $('nav ul li').click(function(){
            $(this).addClass("active").siblings().removeClass("active");
        });
    </script>
</body>
</html>
