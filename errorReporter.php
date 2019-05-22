
<?php
  /*
    this file must be required before database files, except loginscripts,
    also the session must be started before requiring this file.
  */

  function errorReport ($errors, $file, $line) {
    $_SESSION['db-error'] = array(
      'error' => $errors,
      'file'  => $file,
      'line'  => $line );

    $name = base64_encode(session_name());
    die(header("location: /error.php?s=$name"));
  }
?>
