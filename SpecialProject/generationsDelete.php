<?php
  require('../db/adminCheck.php');

  $file = $_POST['file'];

  // if user selected yes
  if (isset($_POST['yes'])) {
    if (file_exists('../generations/' . $file)) {
      unlink('../generations/' . $file);
      move_uploaded_file('../generations/tmp/' . $file, '../generations/' . $file);

      // if there is a copy of file, unlink that as well
      if (copy('../generations/tmp/' . $file, '../generations/' . $file)) {
        unlink('../generations/tmp/' . $file);
      }

      $_SESSION['genmessage'] = 'File replaced.';
      header('location: pdfUploadForm.php');
    } else {
      $_SESSION['pdfmessage'] = 'File not found.';
      header('location: pdfUploadForm.php');
    }
  }

  if (isset($_POST['no'])) {
    unlink('../generations/tmp/' . $file);
    $_SESSION['genmessage'] = 'File not replaced.';
    header('location: pdfUploadForm.php');
  }
?>
