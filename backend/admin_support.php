<?php
session_start();
include 'wegha_db.php'; //

// Admin Authentication Shield
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// HANDLE UPDATE: Status & Admin Notes
if (isset($_POST['update_ticket'])) {
    $tid = intval($_POST['ticket_id']);
    $new_status = mysqli_real_escape_string($conn, $_POST['new_status']);
    $notes = mysqli_real_escape_string($conn, $_POST['admin_notes']);

    // Update the ENUM status and the private admin notes
    $conn->query("UPDATE contact_messages SET status = '$new_status', admin_notes = '$notes' WHERE id = $tid");
    header("Location: admin_support.php?msg=updated");
    exit();
}

// Fetch messages ordered by priority (Pending first)
$result = $conn->query("SELECT * FROM contact_messages ORDER BY FIELD(status, 'Pending', 'In Progress', 'Done'), id DESC");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Support Tickets | Wegha Admin</title>
    <style>
        /* --- STABLE SIDEBAR & LAYOUT CSS --- */
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            display: flex;
            background: #f4f7f6;
        }

        .sidebar {
            width: 250px;
            background: #1e5494;
            color: white;
            height: 100vh;
            padding: 20px;
            position: fixed;
            /* Locked in place */
            top: 0;
            left: 0;
            z-index: 1000;
        }

        .sidebar h2 {
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            padding-bottom: 20px;
        }

        .sidebar a {
            display: block;
            color: white;
            text-decoration: none;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 5px;
            transition: 0.3s;
        }

        .sidebar a:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .main-content {
            margin-left: 270px;
            /* Space for the 250px sidebar + 20px padding */
            padding: 40px;
            width: calc(100% - 270px);
            box-sizing: border-box;
        }

        /* --- TABLE & UI STYLES --- */
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        th,
        td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
            vertical-align: top;
        }

        th {
            background: #f8f9fa;
            color: #1e5494;
        }

        .badge {
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.75rem;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-Pending {
            background: #fff5f5;
            color: #c53030;
        }

        .status-In-Progress {
            background: #fffbe6;
            color: #d48806;
        }

        .status-Done {
            background: #f0fff4;
            color: #2f855a;
        }

        select,
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
            margin-bottom: 10px;
            font-family: inherit;
        }

        .btn-update {
            background: #1e5494;
            color: white;
            border: none;
            padding: 10px;
            width: 100%;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
        }
    </style>
</head>

<body>

    <?php include 'admin_sidebar.php'; ?>

    <div class="main-content">
        <h1>Customer Support Tickets</h1>

        <table>
            <thead>
                <tr>
                    <th>Customer Info</th>
                    <th>Message</th>
                    <th>Update Status & Notes</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($row['name']); ?></strong><br>
                            <small>📧 <?php echo htmlspecialchars($row['email']); ?></small><br>
                            <small>📞 <?php echo htmlspecialchars($row['phone']); ?></small><br><br> <span class="badge status-<?php echo str_replace(' ', '-', $row['status']); ?>">
                                <?php echo $row['status']; ?>
                            </span>
                        </td>
                        <td style="max-width: 300px;">
                            <p style="color: #555;">"<?php echo nl2br(htmlspecialchars($row['message'])); ?>"</p>
                        </td>
                        <td>
                            <form method="POST">
                                <input type="hidden" name="ticket_id" value="<?php echo $row['id']; ?>">

                                <label style="font-size: 0.8rem; font-weight: bold; color: #888;">Set Status:</label>
                                <select name="new_status">
                                    <option value="Pending" <?php if ($row['status'] == 'Pending') echo 'selected'; ?>>🔴 Pending</option>
                                    <option value="In Progress" <?php if ($row['status'] == 'In Progress') echo 'selected'; ?>>🟡 In Progress</option>
                                    <option value="Done" <?php if ($row['status'] == 'Done') echo 'selected'; ?>>🟢 Done</option>
                                </select>

                                <label style="font-size: 0.8rem; font-weight: bold; color: #888;">Admin Notes:</label>
                                <textarea name="admin_notes" rows="2" placeholder="Private notes..."><?php echo htmlspecialchars($row['admin_notes']); ?></textarea>

                                <button type="submit" name="update_ticket" class="btn-update">Save Changes</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>

</html>