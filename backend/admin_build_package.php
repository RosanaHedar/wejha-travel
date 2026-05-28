<?php
session_start();
include 'wegha_db.php';

// --- ADMIN SHIELD ---
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$dest_msg = "";
$service_msg = "";

// Initialize Edit States
$edit_dest_mode = false;
$edit_dest_id = 0;
$dest_to_edit = ['destination_name' => '', 'image_url' => ''];

$edit_service_mode = false;
$edit_service_id = 0;
$service_to_edit = ['destination_id' => '', 'service_name' => '', 'service_type' => '', 'price' => ''];

// ========================================================
// GET LOGIC: DETECT DIRECTIVE EDIT LOADS
// ========================================================
if (isset($_GET['edit_dest'])) {
    $edit_dest_id = intval($_GET['edit_dest']);
    $res = $conn->query("SELECT * FROM destinations WHERE destination_id = $edit_dest_id");
    if ($res && $res->num_rows > 0) {
        $dest_to_edit = $res->fetch_assoc();
        $edit_dest_mode = true;
    }
}

if (isset($_GET['edit_service'])) {
    $edit_service_id = intval($_GET['edit_service']);
    $res = $conn->query("SELECT * FROM services WHERE service_id = $edit_service_id");
    if ($res && $res->num_rows > 0) {
        $service_to_edit = $res->fetch_assoc();
        $edit_service_mode = true;
    }
}

// ========================================================
// GET LOGIC: HIDE, SHOW, AND permanent permanent DELETES
// ========================================================

// Destination Actions
if (isset($_GET['action']) && isset($_GET['dest_id'])) {
    $dest_id = intval($_GET['dest_id']);
    $action = $_GET['action'];

    if ($action === 'hide') {
        $conn->query("UPDATE destinations SET is_active = 0 WHERE destination_id = $dest_id");
        header("Location: admin_build_package.php?msg=dest_hidden");
        exit();
    } elseif ($action === 'show') {
        $conn->query("UPDATE destinations SET is_active = 1 WHERE destination_id = $dest_id");
        header("Location: admin_build_package.php?msg=dest_shown");
        exit();
    } elseif ($action === 'delete') {
        if ($conn->query("DELETE FROM destinations WHERE destination_id = $dest_id")) {
            header("Location: admin_build_package.php?msg=dest_deleted");
        } else {
            header("Location: admin_build_package.php?error=dest_failed");
        }
        exit();
    }
}

// Service Component Actions
if (isset($_GET['action']) && isset($_GET['service_id'])) {
    $service_id = intval($_GET['service_id']);
    $action = $_GET['action'];

    if ($action === 'hide') {
        $conn->query("UPDATE services SET is_active = 0 WHERE service_id = $service_id");
        header("Location: admin_build_package.php?msg=service_hidden");
        exit();
    } elseif ($action === 'show') {
        $conn->query("UPDATE services SET is_active = 1 WHERE service_id = $service_id");
        header("Location: admin_build_package.php?msg=service_shown");
        exit();
    } elseif ($action === 'delete') {
        if ($conn->query("DELETE FROM services WHERE service_id = $service_id")) {
            header("Location: admin_build_package.php?msg=service_deleted");
        } else {
            header("Location: admin_build_package.php?error=service_failed");
        }
        exit();
    }
}

// Handle GET Processing Messages
if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'dest_hidden') $dest_msg = "✅ Destination taken offline.";
    if ($_GET['msg'] == 'dest_shown') $dest_msg = "✅ Destination is now live for users.";
    if ($_GET['msg'] == 'dest_deleted') $dest_msg = "🗑️ Destination deleted permanently.";
    if ($_GET['msg'] == 'dest_updated') $dest_msg = "📝 Destination changes saved successfully!";
    if ($_GET['msg'] == 'service_hidden') $service_msg = "✅ Service hidden from builder choices.";
    if ($_GET['msg'] == 'service_shown') $service_msg = "✅ Service element made live.";
    if ($_GET['msg'] == 'service_deleted') $service_msg = "🗑️ Service choice deleted permanently.";
    if ($_GET['msg'] == 'service_updated') $service_msg = "📝 Service rates updated successfully!";
}
if (isset($_GET['error'])) {
    if ($_GET['error'] == 'dest_failed') $dest_msg = "❌ Cannot delete destination! It is linked to active packages.";
    if ($_GET['error'] == 'service_failed') $service_msg = "❌ Cannot delete service item! It is linked to active bookings.";
}

