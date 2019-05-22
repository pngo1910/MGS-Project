<?php
  require('memberCheck.php');

  if ($_SESSION['access'] < 3){
    header('location: /member/');
  }
?>
