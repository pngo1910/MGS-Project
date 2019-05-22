<?php
  require('../db/adminCheck.php');

  // if user selected yes
  if (isset($_POST['YES'])) {
    // if session pdfTypeCode is set
    if (isset($_SESSION['pdfTypeCode'])) {
      // set session pdfTypeCode variable equal to file variable
      // with extension .pdf
      $file = ($_SESSION['pdfTypeCode'] . '.pdf');
      $folder = $_GET['folder'];

      // check if file exists
      if (file_exists('../PDF/' . $folder . $file)) {
        // unlink the file from folder
        unlink('../PDF/' . $folder . $file);

        //move the file in temp to pdf
        move_uploaded_file("../PDF/tmp/$file", "../PDF/$folder" . $file);

        // // if there is a copy of file, unlink that as well
        if (copy("../PDF/tmp/$file", "../PDF/$folder" . $file)) {
          unlink('../PDF/tmp/' . $file);
        }

        // store the message in session
        $_SESSION['pdfmessage'] = 'File replaced';

        // redirect to the form page
        header('Location: pdfUploadForm.php');
      } else {
        $_SESSION['pdfmessage'] = 'File not found.';
        header('Location: pdfUploadForm.php');
      }
    }
  }

  // if user selects No
  if (isset($_POST['NO'])) {
    //unlink the pdf in the temp folder
    unlink('../PDF/tmp/' . $_SESSION['pdfTypeCode'] . '.pdf');
    // store a different message in the session
    $_SESSION['pdfmessage'] = 'File not replaced.';
    // redirect to the form page
    header('Location: pdfUploadForm.php');
  }
?>