// ========================================================
// POST LOGIC: INSERT OR UPDATE SMART ROUTER SWITCHES
// ========================================================

// Save Destination Form Handler
if (isset($_POST['save_destination'])) {
    $name = mysqli_real_escape_string($conn, $_POST['destination_name']);
    $target_id = intval($_POST['edit_id'] ?? 0);

    // Process image file pointer shifts
    $image = $_FILES['destination_image']['name'];
    if (!empty($image)) {
        $target = "assets/img/" . basename($image);
        move_uploaded_file($_FILES['destination_image']['tmp_name'], $target);
        $img_file = $image;
    } else {
        $img_file = !empty($_POST['old_image']) ? mysqli_real_escape_string($conn, $_POST['old_image']) : 'default_dest.jpg';
    }

    if ($target_id > 0) {
        // Run update query if in edit mode
        $sql = "UPDATE destinations SET destination_name = '$name', image_url = '$img_file' WHERE destination_id = $target_id";
        if ($conn->query($sql)) {
            header("Location: admin_build_package.php?msg=dest_updated");
            exit();
        }
    } else {
        // Run insert query if in standard mode
        $sql = "INSERT INTO destinations (destination_name, image_url, is_active) VALUES ('$name', '$img_file', 1)";
        if ($conn->query($sql)) {
            header("Location: admin_build_package.php?msg=dest_shown");
            exit();
        }
    }
    $dest_msg = "❌ Execution mismatch saving configuration context.";
}

// Save Service Component Form Handler
if (isset($_POST['save_service'])) {
    $dest_id = intval($_POST['destination_id']);
    $name = mysqli_real_escape_string($conn, $_POST['service_name']);
    $type = mysqli_real_escape_string($conn, $_POST['service_type']);
    $price = floatval($_POST['price']);
    $target_id = intval($_POST['edit_id'] ?? 0);

    if ($target_id > 0) {
        // Execute update loop logic
        $sql = "UPDATE services SET destination_id = $dest_id, service_name = '$name', service_type = '$type', price = $price WHERE service_id = $target_id";
        if ($conn->query($sql)) {
            header("Location: admin_build_package.php?msg=service_updated");
            exit();
        }
    } else {
        // Execute basic catalog matrix insert
        $sql = "INSERT INTO services (destination_id, service_name, service_type, price, is_active) VALUES ($dest_id, '$name', '$type', $price, 1)";
        if ($conn->query($sql)) {
            header("Location: admin_build_package.php?msg=service_shown");
            exit();
        }
    }
    $service_msg = "❌ Execution error logging service data.";
}

