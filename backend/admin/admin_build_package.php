<?php
session_start();
include '../wegha_db.php';

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
// GET LOGIC: HIDE, SHOW, AND PERMANENT DELETES
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
        $target = "../assets/img/" . basename($image);
        move_uploaded_file($_FILES['destination_image']['tmp_name'], $target);
        $img_file = $image;
    } else {
        $img_file = !empty($_POST['old_image']) ? mysqli_real_escape_string($conn, $_POST['old_image']) : 'default_dest.jpg';
    }

    if ($target_id > 0) {
        $sql = "UPDATE destinations SET destination_name = '$name', image_url = '$img_file' WHERE destination_id = $target_id";
        if ($conn->query($sql)) {
            header("Location: admin_build_package.php?msg=dest_updated");
            exit();
        }
    } else {
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
        $sql = "UPDATE services SET destination_id = $dest_id, service_name = '$name', service_type = '$type', price = $price WHERE service_id = $target_id";
        if ($conn->query($sql)) {
            header("Location: admin_build_package.php?msg=service_updated");
            exit();
        }
    } else {
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Builder Configuration | Wegha Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* --- WEGHA ADMIN CENTRALIZED STYLE MATRIX --- */
        :root {
            --primary-blue: #1e5494;
            --accent-orange: #f37021;
            --bg-slate: #f8fafc;
            --text-main: #1e293b;
            --text-muted: #64748b;
        }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: var(--bg-slate);
            color: var(--text-main);
            margin: 0;
            display: flex;
        }

        /* --- UNIFIED SIDEBAR NAVIGATION --- */
        .sidebar {
            width: 260px;
            background: var(--primary-blue);
            color: white;
            height: 100vh;
            padding: 24px 16px;
            position: fixed;
            top: 0;
            left: 0;
            box-sizing: border-box;
            box-shadow: 4px 0 10px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            z-index: 999;
        }

        .sidebar h2 {
            text-align: center;
            color: var(--accent-orange);
            margin: 0 0 32px 0;
            font-size: 1.5rem;
            font-weight: 800;
            letter-spacing: 0.5px;
        }

        .sidebar a {
            display: flex;
            align-items: center;
            gap: 12px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            padding: 12px 16px;
            margin-bottom: 6px;
            border-radius: 10px;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .sidebar a i {
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
        }

        .sidebar a:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        /* Highlight rule for the active configuration route page link */
        .sidebar a[href="admin_build_package.php"] {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            border-left: 4px solid var(--accent-orange);
            font-weight: 600;
            padding-left: 12px;
        }

        .sidebar .logout {
            margin-top: auto;
            color: #f87171;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 16px;
        }

        .sidebar .logout:hover {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }

        /* --- MAIN CONTENT CONTAINER --- */
        .main-content {
            margin-left: 260px;
            padding: 40px;
            width: calc(100% - 260px);
            box-sizing: border-box;
            min-height: 100vh;
        }

        h1 {
            color: var(--primary-blue);
            margin: 0 0 8px 0;
            font-size: 2rem;
            font-weight: 700;
        }

        .subtitle {
            color: var(--text-muted);
            margin: 0 0 35px 0;
            font-size: 0.95rem;
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
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);
            border: 1px solid #edf2f7;
            border-top: 4px solid var(--primary-blue);
            transition: 0.3s ease;
        }

        .form-card.edit-active {
            border-top-color: var(--accent-orange);
            box-shadow: 0 4px 20px rgba(243, 112, 33, 0.08);
        }

        .form-card h2 {
            color: var(--primary-blue);
            margin-top: 0;
            font-size: 1.25rem;
            border-bottom: 2px solid #f4f7f6;
            padding-bottom: 10px;
            margin-bottom: 20px;
            font-weight: 700;
        }

        .form-card.edit-active h2 {
            color: var(--accent-orange);
        }

        .input-group {
            margin-bottom: 18px;
        }

        .input-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: 700;
            color: #475569;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .input-group input,
        .input-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            box-sizing: border-box;
            font-size: 0.95rem;
            outline: none;
            background-color: #fbfcfd;
            transition: all 0.2s ease;
        }

        .input-group input:focus,
        .input-group select:focus {
            border-color: var(--primary-blue);
            background-color: white;
            box-shadow: 0 0 0 3px rgba(30, 84, 148, 0.1);
        }

        .btn-flex-container {
            display: flex;
            gap: 12px;
            margin-top: 24px;
            margin-bottom: 25px;
        }

        .btn-submit {
            background: var(--primary-blue);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 10px;
            font-weight: 700;
            cursor: pointer;
            flex-grow: 1;
            font-size: 0.95rem;
            box-shadow: 0 4px 6px -1px rgba(30, 84, 148, 0.1);
            transition: all 0.2s;
        }

        .btn-submit:hover {
            background: #153d6b;
            transform: translateY(-1px);
        }

        .btn-submit.btn-save-edit {
            background: var(--accent-orange);
            box-shadow: 0 4px 6px -1px rgba(243, 112, 33, 0.1);
        }

        .btn-submit.btn-save-edit:hover {
            background: #d65d14;
        }

        .btn-cancel {
            background: #e2e8f0;
            color: #475569;
            text-decoration: none;
            padding: 12px 20px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 0.95rem;
            text-align: center;
            box-sizing: border-box;
            transition: all 0.2s;
        }

        .btn-cancel:hover {
            background: #cbd5e1;
        }

        /* --- TABLES MATRIX INTERFACES --- */
        .table-container {
            max-height: 320px;
            overflow-y: auto;
            border: 1px solid #edf2f7;
            border-radius: 12px;
            margin-top: 15px;
            box-shadow: inset 0 2px 4px 0 rgba(0, 0, 0, 0.01);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            font-size: 0.9rem;
        }

        th,
        td {
            padding: 12px 14px;
            text-align: left;
            border-bottom: 1px solid #edf2f7;
            vertical-align: middle;
        }

        th {
            background: #f8fafc;
            color: var(--primary-blue);
            position: sticky;
            top: 0;
            z-index: 10;
            font-weight: 700;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #edf2f7;
        }

        .thumb-mini {
            width: 42px;
            height: 42px;
            border-radius: 8px;
            object-fit: cover;
            border: 1px solid #edf2f7;
        }

        .action-link {
            text-decoration: none;
            font-weight: 700;
            margin-right: 10px;
            font-size: 0.8rem;
        }

        .link-edit {
            color: var(--primary-blue);
        }

        .link-hide {
            color: #e67e22;
        }

        .link-show {
            color: #10b981;
        }

        .link-delete {
            color: #ef4444;
        }

        .muted-row {
            opacity: 0.6;
            background: #fdfdfd;
        }

        .hidden-badge {
            background: #fef3c7;
            color: #d97706;
            padding: 2px 8px;
            border-radius: 50px;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            display: inline-block;
            margin-left: 5px;
        }

        .alert {
            padding: 14px;
            background: #e6fffa;
            color: #047857;
            border: 1px solid #b2f5ea;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .alert-error {
            background: #fff5f5;
            color: #b91c1c;
            border-color: #feb2b2;
        }
    </style>
