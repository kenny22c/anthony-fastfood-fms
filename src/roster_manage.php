<?php
session_start();
if (!isset($_SESSION['staffID'])) {
    header("Location: login.php");
    exit;
}

$staffID = (int)$_SESSION['staffID'];
$name    = $_SESSION['name'] ?? '';
$roleID  = (int)$_SESSION['roleID'];

// Solo Supervisor (3) y Manager (4) pueden gestionar rosters
$canManageStaff = in_array($roleID, [3, 4], true);
if (!$canManageStaff) {
    header("Location: availability.php");
    exit;
}

require 'database.php';

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

$success = '';
$error   = '';

// Normalizar datetime-local de HTML a formato MySQL
$normalizeDateTime = function (?string $value): ?string {
    if (!$value) return null;
    $value = str_replace('T', ' ', $value);
    if (strlen($value) === 16) { // yyyy-mm-dd hh:mm
        $value .= ':00';
    }
    return $value;
};

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // CREAR NUEVO ROSTER
    if ($action === 'create') {
        $from = $normalizeDateTime($_POST['dateTimeFrom'] ?? '');
        $to   = $normalizeDateTime($_POST['dateTimeTo'] ?? '');

        if ($from && $to) {
            $sql  = "INSERT INTO roster (dateTimeFrom, dateTimeTo) VALUES (?, ?)";
            $stmt = $connection->prepare($sql);
            if ($stmt) {
                $stmt->bind_param('ss', $from, $to);
                if ($stmt->execute()) {
                    $success = 'New roster shift created successfully.';
                } else {
                    $error = 'Error creating roster: ' . htmlspecialchars($stmt->error);
                }
                $stmt->close();
            } else {
                $error = 'Error preparing statement for insert.';
            }
        } else {
            $error = 'Please provide valid From and To date/time values.';
        }

    // ACTUALIZAR ROSTER
    } elseif ($action === 'update') {
        $rosterID = (int)($_POST['rosterID'] ?? 0);
        $from     = $normalizeDateTime($_POST['dateTimeFrom'] ?? '');
        $to       = $normalizeDateTime($_POST['dateTimeTo'] ?? '');

        if ($rosterID > 0 && $from && $to) {
            $sql  = "UPDATE roster SET dateTimeFrom = ?, dateTimeTo = ? WHERE rosterID = ?";
            $stmt = $connection->prepare($sql);
            if ($stmt) {
                $stmt->bind_param('ssi', $from, $to, $rosterID);
                if ($stmt->execute()) {
                    $success = 'Roster shift updated successfully.';
                } else {
                    $error = 'Error updating roster: ' . htmlspecialchars($stmt->error);
                }
                $stmt->close();
            } else {
                $error = 'Error preparing statement for update.';
            }
        } else {
            $error = 'Invalid roster ID or date/time values.';
        }

    // ELIMINAR ROSTER
    } elseif ($action === 'delete') {
        $rosterID = (int)($_POST['rosterID'] ?? 0);

        if ($rosterID > 0) {
            $sql  = "DELETE FROM roster WHERE rosterID = ?";
            $stmt = $connection->prepare($sql);

            if ($stmt) {
                $stmt->bind_param('i', $rosterID);
                if ($stmt->execute()) {
                    $success = 'Roster shift deleted successfully.';
                } else {
                    $error = 'Error deleting roster: ' . htmlspecialchars($stmt->error);
                }
                $stmt->close();
            } else {
                $error = 'Error preparing delete statement.';
            }
        } else {
            $error = 'Invalid roster ID.';
        }
    }
}

/*
 * FILTROS POR FECHA (GET)
 * filterFrom y filterTo vienen como yyyy-mm-dd desde inputs type="date"
 */
$filterFrom = $_GET['filterFrom'] ?? '';
$filterTo   = $_GET['filterTo'] ?? '';

