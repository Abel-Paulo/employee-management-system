<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

ini_set('display_errors', 1); error_reporting(E_ALL);
require_once 'db.php';

$message = '';
$messageClass = '';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Error: Employee ID not specified. <a href='index.php'>Go back</a>");
}

$employee_id = (int)$_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM employees WHERE id = :id");
$stmt->execute([':id' => $employee_id]);
$employee = $stmt->fetch();

if (!$employee) {
    die("Error: Employee not found. <a href='index.php'>Go back</a>");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['employee_file'])) {
    $file = $_FILES['employee_file'];
    
    $raw_file_name = $file['name'];
    $file_tmp      = $file['tmp_name'];
    $file_error    = $file['error'];
    
    if ($file_error === 0) {
        $clean_file_name = basename($raw_file_name);
        $unique_file_name = time() . '_' . $clean_file_name;
        
        $upload_dir = 'uploads/';
        $destination = $upload_dir . $unique_file_name;
        
        if (move_uploaded_file($file_tmp, $destination)) {
            try {
                $sql = "INSERT INTO employee_files (employee_id, file_name, file_path) VALUES (:employee_id, :file_name, :file_path)";
                $insertStmt = $pdo->prepare($sql);
                $insertStmt->execute([
                    ':employee_id' => $employee_id,
                    ':file_name'   => $clean_file_name,
                    ':file_path'   => $destination
                ]);
                
                $message = "File uploaded and linked successfully!";
                $messageClass = "success";
            } catch (\PDOException $e) {
                $message = "Database error: Could not log file tracking data. " . $e->getMessage();
                $messageClass = "error";
            }
        } else {
            $message = "Error: Failed to move uploaded file to the destination folder. Check server permissions.";
            $messageClass = "error";
        }
    } else {
        $message = "Error occurred during file transit. Error Code: " . $file_error;
        $messageClass = "error";
    }
}

$fileStmt = $pdo->prepare("SELECT * FROM employee_files WHERE employee_id = :emp_id ORDER BY uploaded_at DESC");
$fileStmt->execute([':emp_id' => $employee_id]);
$files = $fileStmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Files - <?php echo htmlspecialchars($employee['full_name']); ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background-color: #f4f7f6; }
        .container { max-width: 800px; margin: 0 auto; background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .emp-profile { background: #eef1f5; padding: 15px; border-radius: 6px; margin-bottom: 25px; border-left: 5px solid #0056b3; }
        .emp-profile p { margin: 5px 0; font-size: 15px; }
        h2, h3 { color: #333; }
        .upload-section { background: #fdfdfd; border: 2px dashed #ccc; padding: 20px; border-radius: 6px; margin-bottom: 25px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f1f1f1; color: #555; }
        .btn { padding: 6px 12px; text-decoration: none; border-radius: 4px; color: white; background-color: #007bff; font-size: 14px; }
        .btn-upload { background-color: #28a745; border: none; cursor: pointer; padding: 10px 20px; font-size: 15px; }
        .back-link { display: inline-block; margin-bottom: 15px; color: #007bff; text-decoration: none; }
        .alert { padding: 10px; margin-bottom: 15px; border-radius: 4px; font-weight: bold; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>

<div class="container">
    <a href="index.php" class="back-link">← Back to Dashboard</a>
    
    <h2>Employee Profile</h2>
    
    <div class="emp-profile">
        <p><strong>Employee ID:</strong> <?php echo htmlspecialchars($employee['emp_id']); ?></p>
        <p><strong>Full Name:</strong> <?php echo htmlspecialchars($employee['full_name']); ?></p>
        <p><strong>Department:</strong> <?php echo htmlspecialchars($employee['department']); ?></p>
        <p><strong>Position:</strong> <?php echo htmlspecialchars($employee['position']); ?></p>
    </div>

    <?php if (!empty($message)): ?>
        <div class="alert <?php echo $messageClass; ?>"><?php echo $message; ?></div>
    <?php endif; ?>

    <div class="upload-section">
        <h3>Upload New Document</h3>
        <form action="view_employee.php?id=<?php echo $employee_id; ?>" method="POST" enctype="multipart/form-data">
            <input type="file" name="employee_file" required style="margin-bottom: 15px; display: block;">
            <button type="submit" class="btn btn-upload">Upload Document</button>
        </form>
    </div>

    <h3>Stored Documents</h3>
    <table>
        <thead>
            <tr>
                <th>File Name</th>
                <th>Date Uploaded</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($files) > 0): ?>
                <?php foreach ($files as $file): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($file['file_name']); ?></td>
                        <td><?php echo date('M d, Y h:i A', strtotime($file['uploaded_at'])); ?></td>
                        <td>
                            <a href="<?php echo htmlspecialchars($file['file_path']); ?>" download class="btn">Download</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3" style="text-align: center; color: #777;">No files uploaded for this employee yet.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>