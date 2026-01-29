<?php
session_start();

// Solo Supervisores (3) y Managers (4)
$allowedRoles = [3, 4];

if (!isset($_SESSION['staffID']) || !in_array($_SESSION['roleID'], $allowedRoles)) {
    echo "<div style='margin:50px; font-family:Arial'>
            <h3>Access Denied</h3>
            <p>You do not have permission to delete staff records.</p>
            <a href='index.php' class='btn btn-primary mt-3'>Return to staff list</a>
          </div>";
    exit;
}

require 'database.php';

// Helper para mostrar el nombre del rol
function getRoleName($roleID) {
    switch ($roleID) {
        case 1: return 'Cook';
        case 2: return 'Waiter';
        case 3: return 'Supervisor';
        case 4: return 'Manager';
        default: return 'Unknown';
    }
}

$staffID = "";
$name    = "";
$email   = "";

$errorMessage   = "";
$successMessage = "";

// ========== CONFIRMACIÓN (GET) ==========
if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    if (!isset($_GET['staffID']) || !ctype_digit($_GET['staffID'])) {
        $errorMessage = "Invalid staff ID.";
    } else {
        $staffID = (int) $_GET['staffID'];

        $sql  = "SELECT staffID, name, email FROM staff WHERE staffID = ?";
        $stmt = $connection->prepare($sql);

        if (!$stmt) {
            $errorMessage = "Prepare failed: " . $connection->error;
        } else {
            $stmt->bind_param("i", $staffID);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                $errorMessage = "Staff record not found.";
            } else {
                $row    = $result->fetch_assoc();
                $staffID = $row['staffID'];
                $name    = $row['name'];
                $email   = $row['email'];
            }

            $stmt->close();
        }
    }

// ========== ELIMINACIÓN (POST) ==========
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_POST['staffID']) || !ctype_digit($_POST['staffID'])) {
        $errorMessage = "Invalid staff ID.";
    } else {
        $staffID = (int) $_POST['staffID'];

        $sql  = "DELETE FROM staff WHERE staffID = ?";
        $stmt = $connection->prepare($sql);

        if (!$stmt) {
            $errorMessage = "Prepare failed: " . $connection->error;
        } else {
            $stmt->bind_param("i", $staffID);
            if ($stmt->execute()) {
                // Volvemos a la lista después de borrar
                header("Location: index.php");
                exit;
            } else {
                $errorMessage = "Error deleting record: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delete Staff – Anthony Fastfood FMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="padding-top:70px;">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">Anthony Fastfood FMS</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="index.php">Staff list</a></li>
                <li class="nav-item"><a class="nav-link" href="create.php">Create staff</a></li>
                <li class="nav-item"><a class="nav-link" href="availability.php">Availability</a></li>
                <li class="nav-item"><a class="nav-link" href="profile.php">My profile</a></li>
            </ul>
            <span class="navbar-text me-3 text-light">
                Logged in as: <?= htmlspecialchars($_SESSION['name']) ?>
                (<?= getRoleName($_SESSION['roleID']) ?>)
            </span>
            <a class="btn btn-outline-light btn-sm" href="logout.php">Logout</a>
        </div>
    </div>
</nav>

<div class="container">
    <h1 class="h3 mb-3 text-danger">Delete staff</h1>

    <?php if ($errorMessage): ?>
        <div class="alert alert-danger"><?= $errorMessage ?></div>
    <?php else: ?>
        <div class="alert alert-warning">
            <strong>Warning:</strong> You are about to delete this staff record. This action cannot be undone.
        </div>

        <p>
            <strong>Staff ID:</strong> <?= htmlspecialchars($staffID) ?><br>
            <strong>Name:</strong> <?= htmlspecialchars($name) ?><br>
            <strong>Email:</strong> <?= htmlspecialchars($email) ?>
        </p>

        <form method="post">
            <input type="hidden" name="staffID" value="<?= htmlspecialchars($staffID) ?>">
            <button type="submit" class="btn btn-danger">Yes, delete this staff member</button>
            <a href="index.php" class="btn btn-secondary ms-2">Cancel</a>
        </form>
    <?php endif; ?>
</div>

<footer class="text-center mt-4 mb-3 text-muted">
    © Gelos Enterprises – Anthony Fastfood FMS – Student: Kenny Luis Colliard
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
