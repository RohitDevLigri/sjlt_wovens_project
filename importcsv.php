<?php
// session_start();

// Enable error reporting for debugging (disable in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once 'auth_check.php';
include_once 'dbconnect.php';
include_once 'navbar.php'; // Ensure this file exists and is correctly included

$successMessage = ''; // Variable to hold success message
$errorMessage = '';   // Variable to hold error message
$successfulImport = false; // Initialize the variable to false

// Define expected column names
$expectedHeaders = [
    'Date', 
    'Sort_code', 
    'Construction', 
    'Machine', 
    'Warp_Set_No', 
    'Weft_Lot_No', 
    'Picks', 
    'Length', 
    'Percentage', 
    'F_Stops', 
    'Fsph', 
    'Atpfs', 
    'Fcmpx', 
    'W_Stops', 
    'Wsph', 
    'Atpws', 
    'Wcmpx', 
    'B_Stops', 
    'Bsph', 
    'Atpbs', 
    'Bcmpx', 
    'Speed'
];

// Function to get a user-friendly error message based on upload error code
function uploadErrorMessage($error_code) {
    switch ($error_code) {
        case UPLOAD_ERR_OK:
            return "No error, the file uploaded successfully.";
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return "The uploaded file exceeds the maximum file size.";
        case UPLOAD_ERR_PARTIAL:
            return "The uploaded file was only partially uploaded.";
        case UPLOAD_ERR_NO_FILE:
            return "No file was uploaded.";
        case UPLOAD_ERR_NO_TMP_DIR:
            return "Missing a temporary folder.";
        case UPLOAD_ERR_CANT_WRITE:
            return "Failed to write file to disk.";
        case UPLOAD_ERR_EXTENSION:
            return "File upload stopped by extension.";
        default:
            return "Unknown upload error.";
    }
}

// Validate and format the date to 'Y-m-d' format
function validateAndFormatDate($date) {
    // Define an array of acceptable date formats, including non-padded formats
    $dateFormats = [
        'Y-m-d', // 2024-09-01
        'd/m/Y', // 01/09/2024
        'm/d/Y', // 09/01/2024
        'n/j/Y', // 9/1/2024 (single-digit month/day)
        'd-m-Y', // 01-09-2024
        'm-d-Y', // 09-01-2024
        'n-j-Y', // 9-1-2024
        'd.m.Y', // 01.09.2024
        'm.d.Y', // 09.01.2024
        'n.j.Y', // 9.1.2024
        'Y/m/d', // 2024/09/01
        'Y.m.d'  // 2024.09.01
    ];

    foreach ($dateFormats as $format) {
        $d = DateTime::createFromFormat($format, $date);
        if ($d && $d->format($format) === $date) {
            return $d->format('Y-m-d'); // Return in 'Y-m-d' format
        }
    }
    return false; // Invalid date
}

