<?php
session_start();

$_SESSION = [];
session_unset();
session_destroy();

// Enviar al usuario a la página de logout
header("Location: loggedout.php");
exit;