</head>

<body>

    <div class="sidebar">
        <h2>Wegha Admin</h2>
        <a href="admin_analytics.php"><i class="fas fa-chart-pie"></i> Business Analytics</a>
        <a href="admin_dashboard.php"><i class="fas fa-th-large"></i> Dashboard Overview</a>
        <a href="admin_categories.php"><i class="fas fa-folder"></i> Manage Categories</a>
        <a href="admin_add_package.php"><i class="fas fa-plus-circle"></i> Add New Package</a>
        <a href="admin_build_package.php"><i class="fas fa-tools"></i> Setup Custom Options</a>
        <a href="admin_manage_packages.php"><i class="fas fa-boxes"></i> Manage Packages</a>
        <a href="admin_users.php"><i class="fas fa-users"></i> Registered Users</a>
        <a href="admin_bookings.php"><i class="fas fa-plane"></i> Manage Bookings</a>
        <a href="admin_support.php"><i class="fas fa-envelope"></i> Support Tickets</a>
        <a href="admin_reviews.php"><i class="fas fa-comments"></i> Manage Reviews</a>
        <a href="admin_logout.php" class="logout"><i class="fas fa-door-open"></i> Logout</a>
    </div>

    <div class="main-content">
        <h1>Custom Package Configuration</h1>
        <p class="subtitle">Administer choices pool elements for travelers designing customized itineraries:</p>

        <div class="forms-grid">

            <div class="form-card <?php echo $edit_dest_mode ? 'edit-active' : ''; ?>">
                <h2>
                    <?php if ($edit_dest_mode): ?>
                        <i class="fas fa-edit"></i> Edit Destination City: #<?php echo $edit_dest_id; ?>
                    <?php else: ?>
                        <i class="fas fa-map-marked-alt"></i> Step 1: Destination Cities
                    <?php endif; ?>
                </h2>

                <?php if (!empty($dest_msg)): ?>
                    <div class="alert <?php echo (strpos($dest_msg, '❌') !== false) ? 'alert-error' : ''; ?>"><?php echo $dest_msg; ?></div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data" action="">
                    <input type="hidden" name="edit_id" value="<?php echo $edit_dest_id; ?>">
                    <input type="hidden" name="old_image" value="<?php echo htmlspecialchars($dest_to_edit['image_url']); ?>">

                    <div class="input-group">
                        <label>Destination Name</label>
                        <input type="text" name="destination_name" placeholder="e.g., Aswan, Siwa" value="<?php echo htmlspecialchars($dest_to_edit['destination_name']); ?>" required>
                    </div>
                    <div class="input-group">
                        <label>Location Cover Photo <?php echo $edit_dest_mode ? '<small style="color:#64748b; text-transform:none;">(Leave blank to keep current)</small>' : ''; ?></label>
                        <input type="file" name="destination_image" accept="image/*" style="padding: 8px;">
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

                <h3 style="color: var(--primary-blue); font-size: 1.05rem; margin-top: 25px; font-weight: 700;">Current Saved Destinations</h3>
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
                                    <td><img src="../assets/img/<?php echo $d['image_url']; ?>" class="thumb-mini" alt="city"></td>
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

                <form method="POST" action="">
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

                <h3 style="color: var(--primary-blue); font-size: 1.05rem; margin-top: 25px; font-weight: 700;">Active Builder Catalog Pool</h3>
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
                                    <td><small style="color:#64748b; font-weight:600;"><?php echo htmlspecialchars($s['destination_name']); ?></small></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($s['service_name']); ?></strong><br>
                                        <small style="color:var(--text-muted); text-transform:uppercase; font-size:10px; font-weight:700;"><?php echo $s['service_type']; ?></small>
                                        <?php if ($is_hidden): ?> <span class="hidden-badge">Hidden</span> <?php endif; ?>
                                    </td>
                                    <td style="font-weight:700; color:var(--primary-blue);"><?php echo number_format($s['price']); ?> EGP</td>
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