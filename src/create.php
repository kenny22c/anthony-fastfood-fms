<?php
session_start();

// Only Supervisors (3) and Managers (4) can access Create Staff
$allowedRoles = [3, 4];

if (!isset($_SESSION['staffID']) || !in_array($_SESSION['roleID'], $allowedRoles)) {
    echo "<div style='margin:50px; font-family:Arial'>
            <h3>Access Denied</h3>
            <p>You do not have permission to create staff members.</p>
            <a href='index2.php' class='btn btn-primary mt-3'>Return to home</a>
          </div>";
    exit;
}

require 'database.php';

// 🔥 AGREGAR ESTO — FUNCIÓN QUE FALTABA
function roleName(int $id): string {
    return match ($id) {
        1       => 'Cook',
        2       => 'Waiter',
        3       => 'Supervisor',
        4       => 'Manager',
        default => 'Unknown',
    };
}

$firstName      = "";
$lastName       = "";
$address        = "";
$dateOfBirth    = "";
$email          = "";
$mob            = "";
$roleID         = "";
$errorMessage   = "";
$successMessage = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $firstName   = trim($_POST['firstName'] ?? "");
    $lastName    = trim($_POST['lastName'] ?? "");
    $address     = trim($_POST['address'] ?? "");
    $dateOfBirth = trim($_POST['dateOfBirth'] ?? "");
    $email       = trim($_POST['email'] ?? "");
    $mob         = trim($_POST['mob'] ?? "");
    $roleID      = trim($_POST['roleID'] ?? "");

    $errors = [];

    if (empty($firstName))   $errors[] = "First name is required.";
    if (empty($lastName))    $errors[] = "Last name is required.";
    if (empty($address))     $errors[] = "Address is required.";
    if (empty($dateOfBirth)) $errors[] = "Date of birth is required.";
    if (empty($email))       $errors[] = "Email is required.";
    if (empty($mob))         $errors[] = "Mobile is required.";
    if (empty($roleID))      $errors[] = "Role is required.";

    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email format is invalid.";
    }

    if (!empty($errors)) {
        $errorMessage = implode("<br>", $errors);
    } else {

        // Full name para mantener compatibilidad con la columna 'name'
        $fullName = trim($firstName . " " . $lastName);

        $sql = "INSERT INTO staff (firstName, lastName, name, address, dateOfBirth, email, mob, roleID)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $connection->prepare($sql);
        $stmt->bind_param(
            "sssssssi",
            $firstName,
            $lastName,
            $fullName,
            $address,
            $dateOfBirth,
            $email,
            $mob,
            $roleID
        );

        if ($stmt->execute()) {
            $successMessage = "New staff member created successfully.";

            // Clear form
            $firstName   = "";
            $lastName    = "";
            $address     = "";
            $dateOfBirth = "";
            $email       = "";
            $mob         = "";
            $roleID      = "";
        } else {
            $errorMessage = "Database error: " . htmlspecialchars($stmt->error);
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Staff – Anthony Fastfood FMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light" style="padding-top:70px;">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="index2.php">Anthony Fastfood FMS</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="index.php">Staff list</a></li>
                <li class="nav-item"><a class="nav-link active" href="create.php">Create staff</a></li>
                <li class="nav-item"><a class="nav-link" href="availability.php">Availability</a></li>
                <li class="nav-item"><a class="nav-link" href="profile.php">My profile</a></li>
            </ul>

            <span class="navbar-text text-light me-3 fw-semibold">
                <?= htmlspecialchars($_SESSION['name']) ?>
                <span class="text-info">– <?= htmlspecialchars(roleName((int)$_SESSION['roleID'])) ?></span>
            </span>
            <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
        </div>
    </div>
</nav>

<div class="container">
    <h1 class="h3 mb-4">Create New Staff</h1>

    <?php if ($errorMessage): ?>
        <div class="alert alert-danger"><?= $errorMessage ?></div>
    <?php endif; ?>

    <?php if ($successMessage): ?>
        <div class="alert alert-success"><?= $successMessage ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">First name</label>
                <input type="text" name="firstName" class="form-control"
                       value="<?= htmlspecialchars($firstName) ?>">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Last name</label>
                <input type="text" name="lastName" class="form-control"
                       value="<?= htmlspecialchars($lastName) ?>">
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Address</label>
            <input type="text" name="address" class="form-control"
                   value="<?= htmlspecialchars($address) ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Date of birth</label>
            <input type="date" name="dateOfBirth" class="form-control"
                   value="<?= htmlspecialchars($dateOfBirth) ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control"
                   value="<?= htmlspecialchars($email) ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Mobile</label>
            <input type="text" name="mob" class="form-control"
                   value="<?= htmlspecialchars($mob) ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Role</label>
            <select name="roleID" class="form-select">
                <option value="">-- Select role --</option>
                <option value="1" <?= $roleID == "1" ? "selected" : "" ?>>Cook</option>
                <option value="2" <?= $roleID == "2" ? "selected" : "" ?>>Waiter</option>
                <option value="3" <?= $roleID == "3" ? "selected" : "" ?>>Supervisor</option>
                <option value="4" <?= $roleID == "4" ? "selected" : "" ?>>Manager</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Create</button>
        <a href="index.php" class="btn btn-secondary ms-2">Cancel</a>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
