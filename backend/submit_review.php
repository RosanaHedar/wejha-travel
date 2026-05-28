<?php
session_start();
include 'wegha_db.php';

// --- SECURITY SHIELD ---
// Redirect to login if a non-authenticated request hits this processor directly
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $rating = intval($_POST['rating']);
    $comment = mysqli_real_escape_string($conn, $_POST['comment']);

    // Safely evaluate if this is a standard package review or a Custom Builder evaluation (NULL)
    $package_id = !empty($_POST['package_id']) ? intval($_POST['package_id']) : null;
    $package_id_val = ($package_id !== null) ? $package_id : "NULL";

    // Determine target fallback endpoint routing (profile timeline vs static item catalog grid)
    $redirect_dest = !empty($_POST['redirect']) ? trim($_POST['redirect']) : "details.php?id=" . $package_id;

    // Insert feedback item in isolated quarantine status ('Pending') for administrator evaluation audit loops
    $sql = "INSERT INTO reviews (user_id, package_id, rating, comment, status) 
            VALUES ($user_id, $package_id_val, $rating, '$comment', 'Pending')";

    if (mysqli_query($conn, $sql)) {
        // Appends the validation parameters to trigger the green success notices safely
        $connector = (strpos($redirect_dest, '?') !== false) ? '&' : '?';
        header("Location: " . $redirect_dest . $connector . "review_msg=submitted");
        exit();
    } else {
        die("Relational system entry failure: " . mysqli_error($conn));
    }
} else {
    // Safety exit rule: if hit directly via standard browser lookup instead of a submission form, route home
    header("Location: index.php");
    exit();
}
