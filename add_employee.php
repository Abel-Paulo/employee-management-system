<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'db.php';

$message = '';
$messageClass = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emp_id = trim($_POST['emp_id']);
    $full_name = trim($_POST['full_name']);
    $department = trim($_POST['department']);
    $position = trim($_POST['position']);

    if (!empty($emp_id) && !empty($full_name) && !empty($department) && !empty($position)) {
        try {
            $sql = "INSERT INTO employees (emp_id, full_name, department, position) VALUES (:emp_id, :full_name, :department, :position)";
            $stmt = $pdo->prepare($sql);
            
            $stmt->execute([
                ':emp_id' => $emp_id,
                ':full_name' => $full_name,
                ':department' => $department,
                ':position' => $position
            ]);

            $message = "Employee added successfully! <a href='index.php'>Go to Dashboard</a>";
            $messageClass = "success";
        } catch (\PDOException $e) {
            if ($e->getCode() == 23000) {
                $message = "Error: An employee with ID '$emp_id' already exists.";
            } else {
                $message = "Error: Could not save the record. " . $e->getMessage();
            }
            $messageClass = "error";
        }
    } else {
        $message = "All fields are required.";
        $messageClass = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add New Employee</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background-color: #f4f7f6; }
        .form-container { max-width: 500px; background: #fff; padding: 30px; margin: 0 auto; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        h2 { margin-top: 0; color: #333; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #555; }
        input[type="text"] { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .btn-submit { background-color: #28a745; color: white; border: none; padding: 12px 20px; border-radius: 4px; cursor: pointer; width: 100%; font-size: 16px; }
        .btn-submit:hover { background-color: #218838; }
        .back-link { display: inline-block; margin-top: 15px; color: #007bff; text-decoration: none; }
        .alert { padding: 10px; margin-bottom: 15px; border-radius: 4px; font-weight: bold; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Add New Employee</h2>
    
    <?php if (!empty($message)): ?>
        <div class="alert <?php echo $messageClass; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <form action="add_employee.php" method="POST">
        <div class="form-group">
            <label for="emp_id">Employee ID</label>
            <input type="text" id="emp_id" name="emp_id" placeholder="e.g., EMP-001" required>
        </div>
        
        <div class="form-group">
            <label for="full_name">Full Name</label>
            <input type="text" id="full_name" name="full_name" placeholder="John Doe" required>
        </div>
        
        <div class="form-group">
            <label for="department">Department</label>
            <input type="text" id="department" name="department" placeholder="e.g., Human Resources" required>
        </div>
        
        <div class="form-group">
            <label for="position">Position</label>
            <input type="text" id="position" name="position" placeholder="e.g., HR Specialist" required>
        </div>
        
        <button type="submit" class="btn-submit">Save Employee</button>
    </form>

    <a href="index.php" class="back-link">← Back to Dashboard</a>
</div>

</body>
</html>