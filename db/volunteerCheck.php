<?php
  require('memberCheck.php');

  if ($_SESSION['access'] < 2) {
    header('location:/member/');
  }
?>
