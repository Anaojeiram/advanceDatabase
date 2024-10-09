<?php
session_start();
include 'db_connect.php'; // Include database connection

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: landingpage.php');
    exit();
}

// Fetch total materials, low stock, and expiring soon counts
$total_materials_query = "SELECT COUNT(*) as total FROM materials";
$total_materials_result = mysqli_query($conn, $total_materials_query);
$total_materials = mysqli_fetch_assoc($total_materials_result)['total'];

$low_stock_query = "SELECT COUNT(*) as low_stock FROM materials WHERE stock_quantity < 50";
$low_stock_result = mysqli_query($conn, $low_stock_query);
$low_stock = mysqli_fetch_assoc($low_stock_result)['low_stock'];

$expiring_soon_query = "SELECT COUNT(*) as expiring_soon FROM materials WHERE expiration_date < NOW() + INTERVAL 7 DAY";
$expiring_soon_result = mysqli_query($conn, $expiring_soon_query);
$expiring_soon = mysqli_fetch_assoc($expiring_soon_result)['expiring_soon'];

// Initialize filters
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';
$search_term = isset($_GET['search']) ? $_GET['search'] : '';

// Base query for materials
$query = "
    SELECT m.*, COALESCE(MIN(b.delivery_date), m.delivery_date) AS next_delivery_date, MAX(b.expiration_date) AS max_expiration_date
    FROM materials m
    LEFT JOIN material_batches b ON m.material_id = b.material_id
    WHERE 1=1";
$params = [];
$types = '';

// Category filter
if ($category_filter) {
    $query .= " AND m.category = ?";
    $params[] = $category_filter;
    $types .= 's';
}

// Search filter
if ($search_term) {
    $query .= " AND m.material_name LIKE ?";
    $params[] = '%' . $search_term . '%';
    $types .= 's';
}

$query .= " GROUP BY m.material_id";

// Prepare the statement
$stmt = mysqli_prepare($conn, $query);
if ($stmt === false) {
    die('Prepare failed: ' . mysqli_error($conn));
}

// Bind parameters if present
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}

// Execute the prepared statement
if (!mysqli_stmt_execute($stmt)) {
    die('Execute failed: ' . mysqli_stmt_error($stmt));
}

$result = mysqli_stmt_get_result($stmt);

// Fetch unique categories from the database for filter
$category_query = "SELECT DISTINCT category FROM materials";
$category_result = mysqli_query($conn, $category_query);
$categories = [];
if ($category_result) {
    while ($row = mysqli_fetch_assoc($category_result)) {
        $categories[] = $row['category'];
    }
}

// Fetch Action Logs
$log_query = "
    SELECT ml.action_type, m.material_name, ml.quantity, ml.user, ml.action_date, ml.batch_expiration_date, ml.location
    FROM material_log ml
    JOIN materials m ON ml.material_id = m.material_id
    ORDER BY ml.action_date DESC";

$log_result = mysqli_query($conn, $log_query);
if (!$log_result) {
    die('Query Error: ' . mysqli_error($conn));
}
?>
<?php if (isset($_GET['message'])): ?>
<script>
    window.alert("<?php echo htmlspecialchars($_GET['message']); ?>");
</script>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
<script>
    window.alert("<?php echo htmlspecialchars($_GET['error']); ?>");
