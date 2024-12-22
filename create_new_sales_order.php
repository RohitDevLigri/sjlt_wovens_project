<?php
include('auth_check.php');
include('dbconnect.php');
include('navbar.php');

$db = new Connection();
$conn = $db->getConnection();

try {
    $buyers_stmt = $conn->prepare("SELECT buyer_id, buyer_name FROM buyer");
    $buyers_stmt->execute();
    $buyers_data = $buyers_stmt->fetchAll(PDO::FETCH_ASSOC);

    $agents_stmt = $conn->prepare("SELECT agent_id, agent_name FROM agent");
    $agents_stmt->execute();
    $agents_data = $agents_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("Error fetching data: " . $e->getMessage());
}

$message = "";
$message_type = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Handle form submission
    try {
        $order_qty = $_POST['order_qty'];
        $price = $_POST['price'];
        $currency_type = $_POST['currency_type'];
        $payment_terms = $_POST['payment_terms'];
        $buyer_id = $_POST['buyer_id'];
        $date_of_confirmation = $_POST['date_of_confirmation'];
        $agent_id = $_POST['agent_id'];
        $warp = $_POST['warp'];
        $weft = $_POST['weft'];
        $epi = $_POST['epi'];
        $ppi = $_POST['ppi'];
        $ply = $_POST['ply'];
        $width = $_POST['width'];
        $fiber_type = $_POST['fiber_type'];
        $selvedge_width = $_POST['selvedge_width'];
        $selvedge_weave = $_POST['selvedge_weave'];
        $inspaction_type = $_POST['inspaction_type'];
        $inspaction_standard = $_POST['inspaction_standard'];
        $piece_length = $_POST['piece_length'];
        $packing_type = $_POST['packing_type'];
        $freight = $_POST['freight'];
        $delivery_address = $_POST['delivery_address'];
        $commision = $_POST['commision'];
        $invoice_address = $_POST['invoice_address'];

        // Prepare SQL statement
        $stmt = $conn->prepare("INSERT INTO salesorder (
            order_qty, price, currency_type, payment_terms, buyer_id, 
            date_of_confirmation, agent_id, warp, weft, epi, ppi, ply, 
            width, fiber_type, selvedge_width, selvedge_weave, 
            inspaction_type, inspaction_standard, piece_length, 
            packing_type, freight, delivery_address, commision, 
            invoice_address
        ) VALUES (
            :order_qty, :price, :currency_type, :payment_terms, :buyer_id, 
            :date_of_confirmation, :agent_id, :warp, :weft, :epi, :ppi, :ply, 
            :width, :fiber_type, :selvedge_width, :selvedge_weave, 
            :inspaction_type, :inspaction_standard, :piece_length, 
            :packing_type, :freight, :delivery_address, :commision, 
            :invoice_address
        )");

        $stmt->bindParam(':order_qty', $order_qty);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':currency_type', $currency_type);
        $stmt->bindParam(':payment_terms', $payment_terms);
        $stmt->bindParam(':buyer_id', $buyer_id);
        $stmt->bindParam(':date_of_confirmation', $date_of_confirmation);
        $stmt->bindParam(':agent_id', $agent_id);
        $stmt->bindParam(':warp', $warp);
        $stmt->bindParam(':weft', $weft);
        $stmt->bindParam(':epi', $epi);
        $stmt->bindParam(':ppi', $ppi);
        $stmt->bindParam(':ply', $ply);
        $stmt->bindParam(':width', $width);
        $stmt->bindParam(':fiber_type', $fiber_type);
        $stmt->bindParam(':selvedge_width', $selvedge_width);
        $stmt->bindParam(':selvedge_weave', $selvedge_weave);
        $stmt->bindParam(':inspaction_type', $inspaction_type);
        $stmt->bindParam(':inspaction_standard', $inspaction_standard);
        $stmt->bindParam(':piece_length', $piece_length);
        $stmt->bindParam(':packing_type', $packing_type);
        $stmt->bindParam(':freight', $freight);
        $stmt->bindParam(':delivery_address', $delivery_address);
        $stmt->bindParam(':commision', $commision);
        $stmt->bindParam(':invoice_address', $invoice_address);
        $stmt->execute();

        $message = "Order submitted successfully!";
        $message_type = "success";
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
        $message_type = "error";
        error_log($e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Sales Order</title>
    <link rel="stylesheet" href="css/create_new_sales_order.css"> 
    <script>
        function toggleOrderDetails() {
            const orderType = document.querySelector('input[name="order_type"]:checked').value;
            if (orderType === "Ownsales") {
                document.getElementById('ownsales-details').style.display = 'block';
                document.getElementById('jobwork-details').style.display = 'none';
            } else if (orderType === "Jobwork") {
                document.getElementById('jobwork-details').style.display = 'block';
                document.getElementById('ownsales-details').style.display = 'none';
            }
        }
        function toggleJobWorkDetails() {
            const jobWorkType = document.querySelector('input[name="jobwork_type"]:checked').value;
            if (jobWorkType === "Sizing") {
                document.getElementById('sizing-details').style.display = 'block';
                document.getElementById('weaving-details').style.display = 'none';
            } else if (jobWorkType === "Weaving" || jobWorkType === "Sizing+Weaving") {
                document.getElementById('weaving-details').style.display = 'block';
                document.getElementById('sizing-details').style.display = 'none';
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <!-- Display success or error message -->
        <?php if ($message): ?>
            <div class="message <?= $message_type; ?>">
                <?= htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        <h1>Create New Sales Order</h1>
        <div class="order-type">
            <input type="radio" id="ownsales" name="order_type" value="Ownsales" onclick="toggleOrderDetails()">
            <label for="ownsales">Own Sales</label>
            
            <input type="radio" id="jobwork" name="order_type" value="Jobwork" onclick="toggleOrderDetails()">
            <label for="jobwork">Job Work</label>
        </div>
        <form method="POST">
            <div id="ownsales-details" class="order-details" style="display: none;">
                <!-- row1 -->
                <h3>Order Details:</h3>
                <div class="form-row">    
                    <div class="form-group">
                        <label for="order_qty">Order Qty (Meters):</label>
                        <input type="number" id="order_qty" name="order_qty">
                    </div>
                    <div class="form-group">
                        <label for="price">Price / Meter:</label>
                        <input type="number" id="price" name="price">
                    </div>
                    <div class="form-group">
                        <label for="currency_type">Currency Type:</label>
                        <select name="currency_type" id="currency_type">
                            <option value="INR">INR</option>
                            <option value="USD">USD</option>
                            <option value="Pound">Pound</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="payment_terms">Payment Terms:</label>
                        <input type="text" id="payment_terms" name="payment_terms">
                    </div>
                </div>
                <!-- row 2 -->
                <h3>Fabric Construction:</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label for="date_of_confirmation">Date of Confirmation:</label>
                        <input type="date" id="date_of_confirmation" name="date_of_confirmation">
                    </div>
                    <div class="form-group">
                        <label for="warp">Warp:</label>
                        <input type="text" id="warp" name="warp">
                    </div>
                    <div class="form-group">
                        <label for="weft">Weft:</label>
                        <input type="text" id="weft" name="weft">
                    </div>
                    <div class="form-group">
                        <label for="epi">EPI:</label>
                        <input type="text" id="epi" name="epi">
                    </div>
                </div>
                <!-- row 3 -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="ppi">PPI:</label>
                        <input type="text" id="ppi" name="ppi">
                    </div>
                    <div class="form-group">
                        <label for="ply">PLY:</label>
                        <input type="text" id="ply" name="ply">
                    </div>
                    <div class="form-group">
                        <label for="width">Width(inches):</label>
                        <input type="text" id="width" name="width">
                    </div>
                    <div class="form-group">
                        <label for="fiber_type">Fiber Type:</label>
                        <input type="text" id="fiber_type" name="fiber_type">
                    </div>
                    </div>
                <!-- row 4 -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="selvedge_width">Selvedge Width(inches):</label>
                        <input type="text" id="selvedge_width" name="selvedge_width">
                    </div>
                    <div class="form-group">
                        <label for="selvedge_weave">Selvedge Weave:</label>
                        <input type="text" id="selvedge_weave" name="selvedge_weave">
                    </div>
                    <div class="form-group">
                        <label for=""></label>
                    </div>
                    <div class="form-group">
                        <label for=""></label>
                    </div>
                </div>
                <!-- row 5 -->
                <h3>Despatch Details:</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label for="inspaction_type">Inspection Type:</label>
                        <input type="text" id="inspaction_type" name="inspaction_type">
                    </div>
                    <div class="form-group">
                        <label for="inspaction_standard">Inspection Standard:</label>
                        <input type="text" id="inspaction_standard" name="inspaction_standard">
                    </div>
                    <div class="form-group">
                        <label for="piece_length">Piece Length:</label>
                        <input type="number" id="piece_length" name="piece_length">
                    </div>
                    <div class="form-group">
                        <label for="packing_type">Packing Type:</label>
                        <input type="text" id="packing_type" name="packing_type">
                    </div>
                </div>
                <!-- row 6 -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="freight">Freight:</label>
                        <input type="text" id="freight" name="freight">
                    </div>
                    <div class="form-group">
                        <label for="delivery_address">Delivery Address:</label>
                        <textarea id="delivery_address" name="delivery_address"></textarea>
                    </div>
                    <div class="form-group">
                        <label for=""></label>
                    </div>
                    <div class="form-group">
                        <label for=""></label>
                    </div>
                </div>
                <!-- row 7 -->
                <h3>Buyer Details:</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label for="buyer_name">Buyer Name:</label>
                        <select name="buyer_id" id="buyer_name">
                            <?php foreach ($buyers_data as $row) { ?>
                                <option value="<?= $row['buyer_id']; ?>"><?= $row['buyer_name']; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="agent_name">Agent Name:</label>
                        <select name="agent_id" id="agent_name">
                            <?php foreach ($agents_data as $row) { ?>
                                <option value="<?= $row['agent_id']; ?>"><?= $row['agent_name']; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="commision">Commision:</label>
                        <input type="text" id="commision" name="commision">
                    </div>
                    <div class="form-group">
                        <label for="invoice_address">Invoice Address:</label>
                        <input type="text" id="invoice_address" name="invoice_address" >
                    </div>
                </div>
            </div>

            <div id="jobwork-details" class="order-details" style="display: none;">
                <h3>Job Work Details:</h3>
                <div class="order-type">
                    <input type="radio" name="jobwork_type" value="Sizing" onclick="toggleJobWorkDetails()">
                    <label for="jobwork_type">Sizing</label>
                                        <input type="radio" name="jobwork_type" value="Weaving" onclick="toggleJobWorkDetails()">
                    <label for="jobwork_type">Weaving</label>
                    
                    <input type="radio" name="jobwork_type" value="Sizing+Weaving" onclick="toggleJobWorkDetails()">
                    <label for="jobwork_type">Sizing + Weaving</label>
                </div>
                <div id="sizing-details" style="display: none;">
                    <!-- row 8 -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="order_qty_sizing">Order Qty (Meters):</label>
                            <input type="number" id="order_qty_sizing" name="order_qty_sizing">
                        </div>
                        <div class="form-group">
                            <label for="price_sizing">Price / Pick Rate:</label>
                            <input type="number" id="price_sizing" name="price_sizing">
                        </div>
                        <div class="form-group">
                            <label for="payment_terms_sizing">Payment Terms:</label>
                            <input type="text" id="payment_terms_sizing" name="payment_terms_sizing">
                        </div>
                        <div class="form-group">
                            <label for="buyer_name_sizing">Buyer Name:</label>
                            <select name="buyer_id_sizing" id="buyer_name_sizing">
                                <?php foreach ($buyers_data as $row) { ?>
                                    <option value="<?= $row['buyer_id']; ?>"><?= $row['buyer_name']; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <!-- row 9 -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="date_of_confirmation_sizing">Date of Confirmation:</label>
                            <input type="date" id="date_of_confirmation_sizing" name="date_of_confirmation_sizing">
                        </div>
                        <div class="form-group">
                            <label for="agent_name_sizing">Agent Name:</label>
                            <select name="agent_id_sizing" id="agent_name_sizing">
                                <?php foreach ($agents_data as $row) { ?>
                                    <option value="<?= $row['agent_id']; ?>"><?= $row['agent_name']; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="warp">Warp:</label>
                            <input type="text" id="warp" name="warp">
                        </div>
                        <div class="form-group">
                            <label for="sizing_type">Sizing Type:</label>
                            <input type="text" id="sizing_type" name="sizing_type">
                        </div>
                    </div>
                </div>
                <div id="weaving-details" style="display: none;">
                    <!-- row 10 -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="order_qty_sizing">Order Qty (Meters):</label>
                            <input type="number" id="order_qty_sizing" name="order_qty_sizing">
                        </div>
                        <div class="form-group">
                            <label for="price_sizing">Price / Pick Rate:</label>
                            <input type="number" id="price_sizing" name="price_sizing">
                        </div>
                        <div class="form-group">
                            <label for="payment_terms_sizing">Payment Terms:</label>
                            <input type="text" id="payment_terms_sizing" name="payment_terms_sizing">
                        </div>
                        <div class="form-group">
                            <label for="buyer_name_sizing">Buyer Name:</label>
                            <select name="buyer_id_sizing" id="buyer_name_sizing">
                                <?php foreach ($buyers_data as $row) { ?>
                                    <option value="<?= $row['buyer_id']; ?>"><?= $row['buyer_name']; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <!-- row 11 -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="date_of_confirmation_sizing">Date of Confirmation:</label>
                            <input type="date" id="date_of_confirmation_sizing" name="date_of_confirmation_sizing">
                        </div>
                        <div class="form-group">
                            <label for="agent_name_sizing">Agent Name:</label>
                            <select name="agent_id_sizing" id="agent_name_sizing">
                                <?php foreach ($agents_data as $row) { ?>
                                    <option value="<?= $row['agent_id']; ?>"><?= $row['agent_name']; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="fabric_construction_type">Fabric Construction Type:</label>
                            <input type="text" id="fabric_construction_type" name="fabric_construction_type">
                        </div>
                        <div class="form-group">
                            <label for="warp">Warp:</label>
                            <input type="text" id="warp" name="warp">
                        </div>
                    </div>
                    <!-- row 12 -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="weft">Weft:</label>
                            <input type="text" id="weft" name="weft">
                        </div>
                        <div class="form-group">
                            <label for=""></label>
                        </div>
                        <div class="form-group">
                            <label for=""></label>
                        </div>
                        <div class="form-group">
                            <label for=""></la>
                        </div>
                    </div>
                </div>
            </div>
            <button type="submit" class="submit-btn">Submit</button>
        </form>
    </div>
</body>
</html>
