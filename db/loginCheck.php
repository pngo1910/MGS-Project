<?php
  session_name('login');
  session_start();

  if (!isset($_SESSION['uname']) || !isset($_SESSION['pword'])) {
    $_SESSION['error'] = 'Connection Expired, please log in again.';
    $name = session_name();
    session_destroy();

    unset($_COOKIE[$name]);
    setcookie($name, null, -1, '/');
    die(header('location: /login.php'));
  }
?>