</script>
<?php endif; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Materials Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .dashboard-header { background-image: url('coffee4.jpg'); background-size: cover; color: Black; padding: 40px; text-align: center; margin-bottom: 20px; border-radius: 0.5rem; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); }
        .dashboard-title { font-size: 2rem; }
        .dashboard-subtitle { font-size: 1.2rem; }
        .card { background-color: #ffffff; }
        .icon { font-size: 40px; margin-bottom: 10px; color: black; }
        .summary-card { background-color: #007bff; } /* Slightly darker blue */
        .low-stock { background-color: #dc3545; } /* Slightly darker red */
        .expiring-soon { background-color: #ffc107; } /* Slightly darker yellow */
    </style>
</head>
<body>
    <div class="dashboard-header">
        <div class="container text-center">
            <h2 class="dashboard-title display-4"><b>Welcome to Calbee's Cafe and Diner
                </h2>
                <span>Inventory Management System</span>
            <h2 class="dashboard-subtitle lead">Hello, <?php echo htmlspecialchars($_SESSION['username']); ?></b></h2>
            <div class="btn-group mt-3" role="group" aria-label="Basic example">
                <a href="add_material.php" class="btn btn-primary">Add New Material</a>
                <a href="logout.php" class="btn btn-danger">Logout</a>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card summary-card text-center p-3">
                    <i class="fas fa-box icon"></i>
                    <h4>Total Materials</h4>
                    <p><?php echo $total_materials; ?></p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card low-stock text-center p-3">
                    <i class="fas fa-exclamation-triangle icon"></i>
                    <h4>Low Stock Items</h4>
                    <p><?php echo $low_stock; ?></p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card expiring-soon text-center p-3">
                    <i class="fas fa-clock icon"></i>
                    <h4>Expiring Soon</h4>
                    <p><?php echo $expiring_soon; ?></p>
                </div>
            </div>
        </div>

        <!-- Category Filter and Search -->
        <form method="GET" class="mb-4">
            <div class="input-group mb-3">
                <select name="category" class="form-select">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo htmlspecialchars($category); ?>" <?php echo ($category_filter == $category) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($category); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="text" name="search" class="form-control" placeholder="Search Materials" value="<?php echo htmlspecialchars($search_term); ?>">
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>
        </form>

        <!-- Materials Inventory Display -->
        <div class="row">
            <?php
            if ($result && mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $stock_quantity = $row['stock_quantity'];
                    $stock_class = ($stock_quantity < 50) ? 'bg-danger' :
                                    (($stock_quantity >= 50 && $stock_quantity <= 100) ? 'bg-warning' : 'bg-success');

                    echo "<div class='col-md-4'>
                            <div class='card mb-4'>
                                <div class='card-body'>
                                    <h5 class='card-title'>{$row['material_name']}</h5>
                                    <p class='card-text'>Category: {$row['category']}</p>
                                    <p class='card-text'>Supplier: {$row['supplier']}</p>
                                    <p class='card-text'>Delivery Date: {$row['next_delivery_date']}</p>
                                    <p class='card-text'>Expiration Date: {$row['max_expiration_date']}</p>
                                    <p class='card-text'>Location: {$row['location']}</p>
                                    <p class='card-text stock-quantity {$stock_class} p-2'>Stock: {$stock_quantity} {$row['unit']}</p>
                                    <button class='btn btn-danger' data-bs-toggle='modal' data-bs-target='#removeStockModal{$row['material_id']}'>Remove Stock</button>
                                </div>
                            </div>
                          </div>";

                     // Remove Stock Modal for this specific material
                    echo "<div class='modal fade' id='removeStockModal{$row['material_id']}' tabindex='-1' role='dialog'>
                            <div class='modal-dialog' role='document'>
                                <div class='modal-content'>
                                    <form method='POST' action='remove_stock.php'>
                                        <div class='modal-header'>
                                            <h5 class='modal-title'>Remove Stock</h5>
                                            <button type='button' class='close' data-bs-dismiss='modal'>
                                                <span>&times;</span>
                                            </button>
                                        </div>
                                        <div class='modal-body'>
                                            <p>Material: {$row['material_name']}</p>
                                            <p>Current Stock: {$stock_quantity}</p>
                                            <div class='form-group'>
                                                <label for='quantity_to_remove'>Quantity to Remove</label>
                                                <input type='number' name='quantity_to_remove' class='form-control' min='1' max='{$stock_quantity}' required>
                                            </div>
                                        </div>
                                        <div class='modal-footer'>
                                            <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Close</button>
                                            <button type='submit' class='btn btn-danger'>Remove</button>
                                            <input type='hidden' name='material_id' value='{$row['material_id']}'>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>";
                }
            } else {
                echo "<div class='col-12'><p class='text-center'>No materials found.</p></div>";
            }
            ?>
        </div>

        <!-- Action Logs -->
        <h4 class="mb-4">Action Logs</h4>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Action Type</th>
                    <th>Material</th>
                    <th>Quantity</th>
                    <th>User</th>
                    <th>Date</th>
                    <th>Expiration Date</th>
                    <th>Location</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($log_result && mysqli_num_rows($log_result) > 0) {
                    while ($log = mysqli_fetch_assoc($log_result)) {
                        echo "<tr>
                                <td>{$log['action_type']}</td>
                                <td>{$log['material_name']}</td>
                                <td>{$log['quantity']}</td>
                                <td>{$log['user']}</td>
                                <td>{$log['action_date']}</td>
                                <td>{$log['batch_expiration_date']}</td>
                                <td>{$log['location']}</td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='7' class='text-center'>No action logs available.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
