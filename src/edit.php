<?php
session_start();

// Solo Supervisores (3) y Managers (4) pueden editar staff
$allowedRoles = [3, 4];

if (!isset($_SESSION['staffID']) || !in_array($_SESSION['roleID'], $allowedRoles)) {
    echo "<div style='margin:50px; font-family:Arial'>
            <h3>Access Denied</h3>
            <p>You do not have permission to edit staff records.</p>
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

$errorMessage   = "";
$successMessage = "";

// Obtener staffID desde GET o POST
$staffID = null;
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $staffID = filter_input(INPUT_GET, 'staffID', FILTER_VALIDATE_INT);
} else {
    $staffID = filter_input(INPUT_POST, 'staffID', FILTER_VALIDATE_INT);
}

if (!$staffID) {
    header("Location: index.php");
    exit;
}

// Cargar datos actuales del staff
$sql = "SELECT staffID, firstName, lastName, name, address, dateOfBirth, email, mob, roleID 
        FROM staff WHERE staffID = ?";
$stmt = $connection->prepare($sql);
$stmt->bind_param("i", $staffID);
$stmt->execute();
$result = $stmt->get_result();
$staff  = $result->fetch_assoc();
$stmt->close();

if (!$staff) {
    echo "<div style='margin:50px; font-family:Arial'>
            <h3>Staff not found</h3>
            <p>The requested staff member does not exist.</p>
            <a href='index.php' class='btn btn-primary mt-3'>Return to staff list</a>
          </div>";
    exit;
}

// Datos iniciales para el formulario
$firstName   = $staff['firstName'] ?? '';
$lastName    = $staff['lastName']  ?? '';
$address     = $staff['address']   ?? '';
$dateOfBirth = $staff['dateOfBirth'] ?? '';
$email       = $staff['email']     ?? '';
$mob         = $staff['mob']       ?? '';
$roleID      = $staff['roleID']    ?? '';

// Procesar envío del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $firstName   = trim($_POST['firstName'] ?? '');
    $lastName    = trim($_POST['lastName'] ?? '');
    $address     = trim($_POST['address'] ?? '');
    $dateOfBirth = trim($_POST['dateOfBirth'] ?? '');
    $email       = trim($_POST['email'] ?? '');
    $mob         = trim($_POST['mob'] ?? '');
    $roleID      = trim($_POST['roleID'] ?? '');

    $errors = [];

    if (empty($firstName))   $errors[] = "First name is required.";
    if (empty($lastName))    $errors[] = "Last name is required.";
    if (empty($address))     $errors[] = "Address is required.";
    if (empty($dateOfBirth)) $errors[] = "Date of birth is required.";
    if (empty($email))       $errors[] = "Email is required.";
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL))
        $errors[] = "Invalid email format.";
    if (empty($mob))         $errors[] = "Mobile is required.";
    if (empty($roleID))      $errors[] = "Role is required.";

    if (!empty($errors)) {
        $errorMessage = implode(" ", $errors);
    } else {
        $fullName = trim($firstName . " " . $lastName);

        $sql = "UPDATE staff
                SET firstName = ?, 
                    lastName  = ?, 
                    name      = ?, 
                    address   = ?, 
                    dateOfBirth = ?, 
                    email     = ?, 
                    mob       = ?, 
                    roleID    = ?
                WHERE staffID = ?";

        $stmt = $connection->prepare($sql);
        $stmt->bind_param(
            "sssssssii",
            $firstName,
            $lastName,
            $fullName,
            $address,
            $dateOfBirth,
            $email,
            $mob,
            $roleID,
            $staffID
        );

        if ($stmt->execute()) {
            $successMessage = "Staff record updated successfully.";
        } else {
            $errorMessage = "Database error: " . htmlspecialchars($stmt->error);
        }

        $stmt->close();
    }
}

// Map de roles para mostrar en el select
$roleNames = [
    1 => 'Cook',
    2 => 'Waiter',
    3 => 'Supervisor',
    4 => 'Manager'
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Staff – Anthony Fastfood FMS</title>
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
                <li class="nav-item"><a class="nav-link" href="create.php">Create staff</a></li>
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

<main class="container">
    <h1 class="h3 mb-4">Edit Staff (ID: <?= htmlspecialchars($staffID) ?>)</h1>

    <?php if (!empty($errorMessage)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($errorMessage) ?></div>
    <?php endif; ?>

    <?php if (!empty($successMessage)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($successMessage) ?></div>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="staffID" value="<?= htmlspecialchars($staffID) ?>">

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
                <?php foreach ($roleNames as $id => $label): ?>
                    <option value="<?= $id ?>" <?= ((int)$roleID === $id) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Save changes</button>
        <a href="index.php" class="btn btn-secondary ms-2">Cancel</a>
    </form>
</main>

<footer class="text-center mt-4 mb-3">
    © Gelos Enterprises – Anthony Fastfood FMS – Student: Kenny Luis Colliard
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
