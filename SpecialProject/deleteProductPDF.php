<?php
  require('../db/adminCheck.php');

  // if user selected yes
  if (isset($_POST['YES'])) {
    // if file is set
    if (isset($_GET['file'])) {
      // set session pdfTypeCode variable equal to file variable
      // with extension .pdf
      $file = ($_GET['file']);

      // check if file exists
      if (file_exists('../PDF/Store/' . $file)) {
        // unlink the file from folder
        unlink('../PDF/Store/' . $file);

        //move the file in temp to pdf
        move_uploaded_file('../PDF/tmp/' . $file, '../PDF/Store/' . $file);

        // // if there is a copy of file, unlink that as well
        if (copy('../PDF/tmp/' . $file, '../PDF/Store/' . $file)) {
          unlink('../PDF/tmp/' . $file);
        }

        // store the message in session
        $_SESSION['productmessage'] = 'File replaced';

        // redirect to the form page
        header('Location: pdfUploadForm.php');
      } else {
        $_SESSION['productmessage'] = 'File not found.';
        header('Location: pdfUploadForm.php');
      }
    }
  }

  // if user selects No
  if (isset($_POST['NO'])) {
    //unlink the pdf in the temp folder
    unlink('../PDF/tmp/' . $_GET['file']);
    // store a different message in the session
    $_SESSION['productmessage'] = 'File not replaced.';
    // redirect to the form page
    header('Location: pdfUploadForm.php');
  }
?>
