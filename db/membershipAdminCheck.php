<?php
  require('memberCheck.php');



  if ($_SESSION['access'] < 4){
    header('location: /member/');
  }
?>
