<?php
session_start();
include 'wegha_db.php';

// --- THE ADMIN SHIELD ---
if (!isset($_SESSION['admin_id'])) {
    exit;
}

// Set headers for download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=Wegha_Sales_Report_' . date('Y-m-d') . '.csv');

// Create a file pointer connected to the output stream
$output = fopen('php://output', 'w');

// Set CSV Column Headers
fputcsv($output, array('Booking ID', 'Customer Name', 'Trip Title', 'Travel Date', 'Amount Paid', 'Status'));

// Fetch Confirmed Data (FIXED: Switched to LEFT JOIN to process custom packages safely)
$query = "SELECT b.booking_id, 
                 u.full_name, 
                 COALESCE(p.title, 'Custom Tailored Trip') as title, 
                 b.travel_date, 
                 b.total_price, 
                 b.status 
          FROM bookings b 
          JOIN users u ON b.user_id = u.user_id 
          LEFT JOIN packages p ON b.package_id = p.package_id 
          WHERE b.status = 'Confirmed'
          ORDER BY b.booking_date DESC";

$result = mysqli_query($conn, $query);

while ($row = mysqli_fetch_assoc($result)) {
    fputcsv($output, $row);
}

fclose($output);
exit();
