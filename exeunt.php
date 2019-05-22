
<!doctype html>
<html>
  <head>
    <meta charset="utf-8">
    <title>Manitoba Genealogical Society</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width">
    <link rel="stylesheet" href="css/normalize.css">
    <link rel="stylesheet" href="css/main.css">
    <script src="/js/vendor/modernizr-2.6.2.min.js"></script>

    <?php if (isset($_POST['sessionname'])) : ?>
      <?php
        $name = base64_decode($_POST['sessionname']);
        session_name($name);
        session_start();
        session_destroy();

        unset($_COOKIE[$name]);
        setcookie($name, null, -1, '/');
      ?>

      <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {
          ['selectedlink', 'subheader'].map(function(_) { localStorage.removeItem(_); });
          window.location = '/index.php';
        }, false);
      </script>
    <?php endif ?>
  </head>
  <body>
    <div id="resultsbackground"></div>
    <div id="container" class="home">
      <?php require('header.php'); ?>
      <div id="searchresults">
      </div>
    </div>
  </body>
</html>