if (isset($_POST['impsubmit'])) {
    // Check if a file was uploaded without errors
    if (isset($_FILES['file']) && $_FILES['file']['error'] == UPLOAD_ERR_OK) {
        $allowedExtensions = ["csv"];
        $allowedMimeTypes = ["text/csv", "application/csv", "application/vnd.ms-excel", "text/plain"];

        $filename = $_FILES['file']['name'];
        $filetype = $_FILES['file']['type'];
        $filesize = $_FILES['file']['size'];

        // Verify file extension
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExtensions)) {
            $errorMessage = "Error: Please select a valid CSV file.";
        }
        // Verify MIME type
        elseif (!in_array($filetype, $allowedMimeTypes)) {
            $errorMessage = "Error: Please upload a valid CSV file. Accepted types are: text/csv, application/csv, application/vnd.ms-excel, text/plain.";
        }
        // Check file size (e.g., max 5MB)
        elseif ($filesize > 5 * 1024 * 1024) {
            $errorMessage = "Error: File size is larger than the allowed limit of 5MB.";
        }
        else {
            // Create uploads directory if it doesn't exist
            if (!is_dir('uploads')) {
                if (!mkdir('uploads', 0755, true)) {
                    $errorMessage = "Error: Failed to create uploads directory.";
                }
            }

            // Proceed only if uploads directory exists or was successfully created
            if (empty($errorMessage)) {
                // Generate a unique file name to prevent overwriting
                $new_filename = 'uploads/' . uniqid() . '-' . basename($filename);

                // Move the uploaded file to the uploads directory
                if (!move_uploaded_file($_FILES['file']['tmp_name'], $new_filename)) {
                    $errorMessage = "Error: Failed to move uploaded file. Please check folder permissions.";
                }
                else {
                    // Parse the CSV file and insert data into the database
                    if (($handle = fopen($new_filename, "r")) !== FALSE) {
                        $db = new Connection();
                        $dbo = $db->getConnection();

                        // Assuming the first row contains headers
                        $headers = fgetcsv($handle, 1000, ",");

                        if ($headers === FALSE) {
                            $errorMessage = "Error: CSV file is empty.";
                            fclose($handle);
                        }
                        else {
                            // Trim headers to remove any leading/trailing whitespace
                            $headers = array_map('trim', $headers);

                            // Normalize headers to check against expected values (case-insensitive)
                            $normalizedHeaders = array_map('strtolower', $headers);
                            $expectedHeadersLower = array_map('strtolower', $expectedHeaders);

                            // Check if headers match the expected names (case insensitive)
                            $missingHeaders = array_diff($expectedHeadersLower, $normalizedHeaders);
                            if (!empty($missingHeaders)) {
                                // Convert back to original case for error message
                                $missingHeadersOriginalCase = [];
                                foreach ($missingHeaders as $missing) {
                                    $key = array_search($missing, $expectedHeadersLower);
                                    if ($key !== false) {
                                        $missingHeadersOriginalCase[] = $expectedHeaders[$key];
                                    }
                                }
                                $errorMessage = "Error: Missing headers in CSV file: " . implode(", ", $missingHeadersOriginalCase);
                                fclose($handle);
                            } else {
                                // Map CSV headers to expected headers
                                $headerMap = [];
                                foreach ($expectedHeaders as $expectedHeader) {
                                    $key = array_search(strtolower($expectedHeader), $normalizedHeaders);
                                    if ($key !== false) {
                                        $headerMap[$expectedHeader] = $key;
                                    }
                                }

                                // Prepare the SQL statement with named placeholders
                                $sql = "INSERT INTO Production_report 
                                        (`Date`, `Sort_code`, `Construction`, `Machine`, `Warp_Set_No`, `Weft_Lot_No`, `Picks`, `Length`, `Percentage`, 
                                         `F_Stops`, `Fsph`, `Atpfs`, `Fcmpx`, `W_Stops`, `Wsph`, `Atpws`, `Wcmpx`, `B_Stops`, `Bsph`, 
                                         `Atpbs`, `Bcmpx`, `Speed`) 
                                        VALUES 
                                        (:date, :sort_code, :construction, :machine, :warp_set_no, :weft_lot_no, :picks, :length, :percentage, 
                                         :f_stops, :fsph, :atpfs, :fcmpx, :w_stops, :wsph, :atpws, :wcmpx, :b_stops, :bsph, 
                                         :atpbs, :bcmpx, :speed)";
                                $stmt = $dbo->prepare($sql);

                                try {
                                    $dbo->beginTransaction(); // Start transaction
                                    $rowNumber = 1; // To track row numbers for error reporting

                                    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                                        $rowNumber++;

                                        // Check if the row has enough columns
                                        if (count($data) < count($expectedHeaders)) {
                                            $errorMessage .= "Error: Row {$rowNumber} does not have enough columns.<br>";
                                            continue; // Skip invalid rows
                                        }

                                        // Trim each value and set to null if empty
                                        $rowData = [];
                                        foreach ($expectedHeaders as $header) {
                                            $index = $headerMap[$header];
                                            $value = isset($data[$index]) ? trim($data[$index]) : '';
                                            $rowData[$header] = $value === '' ? null : $value;

                                            // Validate and format the Date
                                            if ($rowData['Date']) {
                                                $formattedDate = validateAndFormatDate($rowData['Date']);
                                                if (!$formattedDate) {
                                                    $errorMessage .= "Error: Invalid Date format in row {$rowNumber}: " . htmlspecialchars($rowData['Date']) . "<br>";
                                                    continue; // Skip this record if the date is invalid
                                                }
                                                $rowData['Date'] = $formattedDate;
                                            } else {
                                                $errorMessage .= "Error: Date is missing in row {$rowNumber}.<br>";
                                                continue; // Skip records without a date
                                            }

                                            // Here, we set specific string values to NULL for database storage
                                                if (strcasecmp($value, 'N/A') === 0 || strcasecmp($value, 'null') === 0 || 
                                                strcasecmp($value, 'NULL') === 0 || strcasecmp($value, 'Null') === 0 || 
                                                $value === '') {
                                                $rowData[$header] = null; // Store as NULL in database
                                            } else {
                                                $rowData[$header] = $value; // Otherwise, store the actual value
                                            }
                                        }

                                        // Prepare associative array for execute
                                        $executeData = [
                                            ':date' => $rowData['Date'], // Already formatted to 'Y-m-d'
                                            ':sort_code' => $rowData['Sort_code'],
                                            ':construction' => $rowData['Construction'],
                                            ':machine' => $rowData['Machine'],
                                            ':warp_set_no' => $rowData['Warp_Set_No'],
                                            ':weft_lot_no' => $rowData['Weft_Lot_No'],
                                            ':picks' => $rowData['Picks'],
                                            ':length' => $rowData['Length'],
                                            ':percentage' => $rowData['Percentage'],
                                            ':f_stops' => $rowData['F_Stops'],
                                            ':fsph' => $rowData['Fsph'],
                                            ':atpfs' => $rowData['Atpfs'],
                                            ':fcmpx' => $rowData['Fcmpx'],
                                            ':w_stops' => $rowData['W_Stops'],
                                            ':wsph' => $rowData['Wsph'],
                                            ':atpws' => $rowData['Atpws'],
                                            ':wcmpx' => $rowData['Wcmpx'],
                                            ':b_stops' => $rowData['B_Stops'],
                                            ':bsph' => $rowData['Bsph'],
                                            ':atpbs' => $rowData['Atpbs'],
                                            ':bcmpx' => $rowData['Bcmpx'],
                                            ':speed' => $rowData['Speed']
                                        ];

                                        // Execute the statement
                                        try {
                                            $stmt->execute($executeData);
                                        }
                                        catch (PDOException $e) {
                                            $errorMessage .= "Database Error in row {$rowNumber}: " . $e->getMessage() . "<br>";
                                            // Optionally, you can decide to rollback and stop or continue
                                            // Here, we'll rollback and stop
                                            $dbo->rollBack(); // Rollback on error
                                            fclose($handle);
                                            break; // Exit the loop
                                        }
                                    }

                                    // Commit the transaction if no errors occurred
                                    if (empty($errorMessage)) {
                                        $dbo->commit();
                                        $successMessage = "CSV file imported successfully!";
                                        $successfulImport = true;
                                    }
                                    else {
                                        // If there were errors, rollback the transaction
                                        $dbo->rollBack();
                                    }
                                }
                                catch (PDOException $e) {
                                    $errorMessage = "Database Error: " . $e->getMessage();
                                    $dbo->rollBack(); // Rollback on error
                                }
                                finally {
                                    fclose($handle);
                                    // Optionally, you can delete the uploaded CSV file after processing
                                    // unlink($new_filename);
                                }
                            }
                        }
                    }
                    else {
                        $errorMessage = "Error: Could not open the CSV file.";
                    }
                }
            }
            else {
                $errorMessage = "Error: No file uploaded or there was an upload error. " . uploadErrorMessage($_FILES['file']['error']);
            }
        }
    }
    else {
        $errorMessage = "Error: No file uploaded or there was an upload error.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Import CSV File</title>
    <link rel="stylesheet" href="css/importcsv.css">
    <style>
        /* Basic styling for messages */
        .message {
            margin-top: 20px;
            padding: 10px;
            border-radius: 5px;
        }
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
        }
        .message.success {
            background-color: #d4edda;
            color: #155724;
        }
        /* Additional styling can be added as needed */
    </style>
</head>
<body>
    <div class="main-content">
        <div id="CSVFile" class="mainbartab-content">
            <h3 class="import-heading">Import CSV File</h3>
            <form action="importcsv.php" method="post" enctype="multipart/form-data">
                <input type="file" name="file" accept=".csv" required />
                <input type="submit" class="btn btn-primary" name="impsubmit" value="IMPORT">
                <br>
            </form>

            <?php if (!empty($errorMessage)): ?>
                <div class="message error"><?php echo $errorMessage; ?></div>
            <?php endif; ?>
            
            <?php if ($successfulImport): ?>
                <div class="message success">Import successful! Redirecting...</div>
                <script>
                    setTimeout(function() {
                        window.location.href = 'view_production_report.php'; // Redirect after success
                    }, 2000); // Redirect after 2 seconds
                </script>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
