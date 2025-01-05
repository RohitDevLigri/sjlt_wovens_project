<?php
// navbar.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Production Report Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="css/navbar.css">
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Get all dropdown buttons
            var dropdowns = document.querySelectorAll('.navbar-dropbtn');

            dropdowns.forEach(function (dropbtn) {
                dropbtn.addEventListener('click', function (event) {
                    // Prevent click from propagating to parent elements
                    event.stopPropagation();

                    // Toggle the active class on the dropdown content
                    var dropdownContent = this.nextElementSibling;
                    dropdownContent.classList.toggle('active');

                    // Close other dropdowns at the same level
                    var siblingDropdowns = this.closest('.navbar-dropdown').parentNode.querySelectorAll('.navbar-dropdown-content');
                    siblingDropdowns.forEach(function (content) {
                        if (content !== dropdownContent) {
                            content.classList.remove('active');
                        }
                    });
                });
            });

            // Click anywhere outside the dropdown to close all open dropdowns
            document.addEventListener('click', function () {
                var allDropdowns = document.querySelectorAll('.navbar-dropdown-content');
                allDropdowns.forEach(function (content) {
                    content.classList.remove('active');
                });
            });
        });
    </script>
</head>
<body>
    <!-- Top Navbar with Company Name and Logo -->
    <div class="top-navbar">
        <div class="company-header">
            <div class="company-name">SJLT WOVENS MILLS (P) LTD </div>
            <div class="company-logo">
                <img src="../images/SJLT-logo.png" alt="Company Logo" />
            </div>
        </div>
        <!-- First Line of Navbar -->
        <div class="first-line">
            <a href="dashboard.php">Dashboard</a>
            <!-- Master Dropdown -->
            <div class="navbar-dropdown">
                <button class="navbar-dropbtn">Master <i class="fa fa-caret-down"></i></button>
                <div class="navbar-dropdown-content">
                    <div class="navbar-dropdown">
                        <button class="navbar-dropbtn">Sort Code Generation <i class="fa fa-caret-right"></i></button>
                        <div class="navbar-dropdown-content">
                            <a href="#">Sort Code Operation</a>
                            <a href="#">Sort & Fabric</a>
                        </div>
                    </div>
                    <a href="agent_details_summary.php">Agent Details</a>
                    <a href="buyer_details_summary.php">Buyer Details</a>
                    <a href="currency_type.php">Currency Type</a>
                    <a href="certification_details.php">Certification Details</a>
                    <a href="fiber_composition.php">Fiber Composition</a>
                    <a href="freight_details.php">Freight Details</a>
                    <a href="order_type.php">Order Type</a>
                    <a href="packing_type.php">Packing Type</a>
                    <a href="sort_code_summary.php">Sort Code</a>
                    <a href="yarn_count_unit.php">Yarn Count Unit</a>
                    <a href="traceability_documents.php">Traceability Documents</a>
                    <a href="yarn_supplier_details.php">Yarn Supplier Details</a>
                    <a href="yarn_quality.php">Yarn Quality</a>
                </div>
            </div>
            <div class="navbar-dropdown">
                <button class="navbar-dropbtn">Sales <i class="fa fa-caret-down"></i></button>
                <div class="navbar-dropdown-content">
                    <a href="sales_order_summary.php">Sales Order Summary</a>
                    <a href="#">Marketing Team</a>
                    <div class="navbar-dropdown">
                        <button class="navbar-dropbtn">Enquiry List <i class="fa fa-caret-right"></i></button>
                        <div class="navbar-dropdown-content">
                            <a href="create_new_sales_order.php">Create New Sales Order</a>
                            <a href="#">Sales Order Pending Approval</a>
                        </div>
                    </div>
                    <div class="navbar-dropdown">
                        <button class="navbar-dropbtn">Yarn PO <i class="fa fa-caret-right"></i></button>
                        <div class="navbar-dropdown-content">
                            <a href="yarn_summary.php">Yarn Summary</a>
                            <a href="create_yarn_purchase_order.php">Yarn Purches Order</a>
                            <a href="Yarn_fresh_stock_entry.php">Yarn Fresh Stock Entry</a>
                            <a href="create_a_yarn_issues.php">Yarn Issues</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="navbar-dropdown">
                <button class="navbar-dropbtn">Production <i class="fa fa-caret-down"></i></button>
                <div class="navbar-dropdown-content">
                    <div class="navbar-dropdown">
                        <button class="navbar-dropbtn">Loom Production Data <i class="fa fa-caret-right"></i></button>
                        <div class="navbar-dropdown-content">
                            <a href="importcsv.php">CSV File Import</a>
                            <a href="manualentry.php">Manual Entry</a>
                        </div>
                        <button class="navbar-dropbtn">Sizing Production <i class="fa fa-caret-right"></i></button>
                        <div class="navbar-dropdown-content">
                            <a href="sizing_form.php">Sizing Form</a>
                            <a href="knotting_form.php">Knotting Form</a>
                            <a href="sort_change_form.php">Sort Change Form</a>
                        </div>
                    </div>
                </div>
            </div>

            <a href="#">Inspection</a>
            <a href="#">Despatch</a>
            <div class="navbar-dropdown">
                <button class="navbar-dropbtn">Reports <i class="fa fa-caret-down"></i></button>
                <div class="navbar-dropdown-content">
                    <a href="view_production_report.php">Production Report</a>
                    <a href="user_defined_reports.php">User Defined Reports</a>

                </div>
            </div>

            <!-- Right-Aligned Links -->
            <div class="right-links">
                <?php if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true): ?>
                    <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                        <a href="admin_account.php">Admin Account</a>
                    <?php else: ?>
                        <a href="user_account.php">User Account</a>
                    <?php endif; ?>
                    <a href="home.php" class="right">Home Page</a>
                    <a href="logout.php" class="right">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="right">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