// Fetch lists to load fields matrices
$destinations_dropdown = $conn->query("SELECT * FROM destinations WHERE is_active = 1 ORDER BY destination_name ASC");
$destinations_list = $conn->query("SELECT * FROM destinations ORDER BY destination_name ASC");
$services_list = $conn->query("SELECT s.*, d.destination_name FROM services s JOIN destinations d ON s.destination_id = d.destination_id ORDER BY s.service_id DESC");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Builder Configuration | Wegha Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f4f7f6;
            margin: 0;
            display: flex;
        }

        /* --- COMPLETE SIDEBAR OVERRIDE BLOCK --- */
        .sidebar {
            width: 250px !important;
            background: #1e5494 !important;
            color: white !important;
            height: 100vh !important;
            padding: 20px !important;
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            box-sizing: border-box !important;
            z-index: 9999 !important;
        }

        .sidebar h2 {
            text-align: center !important;
            color: #f37021 !important;
            margin-bottom: 30px !important;
            margin-top: 10px !important;
        }

        .sidebar a {
            display: block !important;
            color: white !important;
            text-decoration: none !important;
            padding: 12px !important;
            margin-bottom: 5px !important;
            border-radius: 5px !important;
            font-size: 0.9rem !important;
            transition: 0.2s ease !important;
        }

        .sidebar a:hover {
            background: rgba(255, 255, 255, 0.1) !important;
        }

        .sidebar a[href="admin_add_builder_options.php"],
        .sidebar a[href="admin_build_package.php"] {
            background: rgba(255, 255, 255, 0.2) !important;
            border-left: 4px solid #f37021 !important;
            padding-left: 8px !important;
        }

        .sidebar .logout {
            margin-top: 30px !important;
            color: #ff6b6b !important;
            border-top: 1px solid rgba(255, 255, 255, 0.1) !important;
            padding-top: 20px !important;
        }

        /* --- MAIN CONTENT CONTAINER --- */
        .main-content {
            margin-left: 270px;
            padding: 40px;
            width: calc(100% - 270px);
            box-sizing: border-box;
        }

        h1 {
            color: #1e5494;
            margin-bottom: 30px;
        }

        .forms-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
            gap: 30px;
            align-items: start;
        }

        .form-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.04);
            border-top: 4px solid #1e5494;
            transition: 0.3s ease;
        }

        /* Edit Mode Highlights Form Background color */
        .form-card.edit-active {
            border-top-color: #f37021;
            box-shadow: 0 4px 20px rgba(243, 112, 33, 0.1);
        }

        .form-card h2 {
            color: #f37021;
            margin-top: 0;
            font-size: 1.25rem;
            border-bottom: 2px solid #f4f7f6;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .input-group {
            margin-bottom: 15px;
        }

        .input-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: #4a5568;
            font-size: 0.9rem;
        }

        .input-group input,
        .input-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-sizing: border-box;
            font-size: 0.95rem;
            outline: none;
        }

        .input-group input:focus,
        .input-group select:focus {
            border-color: #1e5494;
        }

        .btn-flex-container {
            display: flex;
            gap: 10px;
            margin-bottom: 25px;
        }

        .btn-submit {
            background: #1e5494;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            flex-grow: 1;
            font-size: 1rem;
            transition: 0.2s;
        }

        .btn-submit:hover {
            background: #163f72;
        }

        .btn-submit.btn-save-edit {
            background: #f37021;
        }

        .btn-submit.btn-save-edit:hover {
            background: #d65d14;
        }

        .btn-cancel {
            background: #e2e8f0;
            color: #4a5568;
            text-decoration: none;
            padding: 12px 20px;
            border-radius: 8px;
            font-weight: bold;
            font-size: 0.9rem;
            text-align: center;
            transition: 0.2s;
        }

        .btn-cancel:hover {
            background: #cbd5e1;
        }

        /* --- GLIMPSE OVERVIEW LIST SCROLL INTERFACE --- */
        .table-container {
            max-height: 280px;
            overflow-y: auto;
            border: 1px solid #eee;
            border-radius: 8px;
            margin-top: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            font-size: 0.85rem;
        }

        th,
        td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background: #f8f9fa;
            color: #1e5494;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .thumb-mini {
            width: 35px;
            height: 35px;
            border-radius: 4px;
            object-fit: cover;
        }

        .action-link {
            text-decoration: none;
            font-weight: bold;
            margin-right: 8px;
            font-size: 0.8rem;
        }

        .link-edit {
            color: #1e5494;
        }

        .link-hide {
            color: #e67e22;
        }

        .link-show {
            color: #2ecc71;
        }

        .link-delete {
            color: #e74c3c;
        }

        .muted-row {
            opacity: 0.5;
            background: #fafafa;
        }

        .hidden-badge {
            background: #ffeeba;
            color: #856404;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 0.7rem;
            font-weight: bold;
        }

        .alert {
            padding: 12px;
            background: #e6fffa;
            color: #2c7a7b;
            border: 1px solid #b2f5ea;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .alert-error {
            background: #fff5f5;
            color: #c53030;
            border-color: #feb2b2;
        }
    </style>
