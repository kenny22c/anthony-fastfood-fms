<?php
session_start();
if (!isset($_SESSION['staffID'])) {
    header("Location: login.php");
    exit;
}

$loggedStaffID = (int)$_SESSION['staffID'];
$loggedRoleID  = (int)$_SESSION['roleID'];
$loggedName    = $_SESSION['name'] ?? '';

require 'database.php';

// Solo Supervisores (3) y Managers (4) pueden ver/gestionar todos los staff
$canViewAll = in_array($loggedRoleID, [3, 4], true);

function roleName(int $id): string
{
    return match ($id) {
        1       => 'Cook',
        2       => 'Waiter',
        3       => 'Supervisor',
        4       => 'Manager',
        default => 'Unknown',
    };
}

// ----------------------------------------------------
// 1. Determinar staff seleccionado (según rol)
// ----------------------------------------------------
$selectedStaffID = $loggedStaffID;

if ($canViewAll) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['staffID'])) {
        $selectedStaffID = (int)$_POST['staffID'];
    } elseif (isset($_GET['staffID'])) {
        $selectedStaffID = (int)$_GET['staffID'];
    }
}

// ----------------------------------------------------
// 2. Actualizar disponibilidad (checkboxes) si se envió el formulario
// ----------------------------------------------------
$updateMessage = '';
$updateError   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['updateAvailability'])) {
    // Por seguridad, volvemos a leer el staffID del POST
    $selectedStaffID = (int)($_POST['staffID'] ?? $selectedStaffID);

    // Sanitizar rosterIDs seleccionados
    $selectedRosterIDs = isset($_POST['rosterIDs']) && is_array($_POST['rosterIDs'])
        ? array_map('intval', $_POST['rosterIDs'])
        : [];

    // Sólo supervisores pueden cambiar la disponibilidad de otros
    if (!$canViewAll && $selectedStaffID !== $loggedStaffID) {
        $updateError = 'You do not have permission to update availability for this staff member.';
    } else {
        // Usamos transacción simple
        $connection->begin_transaction();

        try {
            // Borrar disponibilidad previa de ese staff
            $sqlDelete = "DELETE FROM availability WHERE staffID = ?";
            if ($stmtDel = $connection->prepare($sqlDelete)) {
                $stmtDel->bind_param('i', $selectedStaffID);
                $stmtDel->execute();
                $stmtDel->close();
            }

            // Insertar disponibilidad nueva
            if (!empty($selectedRosterIDs)) {
                $sqlInsert = "INSERT INTO availability (staffID, rosterID) VALUES (?, ?)";
                if ($stmtIns = $connection->prepare($sqlInsert)) {
                    foreach ($selectedRosterIDs as $rid) {
                        $stmtIns->bind_param('ii', $selectedStaffID, $rid);
                        $stmtIns->execute();
                    }
                    $stmtIns->close();
                }
            }

            $connection->commit();
            $updateMessage = 'Availability updated successfully.';
        } catch (Exception $e) {
            $connection->rollback();
            $updateError = 'Error updating availability. Please try again.';
        }
    }
}

// ----------------------------------------------------
// 3. Cargar lista de staff para el desplegable (solo Supervisor/Manager)
// ----------------------------------------------------
$staffList = [];
if ($canViewAll) {
    $sqlStaff = "SELECT staffID, firstName, lastName FROM staff ORDER BY staffID";
    if ($stmt = $connection->prepare($sqlStaff)) {
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $staffList[] = $row;
        }
        $stmt->close();
    }
}

// ----------------------------------------------------
// 4. Cargar todos los roster + estado de disponibilidad para el staff seleccionado
// ----------------------------------------------------
$rosters = [];
$sqlRoster = "
    SELECT 
        r.rosterID,
        r.dateTimeFrom,
        r.dateTimeTo,
        CASE WHEN a.staffID IS NULL THEN 0 ELSE 1 END AS isAvailable
    FROM roster r
    LEFT JOIN availability a
      ON a.rosterID = r.rosterID
     AND a.staffID  = ?
    ORDER BY r.dateTimeFrom
";

if ($stmt = $connection->prepare($sqlRoster)) {
    $stmt->bind_param('i', $selectedStaffID);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $rosters[] = $row;
    }
    $stmt->close();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Availability – Anthony Fastfood FMS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
</head>
<body class="bg-light" style="padding-top: 70px;">