/*
 * ORDENAMIENTO POR COLUMNAS (GET)
 * sort: id | from | to
 * dir : asc | desc
 */
$sort = $_GET['sort'] ?? 'from';
$dir  = strtolower($_GET['dir'] ?? 'asc');

$allowedSorts = ['id', 'from', 'to'];
if (!in_array($sort, $allowedSorts, true)) {
    $sort = 'from';
}
$dir = $dir === 'desc' ? 'desc' : 'asc';

// Mapear sort a columnas reales
switch ($sort) {
    case 'id':
        $orderColumn = 'rosterID';
        break;
    case 'to':
        $orderColumn = 'dateTimeTo';
        break;
    case 'from':
    default:
        $orderColumn = 'dateTimeFrom';
        break;
}

$orderBy = " ORDER BY $orderColumn " . strtoupper($dir);

// Cargar rosters (con filtros y sort)
$rosters = [];

$sql = "SELECT rosterID, dateTimeFrom, dateTimeTo FROM roster WHERE 1=1";
$params = [];
$types  = '';

if ($filterFrom !== '') {
    $sql      .= " AND dateTimeFrom >= ?";
    $types    .= 's';
    $params[]  = $filterFrom . ' 00:00:00';
}

if ($filterTo !== '') {
    $sql      .= " AND dateTimeFrom <= ?";
    $types    .= 's';
    $params[]  = $filterTo . ' 23:59:59';
}

$sql .= $orderBy;