</head>

<body>

    <?php include 'admin_sidebar.php'; ?>

    <div class="main-content">
        <h1><i class="fas fa-tools"></i> Custom Package Configuration</h1>

        <div class="forms-grid">

            <div class="form-card <?php echo $edit_dest_mode ? 'edit-active' : ''; ?>">
                <h2>
                    <?php if ($edit_dest_mode): ?>
                        <i class="fas fa-edit"></i> Edit Destination Context: #<?php echo $edit_dest_id; ?>
                    <?php else: ?>
                        <i class="fas fa-map-marked-alt"></i> Step 1: Destination Cities
                    <?php endif; ?>
                </h2>

                <?php if (!empty($dest_msg)): ?>
                    <div class="alert <?php echo (strpos($dest_msg, '❌') !== false) ? 'alert-error' : ''; ?>"><?php echo $dest_msg; ?></div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="edit_id" value="<?php echo $edit_dest_id; ?>">
                    <input type="hidden" name="old_image" value="<?php echo htmlspecialchars($dest_to_edit['image_url']); ?>">

                    <div class="input-group">
                        <label>Destination Name</label>
                        <input type="text" name="destination_name" placeholder="e.g., Aswan, Siwa" value="<?php echo htmlspecialchars($dest_to_edit['destination_name']); ?>" required>
                    </div>
                    <div class="input-group">
                        <label>Location Cover Photo <?php echo $edit_dest_mode ? '<small style="color:#888;">(Leave blank to keep current)</small>' : ''; ?></label>
                        <input type="file" name="destination_image" accept="image/*">
                    </div>

                    <div class="btn-flex-container">
                        <button type="submit" name="save_destination" class="btn-submit <?php echo $edit_dest_mode ? 'btn-save-edit' : ''; ?>">
                            <?php echo $edit_dest_mode ? 'Save Changes' : 'Create Location Context'; ?>
                        </button>
                        <?php if ($edit_dest_mode): ?>
                            <a href="admin_build_package.php" class="btn-cancel">Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>

                <h3>Current Saved Destinations</h3>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Photo</th>
                                <th>City Name</th>
                                <th>Controls</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($d = $destinations_list->fetch_assoc()):
                                $is_hidden = ($d['is_active'] == 0);
                            ?>
                                <tr class="<?php echo $is_hidden ? 'muted-row' : ''; ?>">
                                    <td><img src="assets/img/<?php echo $d['image_url']; ?>" class="thumb-mini"></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($d['destination_name']); ?></strong>
                                        <?php if ($is_hidden): ?> <span class="hidden-badge">Hidden</span> <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="admin_build_package.php?edit_dest=<?php echo $d['destination_id']; ?>" class="action-link link-edit">Edit</a>
                                        <?php if ($is_hidden): ?>
                                            <a href="admin_build_package.php?action=show&dest_id=<?php echo $d['destination_id']; ?>" class="action-link link-show">Show</a>
                                        <?php else: ?>
                                            <a href="admin_build_package.php?action=hide&dest_id=<?php echo $d['destination_id']; ?>" class="action-link link-hide">Hide</a>
                                        <?php endif; ?>
                                        <a href="admin_build_package.php?action=delete&dest_id=<?php echo $d['destination_id']; ?>" class="action-link link-delete" onclick="return confirm('Permanently remove this destination city? This will clear all data dependencies.')">Delete</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="form-card <?php echo $edit_service_mode ? 'edit-active' : ''; ?>">
                <h2>
                    <?php if ($edit_service_mode): ?>
                        <i class="fas fa-edit"></i> Edit Service Option: #<?php echo $edit_service_id; ?>
                    <?php else: ?>
                        <i class="fas fa-hotel"></i> Step 2: Service Components
                    <?php endif; ?>
                </h2>

                <?php if (!empty($service_msg)): ?>
                    <div class="alert <?php echo (strpos($service_msg, '❌') !== false) ? 'alert-error' : ''; ?>"><?php echo $service_msg; ?></div>
                <?php endif; ?>

                <form method="POST">
                    <input type="hidden" name="edit_id" value="<?php echo $edit_service_id; ?>">

                    <div class="input-group">
                        <label>Link to Location Context</label>
                        <select name="destination_id" required>
                            <option value="">-- Select Destination --</option>
                            <?php
                            $destinations_dropdown->data_seek(0);
                            while ($row = $destinations_dropdown->fetch_assoc()):
                            ?>
                                <option value="<?php echo $row['destination_id']; ?>" <?php echo ($row['destination_id'] == $service_to_edit['destination_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($row['destination_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="input-group">
                        <label>Service Allocation Type</label>
                        <select name="service_type" required>
                            <option value="Accommodation" <?php echo ($service_to_edit['service_type'] === 'Accommodation') ? 'selected' : ''; ?>>Accommodation (Hotels)</option>
                            <option value="Activity" <?php echo ($service_to_edit['service_type'] === 'Activity') ? 'selected' : ''; ?>>Activity (Excursions)</option>
                            <option value="Transport" <?php echo ($service_to_edit['service_type'] === 'Transport') ? 'selected' : ''; ?>>Transportation (Logistics)</option>
                        </select>
                    </div>

                    <div class="input-group">
                        <label>Option Title Description</label>
                        <input type="text" name="service_name" placeholder="e.g., Movenpick Resort Room" value="<?php echo htmlspecialchars($service_to_edit['service_name']); ?>" required>
                    </div>

                    <div class="input-group">
                        <label>Price Rate Scale (EGP)</label>
                        <input type="number" step="0.01" name="price" placeholder="e.g., 2400" value="<?php echo $service_to_edit['price']; ?>" required>
                    </div>

                    <div class="btn-flex-container">
                        <button type="submit" name="save_service" class="btn-submit <?php echo $edit_service_mode ? 'btn-save-edit' : ''; ?>">
                            <?php echo $edit_service_mode ? 'Save Changes' : 'Add Component Choice'; ?>
                        </button>
                        <?php if ($edit_service_mode): ?>
                            <a href="admin_build_package.php" class="btn-cancel">Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>

                <h3>Active Builder Catalog Pool</h3>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Context</th>
                                <th>Option Name</th>
                                <th>Price</th>
                                <th>Controls</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($s = $services_list->fetch_assoc()):
                                $is_hidden = ($s['is_active'] == 0);
                            ?>
                                <tr class="<?php echo $is_hidden ? 'muted-row' : ''; ?>">
                                    <td><small style="color:#777; font-weight:600;"><?php echo htmlspecialchars($s['destination_name']); ?></small></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($s['service_name']); ?></strong><br>
                                        <small style="color:#999;"><?php echo $s['service_type']; ?></small>
                                        <?php if ($is_hidden): ?> <span class="hidden-badge">Hidden</span> <?php endif; ?>
                                    </td>
                                    <td style="font-weight:bold; color:#1e5494;"><?php echo number_format($s['price']); ?> EGP</td>
                                    <td>
                                        <a href="admin_build_package.php?edit_service=<?php echo $s['service_id']; ?>" class="action-link link-edit">Edit</a>
                                        <?php if ($is_hidden): ?>
                                            <a href="admin_build_package.php?action=show&service_id=<?php echo $s['service_id']; ?>" class="action-link link-show">Show</a>
                                        <?php else: ?>
                                            <a href="admin_build_package.php?action=hide&service_id=<?php echo $s['service_id']; ?>" class="action-link link-hide">Hide</a>
                                        <?php endif; ?>
                                        <a href="admin_build_package.php?action=delete&service_id=<?php echo $s['service_id']; ?>" class="action-link link-delete" onclick="return confirm('Permanently remove this choice option?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

</body>

</html>