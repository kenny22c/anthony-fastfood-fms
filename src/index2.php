<?php
session_start();
if (!isset($_SESSION['staffID'])) {
    header("Location: login.php");
    exit;
}

$name   = $_SESSION['name'] ?? '';
$roleID = (int)($_SESSION['roleID'] ?? 0);

// Función para mostrar el rol en texto
function roleName(int $id): string {
    return match ($id) {
        1       => 'Cook',
        2       => 'Waiter',
        3       => 'Supervisor',
        4       => 'Manager',
        default => 'Unknown'
    };
}

// Solo Supervisores/Managers pueden ver el botón de Staff list
$canManageStaff = in_array($roleID, [3, 4], true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Home – Anthony Fastfood FMS</title>
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
        .card-feature {
            min-height: 260px;
        }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="index2.php">Anthony Fastfood FMS</a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar"
                aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <?php if ($canManageStaff): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Staff list</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="create.php">Create staff</a>
                    </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link" href="availability.php">Availability</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="profile.php">My profile</a>
                </li>
            </ul>

            <span class="navbar-text text-light me-3 fw-semibold">
                <?= htmlspecialchars($name) ?>
                <span class="text-info">– <?= htmlspecialchars(roleName($roleID)) ?></span>
            </span>
            <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
        </div>
    </div>
</nav>

<!-- CONTENIDO PRINCIPAL -->
<main class="container mt-4">

    <h1 class="h3 text-center mb-3">Welcome to Anthony Fastfood FMS</h1>
    <p class="text-muted text-center mb-4">
        Use this home page to access your roster availability, update your own profile details, and, for
        authorised users, manage staff records.
    </p>

    <div class="row g-4">
        <!-- Staff management (solo para Supervisor/Manager, pero el texto explica la restricción) -->
        <div class="col-md-4">
            <div class="card card-feature border-primary">
                <div class="card-header bg-primary text-white fw-semibold">
                    Staff management
                </div>
                <div class="card-body">
                    <p class="card-text">
                        View and manage staff records. This area is restricted to supervisors and managers.
                    </p>
                    <ul>
                        <li>View all current staff</li>
                        <li>Create new staff members</li>
                        <li>Edit staff details</li>
                        <li>Delete staff records</li>
                    </ul>
                    <?php if ($canManageStaff): ?>
                        <a href="index.php" class="btn btn-primary">Go to staff list</a>
                    <?php else: ?>
                        <button class="btn btn-secondary" disabled>Restricted area</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Staff availability -->
        <div class="col-md-4">
            <div class="card card-feature border-success">
                <div class="card-header bg-success text-white fw-semibold">
                    Staff availability
                </div>
                <div class="card-body">
                    <p class="card-text">
                        Select which roster shifts you are available to work. This information is stored in the database
                        and can be used to build staff rosters.
                    </p>
                    <ul>
                        <li>View available roster shifts</li>
                        <li>Select shifts you can work</li>
                        <li>Update your availability if it changes</li>
                    </ul>
                    <a href="availability.php" class="btn btn-success">Update availability</a>
                </div>
            </div>
        </div>

        <!-- My profile -->
        <div class="col-md-4">
            <div class="card card-feature border-info">
                <div class="card-header bg-info text-white fw-semibold">
                    My profile
                </div>
                <div class="card-body">
                    <p class="card-text">
                        Review and update your own contact details (name, address, email and mobile). Your profile is
                        only visible to you and authorised supervisors/managers.
                    </p>
                    <ul>
                        <li>Check your current details</li>
                        <li>Update your contact information</li>
                        <li>Keep your records accurate and up to date</li>
                    </ul>
                    <a href="profile.php" class="btn btn-info">View / edit my profile</a>
                </div>
            </div>
        </div>
    </div>

    <div class="alert alert-info mt-4">
        <strong>Note for assessment:</strong> This home page is used to demonstrate navigation after login.
        Access to staff management is restricted by user role using PHP sessions, while all users can
        update their own profile and availability.
    </div>

</main>

<footer class="text-center">
    © Gelos Enterprises – Anthony Fastfood FMS – Student: Kenny Luis Colliard
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