<!-- NAVBAR (mismo estilo que profile/index) -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="index2.php">Anthony Fastfood FMS</a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#mainNavbar" aria-controls="mainNavbar"
                aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <?php if ($canViewAll): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Staff list</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="create.php">Create staff</a>
                    </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link active" href="availability.php">Availability</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="profile.php">My profile</a>
                </li>
            </ul>

            <span class="navbar-text text-light me-3">
                <?= htmlspecialchars($loggedName) ?> –
                <span class="text-info"><?= htmlspecialchars(roleName($loggedRoleID)) ?></span>
            </span>
            <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
        </div>
    </div>
</nav>

<div class="container mt-4">

    <h1 class="h3 mb-2">Availability</h1>
    <p class="text-muted mb-4">
        View and manage availability for rostered shifts.
    </p>

    <!-- Mensajes de actualización -->
    <?php if ($updateMessage): ?>
        <div class="alert alert-success"><?= htmlspecialchars($updateMessage) ?></div>
    <?php endif; ?>
    <?php if ($updateError): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($updateError) ?></div>
    <?php endif; ?>

    <!-- Selector de staff (solo Supervisor / Manager) -->
    <?php if ($canViewAll): ?>
        <form method="post" class="row g-2 mb-3">
            <div class="col-md-6">
                <label for="staffID" class="form-label">Select staff</label>
                <select name="staffID" id="staffID" class="form-select">
                    <?php foreach ($staffList as $s): ?>
                        <?php
                        $fullName = trim($s['firstName'] . ' ' . $s['lastName']);
                        $value    = (int)$s['staffID'];
                        ?>
                        <option value="<?= $value ?>" <?= $value === $selectedStaffID ? 'selected' : '' ?>>
                            <?= $value ?> - <?= htmlspecialchars($fullName) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6 d-flex align-items-end justify-content-between">
                <div>
                    <button type="submit" class="btn btn-primary me-2">Load</button>
                    <a href="availability.php" class="btn btn-outline-secondary btn-sm">Reset</a>
                </div>

                <!-- Botón para gestionar rosters (crear/editar) -->
                <div>
                    <a href="roster_manage.php" class="btn btn-outline-dark btn-sm">
                        Manage roster shifts
                    </a>
                </div>
            </div>
        </form>
    <?php else: ?>
        <p class="mb-3"><strong>Staff:</strong> <?= htmlspecialchars($loggedName) ?></p>
    <?php endif; ?>

    <!-- Leyenda -->
    <div class="mb-3">
        <strong>Legend:</strong>
        <span class="badge bg-success ms-2">Available</span>
        <span class="badge bg-danger ms-2">Not available</span>
    </div>

    <!-- Tabla de rosters + disponibilidad -->
    <form method="post">
        <input type="hidden" name="staffID" value="<?= (int)$selectedStaffID ?>">
        <input type="hidden" name="updateAvailability" value="1">

        <div class="table-responsive">
            <table class="table table-striped table-bordered align-middle">
                <thead class="table-dark">
                <tr>
                    <th style="width: 40px;">
                        <input type="checkbox" id="selectAll"
                               onclick="toggleAll(this)">
                    </th>
                    <th>Roster ID</th>
                    <th>Start</th>
                    <th>End</th>
                    <th>Status</th>
                </tr>
                </thead>
                <tbody>
                <?php if (!empty($rosters)): ?>
                    <?php foreach ($rosters as $row): ?>
                        <?php
                        $rid          = (int)$row['rosterID'];
                        $isAvailable  = (int)$row['isAvailable'] === 1;
                        ?>
                        <tr>
                            <td class="text-center">
                                <input type="checkbox"
                                       name="rosterIDs[]"
                                       value="<?= $rid ?>"
                                       <?= $isAvailable ? 'checked' : '' ?>>
                            </td>
                            <td><?= $rid ?></td>
                            <td><?= htmlspecialchars($row['dateTimeFrom']) ?></td>
                            <td><?= htmlspecialchars($row['dateTimeTo']) ?></td>
                            <td>
                                <?php if ($isAvailable): ?>
                                    <span class="badge bg-success">Available</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Not available</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted">
                            No roster shifts found.
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="mt-3 d-flex gap-2">
            <button type="submit" class="btn btn-success">Update</button>
            <a href="index2.php" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>

    <p class="text-muted small mt-4">
        © Gelos Enterprises – Anthony Fastfood FMS – Student: Kenny Luis Colliard
    </p>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function toggleAll(master) {
        const checkboxes = document.querySelectorAll('input[name="rosterIDs[]"]');
        checkboxes.forEach(cb => cb.checked = master.checked);
    }
</script>
</body>
</html>
