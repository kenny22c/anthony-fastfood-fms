<?php
session_start();
if (!isset($_SESSION['staffID'])) {
    header("Location: login.php");
    exit;
}

$staffID = (int)$_SESSION['staffID'];
$sessionName = $_SESSION['name'] ?? '';
$roleID  = (int)($_SESSION['roleID'] ?? 0);

require 'database.php';

$errorMessage   = "";
$successMessage = "";

// Función para mostrar el rol en texto (igual que en index.php)
function roleName(int $id): string {
    return match ($id) {
        1       => 'Cook',
        2       => 'Waiter',
        3       => 'Supervisor',
        4       => 'Manager',
        default => 'Unknown'
    };
}

// 1) Cargar datos actuales del staff logueado
$sql = "SELECT staffID, firstName, lastName, name, address, dateOfBirth, email, mob, roleID
        FROM staff
        WHERE staffID = ?";
$stmt = $connection->prepare($sql);
$stmt->bind_param("i", $staffID);
$stmt->execute();
$result = $stmt->get_result();
$staff  = $result->fetch_assoc();
$stmt->close();

if (!$staff) {
    // Si llegas acá es porque la sesión tiene un staffID que no existe en la tabla
    echo "<div style='margin:50px; font-family:Arial'>
            <h3>Profile not found</h3>
            <p>Your staff profile could not be found in the system.</p>
            <a href='logout.php' class='btn btn-primary mt-3'>Logout</a>
          </div>";
    exit;
}

// Valores iniciales para el formulario
$firstName   = $staff['firstName'] ?? '';
$lastName    = $staff['lastName']  ?? '';
$address     = $staff['address']   ?? '';
$dateOfBirth = $staff['dateOfBirth'] ?? '';
$email       = $staff['email']     ?? '';
$mob         = $staff['mob']       ?? '';

// 2) Si enviaste el formulario, actualizar datos
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $firstName   = trim($_POST['firstName'] ?? '');
    $lastName    = trim($_POST['lastName'] ?? '');
    $address     = trim($_POST['address'] ?? '');
    $dateOfBirth = trim($_POST['dateOfBirth'] ?? '');
    $email       = trim($_POST['email'] ?? '');
    $mob         = trim($_POST['mob'] ?? '');

    $errors = [];

    if ($firstName === '')   $errors[] = "First name is required.";
    if ($lastName === '')    $errors[] = "Last name is required.";
    if ($address === '')     $errors[] = "Address is required.";
    if ($dateOfBirth === '') $errors[] = "Date of birth is required.";
    if ($email === '')       $errors[] = "Email is required.";
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }
    if ($mob === '')         $errors[] = "Mobile is required.";

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
                    mob       = ?
                WHERE staffID = ?";

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
            $staffID
        );

        if ($stmt->execute()) {
            $successMessage = "Profile updated successfully.";

            // Actualizar nombre en la sesión para que la navbar muestre el nuevo nombre
            $_SESSION['name'] = $fullName;
            $sessionName      = $fullName;
        } else {
            $errorMessage = "Database error: " . htmlspecialchars($stmt->error);
        }

        $stmt->close();
    }
}

$roleLabel = roleName($roleID);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile – Anthony Fastfood FMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding-top: 70px;
            background-color: #f5f5f5;
        }
        footer {
            margin-top: 40px;
            padding: 10px 0;
            border-top: 1px solid #ddd;
            font-size: 0.9rem;
            color: #666;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="index2.php">Anthony Fastfood FMS</a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <?php if (in_array($roleID, [3,4], true)): ?>
                    <li class="nav-item"><a class="nav-link" href="index.php">Staff list</a></li>
                    <li class="nav-item"><a class="nav-link" href="create.php">Create staff</a></li>
                <?php endif; ?>
                <li class="nav-item"><a class="nav-link" href="availability.php">Availability</a></li>
                <li class="nav-item"><a class="nav-link active" href="profile.php">My profile</a></li>
            </ul>

            <span class="navbar-text text-light me-3 fw-semibold">
                <?= htmlspecialchars($sessionName) ?>
                <span class="text-info">– <?= htmlspecialchars($roleLabel) ?></span>
            </span>
            <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
        </div>
    </div>
</nav>

<main class="container">
    <h1 class="h3 mb-3">My Profile</h1>
    <p class="text-muted mb-4">
        Update your own contact details. These details are stored securely in the database and are only visible
        to you and authorised supervisors/managers.
    </p>

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
            <label class="form-label">Role</label><br>
            <span class="badge bg-secondary"><?= htmlspecialchars($roleLabel) ?></span>
            <small class="text-muted ms-2">(Role cannot be changed from profile)</small>
        </div>

        <button type="submit" class="btn btn-primary">Save changes</button>
        <a href="index2.php" class="btn btn-secondary ms-2">Cancel</a>
    </form>
</main>

<footer class="text-center mt-4 mb-3">
    © Gelos Enterprises – Anthony Fastfood FMS – Student: Kenny Luis Colliard
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
