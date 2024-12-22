<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Defined Reports</title>
    <link rel="stylesheet" href="css/user_defined_reports.css">
</head>
<body>

<div class="header">
    <div class="logo"></div>
    <div class="user-profile">
        <!-- <img src="profile_icon.png" alt="User Icon"> -->
    </div>
</div>

<div class="content">
    <h2>User Defined Reports</h2>
    <div class="report-filters">
        <select class="dropdown">
            <option></option>
        </select>

        <div class="filter-group">
    <button class="filter-btn">Shed : All</button>
    <div class="calendar-container">
        <!-- Date Pickers -->
        <input type="date" class="date-picker" id="fromDate" value="2024-01-01" onchange="updateDateShift('fromShift', this)">
        
        <!-- Shift Code Buttons Left -->
        <div id="fromShiftContainer" class="shift-buttons-left">
            <span class="shift-code" onclick="selectShift('S1', 'fromShift')">S1</span>
            <span class="shift-code" onclick="selectShift('S2', 'fromShift')">S2</span>
        </div>
        
        <span>to</span>
        
        <input type="date" class="date-picker" id="toDate" value="2024-01-01" onchange="updateDateShift('toShift', this)">
        
        <!-- Shift Code Buttons Right -->
        <div id="toShiftContainer" class="shift-buttons-right">
            <span class="shift-code" onclick="selectShift('S1', 'toShift')">S1</span>
            <span class="shift-code" onclick="selectShift('S2', 'toShift')">S2</span>
        </div>
    </div>

    <!-- Display Selected Shifts -->
    <div class="selected-shifts">
        <p id="fromShift">Selected Shift: None</p>
        <p id="toShift">Selected Shift: None</p>
    </div>
</div>


        <div class="period-buttons">
            <button class="period-btn active">Continuous Period</button>
            <button class="period-btn">Selective Period</button>
        </div>

        <button class="filter-btn">Material Type : All</button>
        <button class="filter-btn count-btn">Count : All</button>
        <button class="filter-btn">Machine : All</button>
        <button class="filter-btn">Supervisor : All</button>
        <button class="filter-btn">Operators : All</button>

        <select class="dropdown">
            <option></option>
        </select>
        
        <div class="parameters">
            <button class="load-btn">Load</button>
        </div>
    </div>

    <div class="action-buttons">
        <button class="settings-btn" title="Settings"></button>
        <button class="print-btn" title="Print"></button>
        <div class="export-dropdown">
            <button class="export-dropdown-btn">Export</button>
            <div class="export-options">
                <button class="export-btn">Excel</button>
                <button class="export-btn">csv</button>
                <button class="export-btn">pdf</button>
            </div>
        </div>
    </div>
</div>

<script>
    function selectShift(shift) {
        // Remove 'selected' class from all shift codes
        document.querySelectorAll('.shift-code').forEach(element => {
            element.classList.remove('selected');
        });
        // Add 'selected' class to the clicked shift
        document.querySelectorAll('.shift-code').forEach(element => {
            if (element.textContent === shift) {
                element.classList.add('selected');
            }
        });
        // Update the selected shift display
        document.getElementById('selectedShiftDisplay').textContent = shift;
    }

      // Function to select and display the shift code
    function selectShift(shiftCode, shiftDisplayId) {
        const shiftDisplay = document.getElementById(shiftDisplayId);
        shiftDisplay.textContent = `Selected Shift: ${shiftCode}`;
    }

    // Function to update shift code based on date change
    function updateDateShift(shiftDisplayId, datePicker) {
        const selectedDate = datePicker.value;
        const shiftDisplay = document.getElementById(shiftDisplayId);
        if (!shiftDisplay.textContent.includes('S1') && !shiftDisplay.textContent.includes('S2')) {
            shiftDisplay.textContent = `Selected Shift: None`;
        }
        console.log(`Date updated: ${selectedDate}`);
    }
</script>

</body>
</html>