if ($stmt = $connection->prepare($sql)) {
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
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
    <title>Roster management – Anthony Fastfood FMS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
</head>
<body class="bg-light" style="padding-top: 70px;">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="index2.php">Anthony Fastfood FMS</a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#mainNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="index.php">Staff list</a></li>
                <li class="nav-item"><a class="nav-link" href="availability.php">Availability</a></li>
                <li class="nav-item"><a class="nav-link" href="profile.php">My profile</a></li>
            </ul>

            <span class="navbar-text text-light me-3">
                <?= htmlspecialchars($name) ?> –
                <span class="text-info"><?= htmlspecialchars(roleName($roleID)) ?></span>
            </span>
            <a class="btn btn-outline-light btn-sm" href="logout.php">Logout</a>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <h1 class="h3 mb-2">Roster management</h1>
    <p class="text-muted mb-4">
        Create, update, and delete roster shifts for all staff. Changes here will be reflected in the availability screen.
    </p>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- Crear nuevo roster -->
    <div class="card mb-4">
        <div class="card-header">
            Create new roster shift
        </div>
        <div class="card-body">
            <form method="post" class="row g-3 align-items-end">
                <input type="hidden" name="action" value="create">

                <div class="col-md-4">
                    <label for="dateTimeFrom" class="form-label">From</label>
                    <input type="datetime-local"
                           name="dateTimeFrom"
                           id="dateTimeFrom"
                           class="form-control"
                           required>
                </div>

                <div class="col-md-4">
                    <label for="dateTimeTo" class="form-label">To</label>
                    <input type="datetime-local"
                           name="dateTimeTo"
                           id="dateTimeTo"
                           class="form-control"
                           required>
                </div>

                <div class="col-md-4 d-grid">
                    <button type="submit" class="btn btn-primary">
                        Create roster
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Filtros de fecha para la tabla de rosters -->
    <h2 class="h5 mb-3">Existing roster shifts</h2>

    <form method="get" class="row g-3 align-items-end mb-3">
        <div class="col-md-3">
            <label for="filterFrom" class="form-label">Filter from</label>
            <input type="date"
                   id="filterFrom"
                   name="filterFrom"
                   class="form-control"
                   value="<?= htmlspecialchars($filterFrom) ?>">
        </div>

        <div class="col-md-3">
            <label for="filterTo" class="form-label">Filter to</label>
            <input type="date"
                   id="filterTo"
                   name="filterTo"
                   class="form-control"
                   value="<?= htmlspecialchars($filterTo) ?>">
        </div>

        <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">
        <input type="hidden" name="dir" value="<?= htmlspecialchars($dir) ?>">

        <div class="col-md-3 d-grid">
            <button type="submit" class="btn btn-outline-primary">
                Apply filter
            </button>
        </div>

        <div class="col-md-3 d-grid">
            <a href="roster_manage.php" class="btn btn-outline-secondary">
                Clear filters
            </a>
        </div>
    </form>

    <?php
    // helpers para los links de sort
    $baseSortParams = [
        'filterFrom' => $filterFrom,
        'filterTo'   => $filterTo,
    ];

    $arrow = function (string $column) use ($sort, $dir): string {
        if ($sort !== $column) return '';
        return $dir === 'asc' ? ' ▲' : ' ▼';
    };

    $sortLink = function (string $column) use ($sort, $dir, $baseSortParams): string {
        $nextDir = ($sort === $column && $dir === 'asc') ? 'desc' : 'asc';
        $params  = array_merge($baseSortParams, ['sort' => $column, 'dir' => $nextDir]);
        return 'roster_manage.php?' . htmlspecialchars(http_build_query($params));
    };
    ?>

    <!-- Tabla de rosters existentes -->
    <div class="table-responsive">
        <table class="table table-striped table-bordered align-middle text-center">
            <thead class="table-dark">
            <tr>
                <th style="width: 60px;">
                    <a href="<?= $sortLink('id') ?>" class="link-light text-decoration-none">
                        ID<?= $arrow('id') ?>
                    </a>
                </th>
                <th style="width: 30%;">
                    <a href="<?= $sortLink('from') ?>" class="link-light text-decoration-none">
                        From<?= $arrow('from') ?>
                    </a>
                </th>
                <th style="width: 30%;">
                    <a href="<?= $sortLink('to') ?>" class="link-light text-decoration-none">
                        To<?= $arrow('to') ?>
                    </a>
                </th>
                <th style="width: 40%;">Edit / Delete</th>
            </tr>
            </thead>
            <tbody>
            <?php if ($rosters): ?>
                <?php foreach ($rosters as $r): ?>
                    <?php
                    $rid     = (int)$r['rosterID'];
                    $fromVal = htmlspecialchars(str_replace(' ', 'T', substr($r['dateTimeFrom'], 0, 16)));
                    $toVal   = htmlspecialchars(str_replace(' ', 'T', substr($r['dateTimeTo'], 0, 16)));
                    ?>
                    <tr>
                        <td><?= $rid ?></td>
                        <td><?= htmlspecialchars($r['dateTimeFrom']) ?></td>
                        <td><?= htmlspecialchars($r['dateTimeTo']) ?></td>

                        <td>
                            <form method="post" class="row g-2 align-items-center justify-content-center">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="rosterID" value="<?= $rid ?>">

                                <div class="col-md-4 col-12">
                                    <input type="datetime-local"
                                           name="dateTimeFrom"
                                           class="form-control form-control-sm"
                                           value="<?= $fromVal ?>">
                                </div>

                                <div class="col-md-4 col-12">
                                    <input type="datetime-local"
                                           name="dateTimeTo"
                                           class="form-control form-control-sm"
                                           value="<?= $toVal ?>">
                                </div>

                                <div class="col-md-2 col-6 d-grid">
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        Save
                                    </button>
                                </div>

                                <div class="col-md-2 col-6 d-grid">
                                    <button type="submit"
                                            name="action"
                                            value="delete"
                                            onclick="return confirm('Are you sure you want to delete this roster shift?');"
                                            class="btn btn-danger btn-sm">
                                        Delete
                                    </button>
                                </div>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" class="text-muted text-center">No roster shifts found for the selected filters.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <p class="text-muted small mt-4">
        © Gelos Enterprises – Anthony Fastfood FMS – Student: Kenny Luis Colliard
    </p>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
