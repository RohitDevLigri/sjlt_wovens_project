<?php
include 'auth_check.php';
require_once 'dbconnect_master.php';
include 'navbar.php';
$connection = new MasterConnection();
$conn = $connection->getConnection();
$message = ''; // Initialize the message variable
$next_s1_no = 1; // Default value for s1_no if the table is empty
try {
    $stmt = $conn->query("SELECT MAX(s1_no) AS last_s1_no FROM buyer_details");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result && $result['last_s1_no'] !== null) {
        $next_s1_no = $result['last_s1_no'] + 1;
    }
} catch (PDOException $e) {
    $message = '<div class="error">Error fetching s1_no: ' . htmlspecialchars($e->getMessage()) . '</div>';
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $buyer_code = $_POST['buyer_code'];
        $buyer_name = $_POST['buyer_name'];
        $invoice_address = $_POST['invoice_address'];
        $contact_no = $_POST['contact_no'];
        $contact_person = $_POST['contact_person'];
        $delivery_address = $_POST['delivery_address'];
        $address_street = $_POST['address_street'];
        $address_city = $_POST['address_city'];
        $address_postal = $_POST['address_postal'];
        $city_pin = $_POST['city_pin'];
        $state = $_POST['state'];
        $country = $_POST['country'];
        $category = $_POST['category'];
        $phone = $_POST['phone'];
        $fax = $_POST['fax'];
        $email = $_POST['email'];
        $web_site = $_POST['web_site'];
        $currency = $_POST['currency'];
        $gstin = $_POST['gstin'];
        $pan_number = $_POST['pan_number'];
        $address = $address_street . ', ' . $address_city . ', ' . $address_postal;
        $sql = "INSERT INTO buyer_details (
            s1_no, buyer_code, buyer_name, invoice_address, contact_no, contact_person,
            delivery_address, address, city_pin, state, country, category,
            phone, fax, email, web_site, currency, gstin, pan_number
        ) VALUES (
            :s1_no, :buyer_code, :buyer_name, :invoice_address, :contact_no, :contact_person,
            :delivery_address, :address, :city_pin, :state, :country, :category,
            :phone, :fax, :email, :web_site, :currency, :gstin, :pan_number
        )";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':s1_no' => $next_s1_no,
            ':buyer_code' => $buyer_code,
            ':buyer_name' => $buyer_name,
            ':invoice_address' => $invoice_address,
            ':contact_no' => $contact_no,
            ':contact_person' => $contact_person,
            ':delivery_address' => $delivery_address,
            ':address' => $address,
            ':city_pin' => $city_pin,
            ':state' => $state,
            ':country' => $country,
            ':category' => $category,
            ':phone' => $phone,
            ':fax' => $fax,
            ':email' => $email,
            ':web_site' => $web_site,
            ':currency' => $currency,
            ':gstin' => $gstin,
            ':pan_number' => $pan_number,
        ]);
        $message = '<div class="success">Data inserted successfully!</div>';
        // Update the next s1_no after successful insertion
        $next_s1_no++;
    } catch (PDOException $e) {
        $message = '<div class="error">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/create_agent_buyer_sort.css">
    <title>Buyer Details</title>
</head>
<body>
    <main>
        <div class="container">
            <h1>Buyer Details</h1>
            <?php if (!empty($message)) echo $message; ?>
            <form method="POST" action="">
                <div class="buttons-container">
                    <button type="button" onclick="window.location.href='buyer_details_summary.php'">Back</button>
                    <button type="submit" class="buyerBtn">Submit</button>
                </div>
                <div class="row">
                    <!-- <div class="form-group">
                        <label for="s1_no">S1 No</label>
                        <input type="text" name="s1_no" id="s1_no" value="<?php echo $next_s1_no; ?>" readonly>
                    </div> -->
                    <div class="form-group">
                        <label for="buyer_code">Buyer Code</label>
                        <input type="text" name="buyer_code" id="buyer_code" required>
                    </div>
                    <div class="form-group">
                        <label for="buyer_name">Buyer Name</label>
                        <input type="text" name="buyer_name" id="buyer_name" required>
                    </div>
                </div>

                <div class="row">
                    <div class="form-group">
                        <label for="invoice_address">Invoice Address</label>
                        <textarea name="invoice_address" id="invoice_address" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="contact_no">Contact Number</label>
                        <input type="text" name="contact_no" id="contact_no" required>
                    </div>
                </div>

                <div class="row">
                    <div class="form-group">
                        <label for="contact_person">Contact Person</label>
                        <input type="text" name="contact_person" id="contact_person" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input type="text" name="phone" id="phone">
                    </div>
                </div>

                <div class="row">
                    <div class="form-group">
                        <label for="delivery_address">Delivery Address</label>
                        <textarea name="delivery_address" id="delivery_address" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="city_pin">City and PIN</label>
                        <input type="text" name="city_pin" id="city_pin" required>
                    </div>
                </div>

                <div class="row">
                    <div class="form-group">
                        <label for="address_street">Address</label>
                        <input type="text" name="address_street" id="address_street" required>
                    </div>
                    <div class="form-group">
                        <label for="state">State</label>
                        <input type="text" name="state" id="state" required>
                    </div>
                </div>
                <div class="row">
                    
                </div>
                <div class="row">
                    <div class="form-group">
                        <label for="address_city"></label>
                        <input type="text" name="address_city" id="address_city" required>
                    </div>
                    <div class="form-group">
                        <label for="country">Country</label>
                        <input type="text" name="country" id="country" required>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group">
                        <label for="address_postal"></label>
                        <input type="text" name="address_postal" id="address_postal" required>
                    </div>
                    <div class="form-group">
                        <label for="category">Category</label>
                        <input type="text" name="category" id="category">
                    </div>
                </div>
                <div class="row">
                    <div class="form-group">
                        <label for="pan_number">Pan Number</label>
                        <input type="text" name="pan_number" id="pan_number">
                    </div>
                    <div class="form-group">
                        <label for="fax">Fax</label>
                        <input type="text" name="fax" id="fax">
                    </div>
                </div>
                <div class="row">
                    <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" name="email" id="email">
                    </div>
                    <div class="form-group">
                        <label for="web_site">Website</label>
                        <input type="text" name="web_site" id="web_site">
                    </div>
                </div>
                <div class="row">
                    <div class="form-group">
                        <label for="currency">Currency</label>
                        <input type="text" name="currency" id="currency">
                    </div>
                    <div class="form-group">
                        <label for="gstin">GSTIN</label>
                        <input type="text" name="gstin" id="gstin">
                    </div>
                </div>
            </form>
        </div>
    </main>
</body>
</html>