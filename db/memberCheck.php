<?php
  require('loginCheck.php');

  if (!$_SESSION['verified'] ) {
    header('location:/myAccount/');
  }
?>
