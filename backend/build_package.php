<?php
session_start();
include 'navbar.php';
include 'wegha_db.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// ========================================================
// POST LOGIC: HANDLE CUSTOM PACKAGE COMPONENT DATA PROCESSING
// ========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_custom_package'])) {
    $total_price = mysqli_real_escape_string($conn, $_POST['calculated_total']);
    $selected_items = json_decode($_POST['selected_services_json'], true);

    if (!empty($selected_items) && $total_price > 0) {
        // 1. Insert Master Record
        $insert_package = "INSERT INTO customer_packages (user_id, total_price) VALUES ('$user_id', '$total_price')";
        if (mysqli_query($conn, $insert_package)) {
            $customer_package_id = mysqli_insert_id($conn);

            // 2. Insert Selected Services into Junction Table (FIXED: Added service_date mapping)
            $stmt = mysqli_prepare($conn, "INSERT INTO customer_package_services (customer_package_id, service_id, quantity, service_date) VALUES (?, ?, ?, ?)");

            foreach ($selected_items as $item) {
                // Ensure date parameter maps empty queries cleanly to a string or fallback NULL
                $service_date = !empty($item['date']) ? $item['date'] : null;

                mysqli_stmt_bind_param($stmt, "iiis", $customer_package_id, $item['id'], $item['qty'], $service_date);
                mysqli_stmt_execute($stmt);
            }
            mysqli_stmt_close($stmt);

            // 3. Store in session for payment processing and redirect
            $_SESSION['active_custom_package'] = $customer_package_id;
            header("Location: payment.php?type=custom&id=" . $customer_package_id);
            exit();
        }
    }
}

// --- FETCH DATA FOR BUILDER BLOCKS ---
$destinations_res = $conn->query("SELECT * FROM destinations WHERE is_active = 1");
$services_res = $conn->query("SELECT s.*, d.destination_name FROM services s JOIN destinations d ON s.destination_id = d.destination_id WHERE s.is_active = 1");

