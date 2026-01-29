<?php
session_start();
require 'database.php';

$email    = "";
$password = "";
$errorMessage = "";

// Si ya está logueado, mandarlo a la home
if (isset($_SESSION['staffID'])) {
    header("Location: index2.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = $_POST['email'] ?? "";
    $password = $_POST['password'] ?? "";

    if (empty($email) || empty($password)) {
        $errorMessage = "Email and password are required.";
    } else {
        // Buscar staff por email
        $sql  = "SELECT staffID, name, roleID, passwordHash FROM staff WHERE email = ?";
        $stmt = $connection->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            // Comparación simple (para el assessment está bien)
            if ($password === $row['passwordHash']) {

                // Guardar datos en la sesión
                $_SESSION['staffID'] = $row['staffID'];
                $_SESSION['name']    = $row['name'];
                $_SESSION['roleID']  = $row['roleID'];

                header("Location: index2.php");
                exit;
            } else {
                $errorMessage = "Invalid email or password.";
            }
        } else {
            $errorMessage = "Invalid email or password.";
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Anthony Fastfood FMS – Login</title>
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
    <style>
        body { background-color: #f8f9fa; }
        .login-container {
            max-width: 420px;
            margin-top: 80px;
        }
    </style>
</head>
<body>

<div class="container d-flex justify-content-center">
    <div class="login-container">
        <div class="text-center mb-4">
            <h1 class="h3">Anthony Fastfood FMS</h1>
        </div>

        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($errorMessage) ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <form method="post">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email address</label>
                        <input
                            type="email"
                            class="form-control"
                            id="email"
                            name="email"
                            value="<?= htmlspecialchars($email) ?>"
                            required
                        >
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input
                            type="password"
                            class="form-control"
                            id="password"
                            name="password"
                            required
                        >
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            Login
                        </button>
                    </div>
                </form>

            </div>
        </div>

        <footer class="text-center mt-3 small text-muted">
            © Gelos Enterprises – Anthony Fastfood FMS – Student: Kenny Luis Colliard
        </footer>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
