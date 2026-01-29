<?php
session_start();
if (!isset($_SESSION['staffID'])) {
    header("Location: login.php");
    exit;
}

$loggedStaffID = $_SESSION['staffID'];
$loggedName    = $_SESSION['name'];
$loggedRoleID  = $_SESSION['roleID'];

// Supervisores/Managers pueden gestionar availability de otros
$canManageAll = in_array($loggedRoleID, [3, 4], true);

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

// rosterID es obligatorio
$rosterID = filter_input(INPUT_GET, 'rosterID', FILTER_VALIDATE_INT);
if (!$rosterID) {
    header("Location: availability.php");
    exit;
}

// staffID por GET solo para Supervisor/Manager, sino uso el propio
$selectedStaffID = null;
if ($canManageAll) {
    $selectedStaffID = filter_input(INPUT_GET, 'staffID', FILTER_VALIDATE_INT);
    if (!$selectedStaffID) {
        $selectedStaffID = $loggedStaffID;
    }
} else {
    $selectedStaffID = $loggedStaffID;
}

// 1) Datos del turno
$sql  = "SELECT dateTimeFrom, dateTimeTo FROM roster WHERE rosterID = ?";
$stmt = $connection->prepare($sql);
$stmt->bind_param("i", $rosterID);
$stmt->execute();
$res = $stmt->get_result();
$roster = $res->fetch_assoc();
$stmt->close();

if (!$roster) {
    header("Location: availability.php");
    exit;
}

// 2) Lista de staff elegible para este roster (solo Supervisor/Manager)
$staffOptions = [];
if ($canManageAll) {
    $sql = "
        SELECT s.staffID, s.name
        FROM staff s
        JOIN rosterRole rr ON s.roleID = rr.roleID
        WHERE rr.rosterID = ?
        ORDER BY s.staffID
    ";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("i", $rosterID);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $staffOptions[] = $row;
    }
    $stmt->close();

    // Si no vino staffID y hay lista, tomar el primero
    if (!$selectedStaffID && !empty($staffOptions)) {
        $selectedStaffID = (int)$staffOptions[0]['staffID'];
    }
} else {
    $staffOptions[] = [
        'staffID' => $loggedStaffID,
        'name'    => $loggedName
    ];
}

// Si seguimos sin staff válido → volver
if (!$selectedStaffID) {
    header("Location: availability.php");
    exit;
}

// 3) Comprobar si actualmente está disponible
$isAvailable = false;
$sql = "SELECT 1 FROM availability WHERE staffID = ? AND rosterID = ? LIMIT 1";
$stmt = $connection->prepare($sql);
$stmt->bind_param("ii", $selectedStaffID, $rosterID);
$stmt->execute();
$stmt->bind_result($dummy);
if ($stmt->fetch()) {
    $isAvailable = true;
}
$stmt->close();

// Nombre del staff seleccionado
$selectedStaffName = '';
foreach ($staffOptions as $opt) {
    if ((int)$opt['staffID'] === (int)$selectedStaffID) {
        $selectedStaffName = $opt['name'];
        break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Confirm availability – Anthony Fastfood FMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding-top: 70px;
            background-color: #f5f5f5;
        }
        .page-card {
            background-color: #ffffff;
            padding: 20px 24px;
            border-radius: 0.5rem;
            box-shadow: 0 0.25rem 0.5rem rgba(0,0,0,0.05);
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="index2.php">Anthony Fastfood FMS</a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#mainNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <?php if ($canManageAll): ?>
                    <li class="nav-item"><a class="nav-link" href="index.php">Staff list</a></li>
                    <li class="nav-item"><a class="nav-link" href="create.php">Create staff</a></li>
                <?php endif; ?>
                <li class="nav-item"><a class="nav-link active" href="availability.php">Availability</a></li>
                <li class="nav-item"><a class="nav-link" href="profile.php">My profile</a></li>
            </ul>

            <span class="navbar-text text-light me-3 fw-semibold">
                <?= htmlspecialchars($_SESSION['name']) ?>
                <span class="text-info">– <?= htmlspecialchars(roleName((int)$_SESSION['roleID'])) ?></span>
            </span>
            <a class="btn btn-outline-light btn-sm" href="logout.php">Logout</a>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <div class="page-card mx-auto" style="max-width: 900px;">
        <h1 class="h4 mb-4">Confirm availability</h1>

        <form action="availUpdate.php" method="post">
            <input type="hidden" name="rosterID" value="<?= htmlspecialchars($rosterID) ?>">
            <input type="hidden" name="staffID" id="staffID_field" value="<?= htmlspecialchars($selectedStaffID) ?>">

            <?php if ($canManageAll): ?>
                <div class="mb-3">
                    <label for="staffSelect" class="form-label">Select staff</label>
                    <select id="staffSelect" class="form-select"
                            onchange="onStaffChange(this.value)">
                        <?php foreach ($staffOptions as $opt): ?>
                            <option value="<?= htmlspecialchars($opt['staffID']) ?>"
                                <?= (int)$opt['staffID'] === (int)$selectedStaffID ? 'selected' : '' ?>>
                                <?= htmlspecialchars($opt['staffID'] . ' - ' . $opt['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php else: ?>
                <p><strong>Staff:</strong> <?= htmlspecialchars($selectedStaffName ?: $loggedName) ?></p>
            <?php endif; ?>

            <div class="mb-3">
                <p class="mb-1"><strong>Shift details</strong></p>
                <p class="mb-0">
                    <span class="badge bg-secondary me-2">Start</span>
                    <?= htmlspecialchars($roster['dateTimeFrom']) ?>
                </p>
                <p class="mb-0">
                    <span class="badge bg-secondary me-2">End</span>
                    <?= htmlspecialchars($roster['dateTimeTo']) ?>
                </p>
            </div>

            <div class="mb-3">
                <p class="mb-1"><strong>Current status</strong></p>
                <?php if ($isAvailable): ?>
                    <span class="badge bg-success">Available</span>
                    <small class="text-muted ms-2">Uncheck the box below to mark as not available.</small>
                <?php else: ?>
                    <span class="badge bg-danger">Not available</span>
                    <small class="text-muted ms-2">Tick the box below to mark as available.</small>
                <?php endif; ?>
            </div>

            <table class="table table-striped table-bordered align-middle text-center mb-4">
                <thead class="table-dark">
                    <tr>
                        <th scope="col">Select</th>
                        <th scope="col">Start</th>
                        <th scope="col">End</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <input type="checkbox" name="available" value="1"
                                   <?= $isAvailable ? 'checked' : '' ?>>
                        </td>
                        <td><?= htmlspecialchars($roster['dateTimeFrom']) ?></td>
                        <td><?= htmlspecialchars($roster['dateTimeTo']) ?></td>
                    </tr>
                </tbody>
            </table>

            <button type="submit" class="btn btn-success">Update</button>
            <a href="availability.php?staffID=<?= urlencode($selectedStaffID) ?>"
               class="btn btn-secondary ms-2">Cancel</a>
        </form>
    </div>
</div>

<script>
    // Cuando cambia el staff en el selector, recarga la página conservando rosterID
    function onStaffChange(newStaffID) {
        const rosterID = <?= json_encode($rosterID) ?>;
        window.location.href = "availability2.php?rosterID=" + encodeURIComponent(rosterID)
            + "&staffID=" + encodeURIComponent(newStaffID);
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
