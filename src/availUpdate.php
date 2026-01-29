<?php
// availUpdate.php
session_start();

if (!isset($_SESSION['staffID'], $_SESSION['roleID'])) {
    header("Location: login.php");
    exit;
}

$loggedStaffID = (int)$_SESSION['staffID'];
$loggedRoleID  = (int)$_SESSION['roleID'];
$canViewAll    = in_array($loggedRoleID, [3, 4], true); // Supervisor / Manager

require 'database.php';

// ------------------------------------------------------------------
// Validar entrada
// ------------------------------------------------------------------
$staffID   = isset($_POST['staffID']) ? (int)$_POST['staffID'] : 0;
$action    = $_POST['action'] ?? '';
$rosterIDs = $_POST['rosterIDs'] ?? [];

if ($staffID <= 0 || !in_array($action, ['available', 'unavailable'], true)) {
    header("Location: availability.php");
    exit;
}

// Reglas de acceso: si no es supervisor/manager, solo puede tocar su propio staffID
if (!$canViewAll && $staffID !== $loggedStaffID) {
    header("Location: availability.php");
    exit;
}

// Si no seleccionó ningún shift, simplemente volvemos
if (empty($rosterIDs) || !is_array($rosterIDs)) {
    header("Location: availability.php?staffID=" . $staffID);
    exit;
}

// ------------------------------------------------------------------
// Si action = 'available' => INSERT IGNORE
// Si action = 'unavailable' => DELETE
// ------------------------------------------------------------------
if ($action === 'available') {

    $sql = "INSERT IGNORE INTO availability (staffID, rosterID) VALUES (?, ?)";
    $stmt = $connection->prepare($sql);
    if (!$stmt) {
        die("Prepare failed: " . $connection->error);
    }

    foreach ($rosterIDs as $rid) {
        $rid = (int)$rid;
        $stmt->bind_param("ii", $staffID, $rid);
        $stmt->execute();
    }

    $stmt->close();

} else { // 'unavailable'

    $sql = "DELETE FROM availability WHERE staffID = ? AND rosterID = ?";
    $stmt = $connection->prepare($sql);
    if (!$stmt) {
        die("Prepare failed: " . $connection->error);
    }

    foreach ($rosterIDs as $rid) {
        $rid = (int)$rid;
        $stmt->bind_param("ii", $staffID, $rid);
        $stmt->execute();
    }

    $stmt->close();
}

$connection->close();

header("Location: availability.php?staffID=" . $staffID);
exit;
