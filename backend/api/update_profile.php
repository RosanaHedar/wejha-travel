<?php
// 1. REST API CORS COMPLIANCE HEADERS
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

session_start();
include '../wegha_db.php';

// 2. SECURITY LAYER
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized access request barred."]);
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch present record fallback references
$user = $conn->query("SELECT image, password_hash FROM users WHERE user_id = $user_id")->fetch_assoc();

// ========================================================
// ACTION DISPATCHER BRANCH 1: PROFILE / IMAGE CHANGEOVER
// ========================================================
if (isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $name  = mysqli_real_escape_string($conn, trim($_POST['name'] ?? ''));
    $phone = mysqli_real_escape_string($conn, trim($_POST['phone'] ?? ''));
    $email = mysqli_real_escape_string($conn, trim($_POST['email'] ?? ''));
    $image_path = $user['image'];

    if (empty($name) || empty($email)) {
        http_response_code(400);
        echo json_encode(["error" => "Name and email fields cannot be empty blocks."]);
        exit();
    }

    // Process file system picture modifications
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $folder = "../uploads/"; // Adjust routing direction up to root directory upload location
        if (!is_dir($folder)) mkdir($folder, 0777, true);

        $filename = time() . "_" . preg_replace("/[^a-zA-Z0-9.]/", "_", $_FILES['image']['name']);
        $target = $folder . $filename;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
            // Save relative route structure string for unified fetching matching layouts
            $image_path = "uploads/" . $filename;
        }
    }

    $update_sql = "UPDATE users SET full_name='$name', phone='$phone', email='$email', image='$image_path' WHERE user_id='$user_id'";
    if (mysqli_query($conn, $update_sql)) {
        http_response_code(200);
        echo json_encode([
            "message" => "Account parameters synchronized successfully.",
            "imgUrl"  => $image_path
        ]);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Profile entry mutation update error: " . mysqli_error($conn)]);
    }
    exit();
}

// ========================================================
// ACTION DISPATCHER BRANCH 2: PASSWORD SECURITY MANIPULATION
// ========================================================
if (isset($_POST['action']) && $_POST['action'] === 'change_password') {
    $current_pass = $_POST['current_pass'] ?? '';
    $new_pass     = $_POST['new_pass'] ?? '';
    $confirm_pass = $_POST['confirm_pass'] ?? '';

    if (empty($current_pass) || empty($new_pass) || empty($confirm_pass)) {
        http_response_code(400);
        echo json_encode(["error" => "All cryptographic account modification fields are required."]);
        exit();
    }

    if ($new_pass !== $confirm_pass) {
        http_response_code(400);
        echo json_encode(["error" => "Confirmation verification error: New password matches mismatch boundaries."]);
        exit();
    }

    if (password_verify($current_pass, $user['password_hash'])) {
        $hashed = password_hash($new_pass, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
        $stmt->bind_param("si", $hashed, $user_id);

        if ($stmt->execute()) {
            http_response_code(200);
            echo json_encode(["message" => "Security parameters re-compiled successfully! Password rewritten."]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Encryption write failure details error."]);
        }
        $stmt->close();
    } else {
        http_response_code(403);
        echo json_encode(["error" => "Current password confirmation mismatch. Access verification barred."]);
    }
    exit();
}

// Default error boundary if action parameter matches invalid branches
http_response_code(400);
echo json_encode(["error" => "Invalid dispatcher instruction parameter received."]);
exit();
