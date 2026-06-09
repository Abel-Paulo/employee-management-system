<?php
session_start();
// If the user is not logged in, redirect them to the login page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'db.php';

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Handle Employee Deletion
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $delete_id = (int)$_GET['id'];
    
    // First, optional but recommended: You could write code to delete physical files from the folder here.
    // For now, let's remove the database record. MySQL CASCADE will clean up the employee_files table records.
    $deleteStmt = $pdo->prepare("DELETE FROM employees WHERE id = :id");
    $deleteStmt->execute([':id' => $delete_id]);
    
    header("Location: index.php?msg=Deleted");
    exit;
}
// Fetch employees (Normal load OR filtered by search query)
if (!empty($search)) {
    $stmt = $pdo->prepare('SELECT * FROM employees WHERE full_name LIKE :search1 OR department LIKE :search2 ORDER BY id DESC');
    $stmt->execute([
        ':search1' => '%' . $search . '%',
        ':search2' => '%' . $search . '%'
    ]);
} else {
    $stmt = $pdo->query('SELECT * FROM employees ORDER BY id DESC');
}
$employees = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Employee Management System Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background-color: #f4f7f6; }
        .header-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        h2 { color: #333; margin: 0; }
        .logout-link { color: #dc3545; text-decoration: none; font-weight: bold; }
        .search-form { margin-bottom: 20px; display: flex; gap: 10px; }
        .search-input { padding: 8px; width: 300px; border: 1px solid #ccc; border-radius: 4px; }
        table { width: 100%; border-collapse: collapse; background: #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #0056b3; color: white; }
        tr:hover { background-color: #f1f1f1; }
        .btn { padding: 6px 12px; text-decoration: none; color: white; border-radius: 4px; font-size: 14px; display: inline-block; }
        .btn-add { background: #28a745; padding: 10px 15px; font-size: 16px; margin-bottom: 15px; }
        .btn-view { background: #007bff; }
        .btn-delete { background: #dc3545; margin-left: 5px; }
    </style>
</head>
<body>

    <div class="header-bar">
        <h2>Employee Records Dashboard</h2>
        <div>
            <span>Welcome, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>! </span>
            <a href="logout.php" class="logout-link">Logout</a>
        </div>
    </div>

    <a href="add_employee.php" class="btn btn-add">Add New Employee</a>

    <form action="index.php" method="GET" class="search-form">
        <input type="text" name="search" class="search-input" placeholder="Search by name or department..." value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit" class="btn " style="background:#555;">Search</button>
        <?php if (!empty($search)): ?>
            <a href="index.php" class="btn" style="background:#aaa; color:#333;">Clear</a>
        <?php endif; ?>
    </form>

    <table>
        <thead>
            <tr>
                <th>Employee ID</th>
                <th>Full Name</th>
                <th>Department</th>
                <th>Position</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($employees) > 0): ?>
                <?php foreach ($employees as $emp): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($emp['emp_id']); ?></strong></td>
                        <td><?php echo htmlspecialchars($emp['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($emp['department']); ?></td>
                        <td><?php echo htmlspecialchars($emp['position']); ?></td>
                        <td>
                            <a href="view_employee.php?id=<?php echo $emp['id']; ?>" class="btn btn-view">View Files</a>
                            <a href="index.php?action=delete&id=<?php echo $emp['id']; ?>" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this employee and all their files?');">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="text-align: center; color:#777;">No matching employee records found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

</body>
</html>