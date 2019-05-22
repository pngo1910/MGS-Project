
<!DOCTYPE html>
<html lang="en-us">
  <head>
    <meta charset="utf-8">
    <title>Database Error</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width">
    <link rel="stylesheet" href="/css/normalize.css">
    <link rel="stylesheet" href="/css/main.css">
    <script src="/js/vendor/modernizr-2.6.2.min.js"></script>
  </head>
  <body>
    <div id="homeBackgroundFree"></div>
    <div id="container" class="home">

      <?php
        if (isset($_GET['s']))
          session_name(base64_decode($_GET['s']));

        session_start();
        require('header.php');
      ?>

      <h1>Database Error</h1>

      <?php if (isset($_SESSION['db-error'])) : ?>
        <h4>The following errors occured while running a database query:</h4>
        <ul style="list-style: none">
          <?php foreach ($_SESSION['db-error']['error'] as $error) : ?>
            <li><?= $error['code'] ?>:
                <?= preg_replace('/(\[.+?\])*/', '', $error['message']) ?></li>
          <?php endforeach; ?>
        </ul>

        <h5><?= $_SESSION['db-error']['file'] ?> ::
            <?= $_SESSION['db-error']['line'] ?></h5>
        <?php unset($_SESSION['db-error']) ?>
      <?php endif; ?>
    </div>
  </body>
</html>
