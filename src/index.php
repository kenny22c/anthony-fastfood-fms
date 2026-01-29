<?php
session_start();
if (!isset($_SESSION['staffID'])) {
    header("Location: login.php");
    exit;
}

$staffID = $_SESSION['staffID'];
$name    = $_SESSION['name'];
$roleID  = $_SESSION['roleID'];

// Solo Supervisor (3) y Manager (4) pueden ver la lista de staff
$canManageStaff = in_array($roleID, [3, 4], true);
if (!$canManageStaff) {
    // Si es Cook o Waiter, mandarlo al home general
    header("Location: index2.php");
    exit;
}

// Función para traducir roleID a texto
function roleName(int $id): string {
    return match ($id) {
        1       => 'Cook',
        2       => 'Waiter',
        3       => 'Supervisor',
        4       => 'Manager',
        default => 'Unknown'
    };
}

// Conexión a la base de datos
require 'database.php';

// Traemos todos los staff, incluyendo firstName/lastName/roleID
$sql = "
    SELECT 
        staffID,
        firstName,
        lastName,
        name,
        address,
        dateOfBirth,
        email,
        mob,
        roleID
    FROM staff
    ORDER BY staffID ASC
";

$result = $connection->query($sql);
if (!$result) {
    die("Database query failed: " . htmlspecialchars($connection->error));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Staff List – Anthony Fastfood FMS</title>
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
        .table-wrapper {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 0.5rem;
            box-shadow: 0 0.25rem 0.5rem rgba(0,0,0,0.05);
        }
        .navbar-brand span {
            font-weight: 600;
        }
    </style>
</head>
<body>

<!-- Navbar principal -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="index2.php">
            Anthony Fastfood FMS
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar"
                aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="index.php">Staff list</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="create.php">Create staff</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="availability.php">Availability</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="profile.php">My profile</a>
                </li>
            </ul>

            <span class="navbar-text text-light me-3 fw-semibold">
                <?= htmlspecialchars($_SESSION['name']) ?>
                <span class="text-info">– <?= htmlspecialchars(roleName((int)$_SESSION['roleID'])) ?></span>
            </span>


            <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
        </div>
    </div>
</nav>

<main class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 mb-0">Staff List</h1>
            <small class="text-muted">
                From here you can view and manage all staff records (Supervisor / Manager only).
            </small>
        </div>
        <a href="create.php" class="btn btn-primary">
            + Add new staff
        </a>
    </div>

    <div class="table-wrapper">
        <table class="table table-striped table-bordered text-center align-middle">
            <thead class="table-dark">
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">First name</th>
                    <th scope="col">Last name</th>
                    <th scope="col">Address</th>
                    <th scope="col">Date of Birth</th>
                    <th scope="col">Email</th>
                    <th scope="col">Mobile</th>
                    <th scope="col">Role</th>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['staffID']) ?></td>
                    <td><?= htmlspecialchars($row['firstName']) ?></td>
                    <td><?= htmlspecialchars($row['lastName']) ?></td>
                    <td><?= htmlspecialchars($row['address']) ?></td>
                    <td><?= htmlspecialchars($row['dateOfBirth']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td><?= htmlspecialchars($row['mob']) ?></td>
                    <td>
                        <?php
                            $roleIDClean = (int) trim((string)$row['roleID']);
                            echo htmlspecialchars(roleName($roleIDClean));
                        ?>
                    </td>
                    <td class="text-nowrap">
                        <a href="availability.php?staffID=<?= urlencode($row['staffID']) ?>"
                           class="btn btn-info btn-sm me-1">
                            Availability
                        </a>
                        <a href="edit.php?staffID=<?= urlencode($row['staffID']) ?>"
                           class="btn btn-warning btn-sm me-1">
                            Edit
                        </a>
                        <a href="delete.php?staffID=<?= urlencode($row['staffID']) ?>"
                           class="btn btn-danger btn-sm"
                           onclick="return confirm('Are you sure you want to delete this staff record?');">
                            Delete
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>

</main>

<footer class="text-center">
    © Gelos Enterprises – Anthony Fastfood FMS – Student: Kenny Luis Colliard
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
