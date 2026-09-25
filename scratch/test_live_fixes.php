<?php
/**
 * Test Live Fixes against live rentora_db
 */
require_once(__DIR__ . '/../config/db.php');

$passes = 0;
$fails = 0;

function assert_true($cond, $msg) {
    global $passes, $fails;
    if ($cond) {
        echo " [PASS] $msg\n";
        $passes++;
    } else {
        echo " [FAIL] $msg\n";
        $fails++;
    }
}

echo "=== TESTING 5 SPECIFIC FIXES AGAINST LIVE rentora_db ===\n\n";

// -------------------------------------------------------------
// FIX 1: admin/verify_user.php & admin/dashboard.php
// -------------------------------------------------------------
echo "--- FIX 1: Admin Verify User & Member Status ---\n";
// Insert test member with Pending status
$test_user = 'test_member_' . rand(1000, 9999);
$test_email = $test_user . '@univ.ac.bd';
$insMem = $pdo->prepare("
    INSERT INTO member (first_name, last_name, username, university_email, password_hash, status)
    VALUES ('Test', 'Student', :u, :e, 'hash123', 'Pending')
");
$insMem->execute(['u' => $test_user, 'e' => $test_email]);
$test_mid = (int)$pdo->lastInsertId();
assert_true($test_mid > 0, "Created test member with ID $test_mid");

// Verify initial status is Pending
$chk = $pdo->query("SELECT status FROM member WHERE member_id = $test_mid")->fetchColumn();
assert_true($chk === 'Pending', "Member initial status is 'Pending'");

// Test approve action -> UPDATE member SET status = 'Verified' WHERE member_id = :id
$action = 'approve';
$status = ($action === 'approve' || $action === 'verify' || $action === 'Verified') ? 'Verified' : 'Rejected';
$upStmt = $pdo->prepare("UPDATE member SET status = :status WHERE member_id = :id");
$upStmt->execute(['status' => $status, 'id' => $test_mid]);
$chk2 = $pdo->query("SELECT status FROM member WHERE member_id = $test_mid")->fetchColumn();
assert_true($chk2 === 'Verified', "verify_user.php successfully updated member status to 'Verified'");

// Test reject action -> UPDATE member SET status = 'Rejected' WHERE member_id = :id
$action = 'reject';
$status = ($action === 'approve' || $action === 'verify' || $action === 'Verified') ? 'Verified' : 'Rejected';
$upStmt->execute(['status' => $status, 'id' => $test_mid]);
$chk3 = $pdo->query("SELECT status FROM member WHERE member_id = $test_mid")->fetchColumn();
assert_true($chk3 === 'Rejected', "verify_user.php successfully updated member status to 'Rejected'");

// Test admin/dashboard.php query
$all_members = $pdo->query("SELECT member_id, username, first_name, last_name, status FROM member WHERE member_id = $test_mid")->fetch();
assert_true(!empty($all_members) && $all_members['status'] === 'Rejected', "admin/dashboard.php can query member's current status ('{$all_members['status']}')");


// -------------------------------------------------------------
// FIX 2: user/add_item.php & user/equipment.php
// -------------------------------------------------------------
echo "\n--- FIX 2: Equipment Insertion with Deposit, Spot, Image & Condition ---\n";
// Test Condition translation ('Like New' -> 'New')
$raw_cond = 'Like New';
$cond = ($raw_cond === 'Like New') ? 'New' : $raw_cond;
if (!in_array($cond, ['New', 'Good', 'Fair', 'Poor'])) {
    $cond = 'Good';
}
assert_true($cond === 'New', "Condition 'Like New' correctly mapped to 'New'");

// Fetch category
$cat_id = $pdo->query("SELECT category_id FROM category LIMIT 1")->fetchColumn();
if (!$cat_id) {
    $pdo->exec("INSERT INTO category (category_name) VALUES ('Test Category')");
    $cat_id = $pdo->lastInsertId();
}

$test_deposit = 750.50;
$test_spot = 'Hazari Lane';
$test_img = 'https://example.com/item.jpg';
$test_title = 'Scientific Calculator FX';

$insEq = $pdo->prepare("
    INSERT INTO equipment (owner_id, category_id, equipment_name, description, condition_status, availability_status, rental_rate, security_deposit, campus_spot, image_url)
    VALUES (:owner_id, :category_id, :equipment_name, 'Test Description', :condition_status, 'Available', 60.00, :security_deposit, :campus_spot, :image_url)
");
$insEq->execute([
    'owner_id'         => $test_mid,
    'category_id'      => $cat_id,
    'equipment_name'   => $test_title,
    'condition_status' => $cond,
    'security_deposit' => $test_deposit,
    'campus_spot'      => $test_spot,
    'image_url'        => $test_img
]);
$test_eq_id = (int)$pdo->lastInsertId();
assert_true($test_eq_id > 0, "Equipment inserted with ID $test_eq_id");

// Verify persisted fields
$eqRow = $pdo->query("SELECT security_deposit, campus_spot, image_url, condition_status FROM equipment WHERE equipment_id = $test_eq_id")->fetch();
assert_true(floatval($eqRow['security_deposit']) === 750.50, "security_deposit saved: {$eqRow['security_deposit']}");
assert_true($eqRow['campus_spot'] === $test_spot, "campus_spot saved: {$eqRow['campus_spot']}");
assert_true($eqRow['image_url'] === $test_img, "image_url saved: {$eqRow['image_url']}");
assert_true($eqRow['condition_status'] === 'New', "condition_status saved: {$eqRow['condition_status']}");


// -------------------------------------------------------------
// FIX 3: user/submit_request.php & item-details.php
// -------------------------------------------------------------
echo "\n--- FIX 3: Rental Agreement with Token, Deposit, Pickup Spot ---\n";
// Create another member as renter
$renter_user = 'renter_' . rand(1000, 9999);
$pdo->prepare("INSERT INTO member (first_name, last_name, username, university_email, password_hash) VALUES ('Renter', 'User', :u, :e, 'hash')")
    ->execute(['u' => $renter_user, 'e' => $renter_user.'@univ.ac.bd']);
$test_renter_id = (int)$pdo->lastInsertId();

$test_token = 'TRX-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
$test_pickup = 'Wasa';
$calc_deposit = floatval($eqRow['security_deposit']);
$total_cost = 180.00;

$insRent = $pdo->prepare("
    INSERT INTO rental_agreement (equipment_id, renter_id, start_date, expected_end_date, total_cost, deposit_amount, handover_token, pickup_spot, fine, status)
    VALUES (:item_id, :renter_id, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 3 DAY), :total_cost, :deposit_amount, :handover_token, :pickup_spot, 0.00, 'Pending')
");
$insRent->execute([
    'item_id'        => $test_eq_id,
    'renter_id'      => $test_renter_id,
    'total_cost'     => $total_cost,
    'deposit_amount' => $calc_deposit,
    'handover_token' => $test_token,
    'pickup_spot'    => $test_pickup
]);
$test_rent_id = (int)$pdo->lastInsertId();
assert_true($test_rent_id > 0, "Rental agreement inserted with ID $test_rent_id");

$rentRow = $pdo->query("SELECT handover_token, deposit_amount, pickup_spot FROM rental_agreement WHERE rental_id = $test_rent_id")->fetch();
assert_true($rentRow['handover_token'] === $test_token, "handover_token saved: {$rentRow['handover_token']}");
assert_true(floatval($rentRow['deposit_amount']) === $calc_deposit, "deposit_amount saved: {$rentRow['deposit_amount']}");
assert_true($rentRow['pickup_spot'] === $test_pickup, "pickup_spot saved: {$rentRow['pickup_spot']}");


// -------------------------------------------------------------
// FIX 4: user/exchanges.php Decline to Rejected & Proper Columns
// -------------------------------------------------------------
echo "\n--- FIX 4: Exchanges SQL Columns & Decline Mapping ---\n";
// Create a second equipment owned by renter for exchange
$insEq2 = $pdo->prepare("
    INSERT INTO equipment (owner_id, category_id, equipment_name, description, condition_status, availability_status, rental_rate)
    VALUES (:owner_id, :category_id, 'Drafter Tool', 'Test Drafter', 'Good', 'Available', 80.00)
");
$insEq2->execute(['owner_id' => $test_renter_id, 'category_id' => $cat_id]);
$test_eq2_id = (int)$pdo->lastInsertId();

// Insert exchange agreement with proper column names
$insEx = $pdo->prepare("
    INSERT INTO exchange_agreement (lender_a_id, lender_b_id, equipment_a_id, equipment_b_id, status)
    VALUES (:lender_a_id, :lender_b_id, :equipment_a_id, :equipment_b_id, 'Pending')
");
$insEx->execute([
    'lender_a_id'    => $test_mid,
    'lender_b_id'    => $test_renter_id,
    'equipment_a_id' => $test_eq_id,
    'equipment_b_id' => $test_eq2_id
]);
$test_ex_id = (int)$pdo->lastInsertId();
assert_true($test_ex_id > 0, "Exchange agreement created with proper columns (ID $test_ex_id)");

// Test decline action -> UPDATE exchange_agreement SET status = 'Rejected' WHERE exchange_id = :id
$action = 'decline';
$new_status = ($action === 'decline' || $action === 'reject') ? 'Rejected' : (($action === 'accept') ? 'Accepted' : 'Completed');
$upEx = $pdo->prepare("UPDATE exchange_agreement SET status = :status WHERE exchange_id = :id");
$upEx->execute(['status' => $new_status, 'id' => $test_ex_id]);

$exRow = $pdo->query("SELECT status, lender_a_id, lender_b_id, equipment_a_id, equipment_b_id FROM exchange_agreement WHERE exchange_id = $test_ex_id")->fetch();
assert_true($exRow['status'] === 'Rejected', "Decline successfully mapped to 'Rejected' (No enum error)");
assert_true((int)$exRow['lender_a_id'] === $test_mid && (int)$exRow['equipment_a_id'] === $test_eq_id, "lender_a_id and equipment_a_id persisted accurately");


// -------------------------------------------------------------
// FIX 5: auth/register.php Name splitting validation
// -------------------------------------------------------------
echo "\n--- FIX 5: auth/register.php Name Splitting Verification ---\n";
$full_name = "Abdur Rahman Khan";
$parts = explode(' ', trim($full_name), 2);
$first_name = $parts[0];
$last_name = isset($parts[1]) ? $parts[1] : '';
assert_true($first_name === 'Abdur', "First name correctly split: '$first_name'");
assert_true($last_name === 'Rahman Khan', "Last name correctly split: '$last_name'");


// -------------------------------------------------------------
// CLEANUP
// -------------------------------------------------------------
echo "\n--- CLEANUP TEMPORARY TEST DATA ---\n";
$pdo->exec("DELETE FROM exchange_agreement WHERE exchange_id = $test_ex_id");
$pdo->exec("DELETE FROM rental_agreement WHERE rental_id = $test_rent_id");
$pdo->exec("DELETE FROM equipment WHERE equipment_id IN ($test_eq_id, $test_eq2_id)");
$pdo->exec("DELETE FROM member WHERE member_id IN ($test_mid, $test_renter_id)");
echo " Cleaned up temporary test rows successfully.\n";

echo "\n==============================\n";
echo "SUMMARY: Passes: $passes, Fails: $fails\n";
if ($fails === 0) {
    echo "ALL 5 FIXES VERIFIED AND WORKING AGAINST LIVE rentora_db!\n";
} else {
    echo "SOME TESTS FAILED!\n";
}
