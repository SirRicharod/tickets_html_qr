<?php
/**
 * check.php - Ticket Verification & Check-In Page
 * 
 * This page is opened when someone scans a QR code on a ticket.
 * 
 * What it does:
 *   1. Reads the encrypted order ID from the URL (?id=...)
 *   2. Decrypts it back to the real order ID using our helper.php
 *   3. Looks up the order in the database
 *   4. Shows the ticket details and current status
 *   5. If the ticket is valid (unused), shows a "Check In" button
 *   6. When confirmed, marks the ticket as "checked-in" in the database
 */

// Include the header (starts session, loads Bootstrap CSS/JS)
include 'includes/header.php';
require 'includes/conn.php';
require 'includes/helper.php'; // Our OpenSSL encrypt/decrypt functions

// ===============================================
// STEP 1: Get and decrypt the order ID from URL
// ===============================================

// Check if the 'id' parameter exists in the URL
if (!isset($_GET['id']) || trim($_GET['id']) === '') {
    die("
    <div class='container mt-5'>
        <div class='alert alert-danger'>
            <h4>Invalid Request</h4>
            <p>No ticket ID was provided. Please scan a valid QR code.</p>
        </div>
    </div>");
}

// Get the encrypted token from the URL
$token = trim($_GET['id']);

// Decrypt the token back to the original order ID
// decryptId() is defined in includes/helper.php
$order_id = decryptId($token);

// If decryption fails, the token was tampered with or invalid
if ($order_id === false) {
    die("
    <div class='container mt-5'>
        <div class='alert alert-danger'>
            <h4>Invalid Ticket</h4>
            <p>This ticket could not be verified. The QR code may be damaged or forged.</p>
        </div>
    </div>");
}

// ===============================================
// STEP 2: Handle Check-In (POST request)
// ===============================================

// This block runs when the staff clicks "Confirm Check-In"
// We use POST to prevent accidental re-submissions (e.g., refreshing the page)
$checkin_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_checkin'])) {
    
    // Update the order: set status to 'checked-in' and record the timestamp
    // We only update if the ticket is currently 'unused' (prevents double check-in)
    $stmt = $conn->prepare("UPDATE orders SET status = 'checked-in', checked_in_at = NOW() WHERE id = ? AND status = 'unused'");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();

    // affected_rows tells us how many rows were actually updated
    // If it's 1, the check-in was successful
    // If it's 0, the ticket was already checked in (no rows matched the WHERE clause)
    if ($stmt->affected_rows > 0) {
        $checkin_message = '<div class="alert alert-success mt-3">
            <h4>&#10004; Check-In Successful!</h4>
            <p>Ticket has been marked as <strong>checked-in</strong> at ' . date('d/m/Y H:i:s') . '</p>
        </div>';
    } else {
        $checkin_message = '<div class="alert alert-warning mt-3">
            <h4>Already Checked In</h4>
            <p>This ticket was already used. No changes were made.</p>
        </div>';
    }
    $stmt->close();
}

// ===============================================
// STEP 3: Look up the order in the database
// ===============================================

// Use a prepared statement to prevent SQL injection
// We JOIN with the users table to get the ticket holder's name and email
$stmt = $conn->prepare("
    SELECT 
        o.id,
        o.order_number,
        o.amount_tickets,
        o.total_price,
        o.order_date,
        o.status,
        o.checked_in_at,
        o.event_date,
        u.name AS user_name,
        u.email AS user_email
    FROM orders o
    JOIN users u ON o.user_id = u.id
    WHERE o.id = ?
");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();

// Check if the order exists
if ($result->num_rows === 0) {
    die("
    <div class='container mt-5'>
        <div class='alert alert-danger'>
            <h4>Ticket Not Found</h4>
            <p>No order was found with this ID. The ticket may have been deleted.</p>
        </div>
    </div>");
}

// Fetch the order data as an associative array
$order = $result->fetch_assoc();
$stmt->close();

// ===============================================
// STEP 4: Display the ticket information
// ===============================================

// Determine the badge color based on ticket status
$statusBadge = ($order['status'] === 'unused')
    ? '<span class="badge bg-success fs-5">VALID - Unused</span>'
    : '<span class="badge bg-danger fs-5">ALREADY CHECKED IN</span>';
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">

            <!-- Page title -->
            <h1 class="text-center mb-4">Ticket Verification</h1>

            <!-- Show check-in result message (if any) -->
            <?php echo $checkin_message; ?>

            <!-- Ticket Details Card -->
            <div class="card shadow">
                <div class="card-header <?php echo ($order['status'] === 'unused') ? 'bg-success' : 'bg-secondary'; ?> text-white">
                    <h3 class="mb-0">Order #<?php echo htmlspecialchars($order['order_number']); ?></h3>
                </div>
                <div class="card-body">
                    
                    <!-- Status Badge (prominent) -->
                    <div class="text-center mb-4">
                        <?php echo $statusBadge; ?>
                    </div>

                    <!-- Order Details Table -->
                    <table class="table table-bordered">
                        <tr>
                            <th>Ticket Holder</th>
                            <td><?php echo htmlspecialchars($order['user_name']); ?></td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td><?php echo htmlspecialchars($order['user_email']); ?></td>
                        </tr>
                        <tr>
                            <th>Number of Tickets</th>
                            <td><?php echo (int) $order['amount_tickets']; ?></td>
                        </tr>
                        <tr>
                            <th>Total Price</th>
                            <td>&euro; <?php echo number_format($order['total_price'], 2); ?></td>
                        </tr>
                        <tr>
                            <th>Order Date</th>
                            <td><?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                <?php echo htmlspecialchars($order['status']); ?>
                                <?php if ($order['checked_in_at']): ?>
                                    <br><small class="text-muted">
                                        Checked in at: <?php echo date('d/m/Y H:i:s', strtotime($order['checked_in_at'])); ?>
                                    </small>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>

                    <!-- ============================================= -->
                    <!-- Check-In Button (only shown for unused tickets) -->
                    <!-- ============================================= -->
                    <?php if ($order['status'] === 'unused'): ?>
                        <div class="card bg-light p-3 mt-3">
                            <h5>Ready to check in this ticket?</h5>
                            <p class="text-muted">This action cannot be undone. The ticket will be marked as used.</p>
                            
                            <!-- 
                                We use a form with POST method so the check-in action
                                is intentional (not triggered by a page refresh).
                                The hidden field 'confirm_checkin' tells our PHP code
                                that the staff confirmed the check-in.
                                The hidden field 'id' passes the encrypted token back.
                            -->
                            <form method="POST" action="check.php?id=<?php echo urlencode($token); ?>"
                                  onsubmit="return confirm('Are you sure you want to check in this ticket?\n\nTicket holder: <?php echo htmlspecialchars($order['user_name'], ENT_QUOTES); ?>\nOrder: <?php echo htmlspecialchars($order['order_number'], ENT_QUOTES); ?>');">
                                <input type="hidden" name="confirm_checkin" value="1">
                                <button type="submit" class="btn btn-primary btn-lg w-100">
                                    &#10004; Confirm Check-In
                                </button>
                            </form>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-secondary mt-3 text-center">
                            <strong>This ticket has already been used.</strong>
                            <br>No further action needed.
                        </div>
                    <?php endif; ?>

                </div><!-- card-body -->
            </div><!-- card -->

        </div><!-- col -->
    </div><!-- row -->
</div><!-- container -->

</body>
</html>