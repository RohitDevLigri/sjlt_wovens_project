<?php
include('dbconnect.php');
include('navbar.php');

// Create connection instance and fetch PDO connection
$db = new Connection();
$conn = $db->getConnection();

// Fetch data and store in arrays
$buyers_stmt = $conn->prepare("SELECT buyer_id, buyer_name FROM buyer");
$buyers_stmt->execute();
$buyers_data = $buyers_stmt->fetchAll(PDO::FETCH_ASSOC);

$agents_stmt = $conn->prepare("SELECT agent_id, agent_name FROM agent");
$agents_stmt->execute();
$agents_data = $agents_stmt->fetchAll(PDO::FETCH_ASSOC);

$fibre_types_stmt = $conn->prepare("SELECT fibre_id, fibre_name FROM fibretype");
$fibre_types_stmt->execute();
$fibre_types_data = $fibre_types_stmt->fetchAll(PDO::FETCH_ASSOC);

$selvedge_types_stmt = $conn->prepare("SELECT selvedge_id, selvedge_name FROM selvedgetype");
$selvedge_types_stmt->execute();
$selvedge_types_data = $selvedge_types_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Sales Order</title>
    <link rel="stylesheet" href="css/create_new_sales_orderOLD.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .hidden {
            display: none;
        }
    </style>
    <script>
        function toggleFormSections() {
            const orderTypeElement = document.querySelector('input[name="order_type"]:checked');
            if (!orderTypeElement) {
                console.error('No order type selected');
                return;
            }

            const orderType = orderTypeElement.value;
            const ownSalesSection = document.getElementById('ownsales-section');
            const jobWorkSection = document.getElementById('jobwork-section');
            
            // Toggle visibility between own sales and job work
            if (orderType === 'Ownsales') {
                ownSalesSection.style.display = 'block';
                jobWorkSection.style.display = 'none';
                toggleRequiredFields(jobWorkSection, false);  // Remove required from job work section
                toggleRequiredFields(ownSalesSection, true);  // Add required to own sales section
            } else {
                ownSalesSection.style.display = 'none';
                jobWorkSection.style.display = 'block';
                toggleRequiredFields(ownSalesSection, false);  // Remove required from own sales section
                toggleRequiredFields(jobWorkSection, true);  // Add required to job work section
                toggleFabricConstructionSection();  // Handle job work specifics
            }
        }

        function toggleFabricConstructionSection() {
            const jobWorkType = document.querySelector('input[name="jobwork_type"]:checked');
            const fabricConstructionSection = document.getElementById('fabric_construction');
            const warpSizingSection = document.getElementById('warp_sizing');
            const sizingHiddenFields = document.getElementById('sizing_hidden_fields');
            
            if (!jobWorkType) return;

            // Show/hide fabric construction or warp sizing fields based on the job work type
            if (jobWorkType.value === 'Sizing + Weaving' || jobWorkType.value === 'Weaving') {
                fabricConstructionSection.classList.remove('hidden');
                warpSizingSection.classList.add('hidden');
                sizingHiddenFields.classList.remove('hidden');
                toggleRequiredFields(fabricConstructionSection, true);  // Required for weaving or sizing + weaving
                toggleRequiredFields(warpSizingSection, false);  // Not required
                toggleRequiredFields(sizingHiddenFields, true);  // Required for weaving or sizing + weaving
            } else if (jobWorkType.value === 'Sizing') {
                warpSizingSection.classList.remove('hidden');
                fabricConstructionSection.classList.add('hidden');
                sizingHiddenFields.classList.add('hidden');
                toggleRequiredFields(fabricConstructionSection, false);  // Not required
                toggleRequiredFields(warpSizingSection, true);  // Required for sizing
                toggleRequiredFields(sizingHiddenFields, false);  // Not required
            }
        }

        // Helper function to add or remove "required" attribute from all inputs in a section
        function toggleRequiredFields(section, shouldBeRequired) {
            const inputs = section.querySelectorAll('input, select');
            inputs.forEach(input => {
                if (shouldBeRequired) {
                    input.setAttribute('required', 'required');
                } else {
                    input.removeAttribute('required');
                }
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            // Set initial visibility and required fields
            toggleFormSections();
            toggleFabricConstructionSection();

            // Add event listeners to toggle sections when order type or job work type changes
            document.querySelectorAll('input[name="order_type"]').forEach(input => {
                input.addEventListener('change', toggleFormSections);
            });

            document.querySelectorAll('input[name="jobwork_type"]').forEach(input => {
                input.addEventListener('change', toggleFabricConstructionSection);
            });
        });
    </script>
</head>
<body>
    <main>
        <section class="create-sales-order-form">
            <h2>Create New Sales Order</h2>
            <form id="sales-order-form" action="submit_sales_order.php" method="POST">
                <label for="order_type">Order Type:</label><br>
                <input type="radio" name="order_type" value="Ownsales" required> Own Sales
                <input type="radio" name="order_type" value="Jobwork" required> Job Work<br><br>

                <!-- Own Sales Section -->
                <div id="ownsales-section" style="display:none;">
                    <div class="row1">
                        <div class="formitem3">
                            Order Qty (Meters) <br>
                            <input type="number" name="order_qty" required>
                        </div>
                        <div class="formitem3">
                            Price / Meter <br>
                            <input type="number" name="price" step="0.01" required>
                        </div>
                        <div class="formitem3">
                            Currency Type <br>
                            <select name="currency_type" required>
                                <option value="INR">INR</option>
                                <option value="USD">USD</option>
                                <option value="Pound">Pound</option>
                            </select>
                        </div>
                    </div>

                    <div class="row1">
                        <div class="formitem3">
                            Payment Terms <br>
                            <input type="text" name="payment_terms" required>
                        </div>
                        <div class="formitem3">
                            Buyer Name <br>
                            <select name="buyer_id" required>
                                <?php foreach ($buyers_data as $row): ?>
                                    <option value="<?= $row['buyer_id']; ?>"><?= $row['buyer_name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="formitem3">
                            Date of Confirmation <br>
                            <input type="date" name="date_of_confirmation" required>
                        </div>
                    </div>

                    <div class="row1">
                        <div class="formitem3">
                            Agent Name <br>
                            <select name="agent_id" required>
                                <?php foreach ($agents_data as $row): ?>
                                    <option value="<?= $row['agent_id']; ?>"><?= $row['agent_name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Job Work Section -->
                <div id="jobwork-section" style="display:none;">
                    <div class="row1">
                        <div class="formitem3">
                            Job Work Type <br>
                            <input type="radio" name="jobwork_type" value="Sizing" required> Sizing
                            <input type="radio" name="jobwork_type" value="Weaving" required> Weaving
                            <input type="radio" name="jobwork_type" value="Sizing + Weaving" required> Sizing + Weaving
                        </div>
                        <div class="formitem3">
                            Order Quantity (Meters) <br>
                            <input type="number" name="order_quantity" required>
                        </div>
                    </div>

                    <div class="row1">
                        <div class="formitem3">
                            Price / Pick Rate <br>
                            <input type="number" name="price_or_pickrate" step="0.01" required>
                        </div>
                        <div class="formitem3">
                            Payment Terms <br>
                            <input type="text" name="payment_terms" required>
                        </div>
                        <div class="formitem3">
                            Buyer Name <br>
                            <select name="buyer_id" required>
                                <?php foreach ($buyers_data as $row): ?>
                                    <option value="<?= $row['buyer_id']; ?>"><?= $row['buyer_name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row1">
                        <div class="formitem3">
                            Date of Confirmation <br>
                            <input type="date" name="date_of_confirmation" required>
                        </div>
                        <div class="formitem3">
                            Agent Name <br>
                            <select name="agent_id" required>
                                <?php foreach ($agents_data as $row): ?>
                                    <option value="<?= $row['agent_id']; ?>"><?= $row['agent_name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Sizing Section (if only sizing is selected) -->
                    <div id="warp_sizing" class="hidden">
                        <div class="row1">
                            <div class="formitem3">
                                Warp Count <br>
                                <input type="text" name="warp_count">
                            </div>
                            <div class="formitem3">
                                Sizing Type <br>
                                <input type="text" name="sizing_type">
                            </div>
                        </div>
                    </div>

                    <!-- Weaving Section (if weaving or sizing + weaving is selected) -->
                    <div id="fabric_construction" class="hidden">
                        <div class="row1">
                            <div class="formitem3">
                                Fabric Construction Type <br>
                                <input type="text" name="fabric_construction_type">
                            </div>
                        </div>
                    </div>

                    <div id="sizing_hidden_fields" class="hidden">
                        <div class="row1">
                            <div class="formitem3">
                                Hidden Field 1 <br>
                                <input type="text" name="sizing_hidden_field_1">
                            </div>
                            <div class="formitem3">
                                Hidden Field 2 <br>
                                <input type="text" name="sizing_hidden_field_2">
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit">Submit Order</button>
            </form>
        </section>
    </main>
</body>
</html>