// Group services for quick parsing in HTML
$services_list = [];
while ($row = $services_res->fetch_assoc()) {
    $services_list[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Build Your Adventure | Wegha</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&family=Poppins:wght@300;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --main-copper: #c8a97e;
            --deep-blue: #1e5494;
            --soft-bg: #fdfaf7;
            --text: #4a4a4a;
        }

        body {
            font-family: 'Poppins', 'Cairo', sans-serif;
            background: #f5f1eb;
            margin: 0;
            color: var(--text);
        }

        .builder-container {
            width: 90%;
            max-width: 1200px;
            margin: 40px auto;
            display: grid;
            grid-template-columns: 1fr 350px;
            gap: 30px;
        }

        .step-section {
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
            margin-bottom: 25px;
        }

        .step-section h2 {
            margin-top: 0;
            color: var(--deep-blue);
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.4rem;
            border-bottom: 2px solid #fcf9f5;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        /* Options Card Layout grids */
        .options-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(230px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }

        .option-card {
            background: var(--soft-bg);
            border: 2px solid #f0e6d8;
            border-radius: 15px;
            padding: 15px;
            text-align: center;
            transition: 0.3s;
            position: relative;
            cursor: pointer;
        }

        .option-card:hover {
            border-color: var(--main-copper);
            transform: translateY(-2px);
        }

        .option-card.selected {
            border-color: var(--deep-blue);
            background: #eef5fc;
        }

        .option-card img {
            width: 100%;
            height: 120px;
            border-radius: 10px;
            object-fit: cover;
            margin-bottom: 10px;
        }

        .option-card h4 {
            margin: 5px 0;
            font-size: 1rem;
        }

        .price-lbl {
            color: var(--main-copper);
            font-weight: bold;
            font-size: 0.9rem;
            display: block;
            margin-bottom: 10px;
        }

        /* Dynamic Section Lock overlays */
        .locked-section {
            opacity: 0.4;
            pointer-events: none;
            transition: 0.3s;
        }

        /* Selector Controls */
        .select-btn {
            background: var(--main-copper);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            font-size: 0.85rem;
            width: 100%;
            transition: 0.2s;
        }

        .option-card.selected .select-btn {
            background: var(--deep-blue);
        }

        .qty-spinner {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 12px;
            border-top: 1px dashed #ddd;
            padding-top: 10px;
        }

        .qty-btn {
            background: #ddd;
            border: none;
            width: 25px;
            height: 25px;
            border-radius: 50%;
            font-weight: bold;
            cursor: pointer;
        }

        /* --- NEW SERVICE COMPONENT DATE PICKER STYLES --- */
        .date-picker-wrap {
            margin-top: 12px;
            text-align: left;
        }

        .date-picker-wrap label {
            font-size: 0.75rem;
            font-weight: bold;
            color: #555;
            display: block;
            margin-bottom: 4px;
        }

        .date-picker-wrap input[type="date"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 6px;
            box-sizing: border-box;
            font-family: inherit;
            font-size: 0.85rem;
            outline: none;
        }

        .date-picker-wrap input[type="date"]:focus {
            border-color: var(--deep-blue);
        }

        /* Sticky Summary Panel Card */
        .summary-panel {
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 100px;
            height: fit-content;
        }

        .summary-panel h3 {
            margin-top: 0;
            color: var(--deep-blue);
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }

        .summary-list {
            list-style: none;
            padding: 0;
            margin: 20px 0;
            font-size: 0.9rem;
        }

        .summary-list li {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 1px dashed #eee;
        }

        .total-box {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 1.3rem;
            font-weight: bold;
            color: var(--deep-blue);
            margin-top: 20px;
            border-top: 2px solid #eee;
            padding-top: 15px;
        }

        .book-now-btn {
            background: #2ecc71;
            color: white;
            border: none;
            padding: 15px;
            width: 100%;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            margin-top: 20px;
            transition: 0.2s;
        }

        .book-now-btn:hover {
            background: #27ae60;
        }

        .book-now-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
    </style>
</head>

<body>

    <div class="builder-container">

        <div class="builder-steps">

            <!-- STEP 1: DESTINATION SELECTION -->
            <div class="step-section" id="section-destinations">
                <h2><span>1️⃣</span> Select Destinations</h2>
                <p style="font-size: 0.85rem; color: #888;">Add one or more cities to unlock activities and custom stays matching your route.</p>
                <div class="options-grid">
                    <?php while ($dest = $destinations_res->fetch_assoc()): ?>
                        <div class="option-card destination-card" data-id="<?php echo $dest['destination_id']; ?>" data-name="<?php echo htmlspecialchars($dest['destination_name']); ?>" onclick="toggleDestination(this)">
                            <img src="assets/img/<?php echo $dest['image_url'] ?? 'default_dest.jpg'; ?>" alt="">
                            <h4><?php echo htmlspecialchars($dest['destination_name']); ?></h4>
                            <button class="select-btn">Add to Trip</button>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>

            <!-- STEP 2: STAYS & ACTIVITIES -->
            <div class="step-section locked-section" id="section-services">
                <h2><span>2️⃣</span> Select Stays & Activities</h2>
                <div class="options-grid">
                    <?php foreach ($services_list as $srv):
                        if ($srv['service_type'] == 'Transport') continue; ?>
                        <div class="option-card service-card"
                            data-id="<?php echo $srv['service_id']; ?>"
                            data-dest="<?php echo $srv['destination_id']; ?>"
                            data-name="<?php echo htmlspecialchars($srv['service_name']); ?>"
                            data-price="<?php echo $srv['price']; ?>"
                            style="display: none;">
                            <h4><?php echo htmlspecialchars($srv['service_name']); ?></h4>
                            <small style="color:#888; display:block; margin-bottom:5px;"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($srv['destination_name']); ?></small>
                            <span class="price-lbl"><?php echo number_format($srv['price']); ?> EGP</span>

                            <button class="select-btn" onclick="toggleService(this, event)">Select Item</button>

                            <!-- Inline Quantity Selector Control -->
                            <div class="qty-spinner" style="display:none;">
                                <button class="qty-btn" onclick="adjustQty(this, -1, event)">-</button>
                                <span class="qty-val">1</span>
                                <button class="qty-btn" onclick="adjustQty(this, 1, event)">+</button>
                            </div>

                            <!-- NEW: Inline Date Picker Entry Wrapper Component -->
                            <div class="date-picker-wrap" style="display:none;">
                                <label><i class="far fa-calendar-alt"></i> Allocation Date:</label>
                                <input type="date" class="service-date-input" min="<?php echo date('Y-m-d'); ?>" onchange="updateServiceDate(this, event)" onclick="event.stopPropagation();">
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- STEP 3: TRANSIT LOGISTICS ARRANGEMENTS -->
            <div class="step-section locked-section" id="section-transport">
                <h2><span>3️⃣</span> Arrange Transport</h2>
                <div class="options-grid">
                    <?php foreach ($services_list as $srv):
                        if ($srv['service_type'] != 'Transport') continue; ?>
                        <div class="option-card service-card"
                            data-id="<?php echo $srv['service_id']; ?>"
                            data-dest="<?php echo $srv['destination_id']; ?>"
                            data-name="<?php echo htmlspecialchars($srv['service_name']); ?>"
                            data-price="<?php echo $srv['price']; ?>"
                            style="display: none;">
                            <div style="font-size: 1.5rem; margin-bottom:10px; color:var(--deep-blue);"><i class="fas fa-bus-alt"></i></div>
                            <h4><?php echo htmlspecialchars($srv['service_name']); ?></h4>
                            <small style="color:#888; display:block; margin-bottom:5px;"><i class="fas fa-route"></i> To <?php echo htmlspecialchars($srv['destination_name']); ?></small>
                            <span class="price-lbl"><?php echo number_format($srv['price']); ?> EGP</span>

                            <button class="select-btn" onclick="toggleService(this, event)">Add Ticket</button>

                            <!-- NEW: Transit Allocation Date Entry Wrapper Component -->
                            <div class="date-picker-wrap" style="display:none;">
                                <label><i class="far fa-calendar-alt"></i> Departure Date:</label>
                                <input type="date" class="service-date-input" min="<?php echo date('Y-m-d'); ?>" onchange="updateServiceDate(this, event)" onclick="event.stopPropagation();">
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>

        <!-- SIDEBAR CHECKOUT SYNC HUD PANEL -->
        <aside class="summary-sidebar">
            <div class="summary-panel">
                <h3>Custom Itinerary</h3>
                <div style="font-size:0.8rem; color:#888; margin-bottom:15px;">Live Breakdown</div>

                <ul class="summary-list" id="summary-items">
                    <li style="color:#ccc; border:none; text-align:center; display:block;">No choices selected yet</li>
                </ul>

                <div class="total-box">
                    <span>Total:</span>
                    <div><span id="live-total">0</span> <span style="font-size:0.9rem;">EGP</span></div>
                </div>

                <form method="POST" id="custom-package-form">
                    <input type="hidden" name="calculated_total" id="form-total" value="0">
                    <input type="hidden" name="selected_services_json" id="form-json" value="[]">
                    <button type="submit" name="submit_custom_package" id="submit-btn" class="book-now-btn" disabled>Confirm & Book</button>
                </form>
            </div>
        </aside>

    </div>

    <script>
        let activeDestinations = [];
        let selectedServices = {}; // Matrix: service_id -> { name, price, qty, date }

        function toggleDestination(card) {
            const destId = parseInt(card.dataset.id);
            card.classList.toggle('selected');
            const btn = card.querySelector('.select-btn');

            if (card.classList.contains('selected')) {
                activeDestinations.push(destId);
                btn.innerText = "Remove";
            } else {
                activeDestinations = activeDestinations.filter(id => id !== destId);
                btn.innerText = "Add to Trip";
                removeServicesByDestination(destId);
            }

            evaluateSteppers();
        }

        function evaluateSteppers() {
            const serviceSec = document.getElementById('section-services');
            const transSec = document.getElementById('section-transport');
            const allServiceCards = document.querySelectorAll('.service-card');

            if (activeDestinations.length > 0) {
                serviceSec.classList.remove('locked-section');
                transSec.classList.remove('locked-section');

                allServiceCards.forEach(card => {
                    const cardDest = parseInt(card.dataset.dest);
                    if (activeDestinations.includes(cardDest)) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            } else {
                serviceSec.classList.add('locked-section');
                transSec.classList.add('locked-section');
                allServiceCards.forEach(card => card.style.display = 'none');
            }
            updateUI();
        }

        function toggleService(btn, event) {
            event.stopPropagation();
            const card = btn.closest('.option-card');
            const srvId = card.dataset.id;
            const name = card.dataset.name;
            const price = parseFloat(card.dataset.price);
            const spinner = card.querySelector('.qty-spinner');
            const dateWrap = card.querySelector('.date-picker-wrap');
            const dateInput = card.querySelector('.service-date-input');

            card.classList.toggle('selected');

            if (card.classList.contains('selected')) {
                selectedServices[srvId] = {
                    name: name,
                    price: price,
                    qty: 1,
                    date: dateInput ? dateInput.value : "" // Fetch base date if typed preemptively
                };
                btn.innerText = "Remove";
                if (spinner) spinner.style.display = 'flex';
                if (dateWrap) dateWrap.style.display = 'block';
            } else {
                delete selectedServices[srvId];
                btn.innerText = card.classList.contains('transport-card') ? "Add Ticket" : "Select Item";
                if (spinner) {
                    spinner.style.display = 'none';
                    card.querySelector('.qty-val').innerText = "1";
                }
                if (dateWrap) {
                    dateWrap.style.display = 'none';
                    if (dateInput) dateInput.value = ""; // Clear values safely
                }
            }
            updateUI();
        }

        function adjustQty(btn, amount, event) {
            event.stopPropagation();
            const card = btn.closest('.option-card');
            const srvId = card.dataset.id;
            const qtyText = card.querySelector('.qty-val');

            if (selectedServices[srvId]) {
                let currentQty = selectedServices[srvId].qty;
                currentQty += amount;
                if (currentQty < 1) currentQty = 1;

                selectedServices[srvId].qty = currentQty;
                qtyText.innerText = currentQty;
                updateUI();
            }
        }

        // NEW: Real-time Date Listener Sync Tool
        function updateServiceDate(input, event) {
            const card = input.closest('.option-card');
            const srvId = card.dataset.id;

            if (selectedServices[srvId]) {
                selectedServices[srvId].date = input.value;
                updateUI();
            }
        }

        function removeServicesByDestination(destId) {
            const serviceCards = document.querySelectorAll(`.service-card[data-dest="${destId}"]`);
            serviceCards.forEach(card => {
                const srvId = card.dataset.id;
                if (selectedServices[srvId]) {
                    delete selectedServices[srvId];
                    card.classList.remove('selected');
                    const btn = card.querySelector('.select-btn');
                    if (btn) btn.innerText = "Select Item";

                    const spinner = card.querySelector('.qty-spinner');
                    if (spinner) spinner.style.display = 'none';

                    const dateWrap = card.querySelector('.date-picker-wrap');
                    if (dateWrap) {
                        dateWrap.style.display = 'none';
                        card.querySelector('.service-date-input').value = "";
                    }
                }
            });
        }

        function updateUI() {
            const listContainer = document.getElementById('summary-items');
            const totalDisplay = document.getElementById('live-total');
            const formTotal = document.getElementById('form-total');
            const formJson = document.getElementById('form-json');
            const submitBtn = document.getElementById('submit-btn');

            listContainer.innerHTML = '';
            let grandTotal = 0;
            let itemsArray = [];
            let missingDates = false; // Guard flag to prevent checkout without date validations

            const serviceKeys = Object.keys(selectedServices);

            if (serviceKeys.length === 0) {
                listContainer.innerHTML = '<li style="color:#ccc; border:none; text-align:center; display:block;">No choices selected yet</li>';
                submitBtn.disabled = true;
                totalDisplay.innerText = "0";
                return;
            }

            serviceKeys.forEach(id => {
                const item = selectedServices[id];
                const itemCost = item.price * item.qty;
                grandTotal += itemCost;

                // FIXED: Included 'date' parameter fields inside serialized data arrays
                itemsArray.push({
                    id: parseInt(id),
                    qty: item.qty,
                    date: item.date
                });

                // Flag tracking evaluation loops if any field calendar is missing selection properties
                if (!item.date || item.date === "") {
                    missingDates = true;
                }

                const li = document.createElement('li');
                // Displays chosen individual dates inside the sidebar breakdown view
                const dateDisplay = item.date ? `<span style="color:#1e5494; font-weight:600;"><i class="far fa-calendar-check"></i> ${item.date}</span>` : `<span style="color:#e74c3c; font-weight:600;"><i class="fas fa-exclamation-circle"></i> Pick Date</span>`;

                li.innerHTML = `
                    <div>
                        <strong>${item.name}</strong>
                        <br><small style="color:#888;">Qty: ${item.qty} × ${item.price.toLocaleString()} EGP</small>
                        <br><small style="font-size: 0.75rem;">${dateDisplay}</small>
                    </div>
                    <span style="font-weight:500; align-self:center;">${itemCost.toLocaleString()} EGP</span>
                `;
                listContainer.appendChild(li);
            });

            totalDisplay.innerText = grandTotal.toLocaleString();
            formTotal.value = grandTotal;
            formJson.value = JSON.stringify(itemsArray);

            // Submitter is disabled if items are missing calendar values
            submitBtn.disabled = missingDates;
            if (missingDates) {
                submitBtn.innerText = "📅 Assign Dates to Book";
                submitBtn.style.background = "#e67e22";
            } else {
                submitBtn.innerText = "Confirm & Book";
                submitBtn.style.background = "#2ecc71";
            }
        }
    </script>

</body>

</html>