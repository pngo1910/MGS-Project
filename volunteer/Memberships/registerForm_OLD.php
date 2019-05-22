<?php
  header('X-UA-Compatible: IE=edge,chrome=1');
  require('../../db/membershipAdminCheck.php');
  require('../../db/memberConnection.php');

  // if session error is set
  if (isset($_SESSION['error'])) {
    // set the session error equal to error variable and then to null ("")
    $error = $_SESSION['error'];
    $_SESSION['error'] = '';
  }

  // if not, set the error variable to null ("")
  else {
    $error = '';
  }
?>
<!DOCTYPE HTML>
<html lang="en-US">
  <head>
    <meta charset="utf-8">
    <title>Member Registration</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width">
    <!-- Place favicon.ico and apple-touch-icon.png in the root directory -->
    <link rel="stylesheet" href="/css/normalize.css">
    <link rel="stylesheet" href="/css/main.css">
    <script src="/js/vendor/modernizr-2.6.2.min.js"></script>
    <script type="text/javascript" src="country.js"></script>
  </head>
  <body>
    <div id="resultsbackground">
      <div id="container" class="home">
        <div id="searchresults">
          <?php require('header.php'); ?>
        </div>
        <!--<div class="toMemberLogin"><a href="index.php">Home</a></div>-->
        <div id="registerMain">
          <h2>Enter The Member's Information</h2><br/>
          <form action="register.php" method="post">
            <p class="errorColor"><?= $error ?></p>
            <h3>Personal info</h3>
            <label class="label" for="fname">First Name*</label>
            <input type="text" class="searching" name="fname" placeholder="First Name" required autofocus><br/>
            <label class="label" for="lname">Last Name*</label>
            <input type="text" class="searching" name="lname" placeholder="Last Name" required><br/>
            <label class="label" for="address">Address</label>
            <input type="text" class="searching" name="address" placeholder="Address"><br/>
            <label class="label" for="city">City</label>
            <input type="text" class="searching" name="city" placeholder="City"><br/>
            <label class="label" for="country">Country</label>
            <select class="searching" name="nation" id="nation"></select><br/>
            <label class="label" for="province" id="provlabel" style="visibility: hidden;"></label>
            <div id="prov" style="display:inline-block;"></div><br/>
            <label class="label" for="postalCode" id="codelabel" style="visibility: hidden;"></label>
            <input type="text" class="searching" name="postalCode" id="codebox" style="visibility: hidden;"><br/>
            <label class="label" for="phone">Phone Number</label>
            <input type="tel" class="searching" name="phone" placeholder="Phone Number"><br/>
            <label class="label" for="email">Email Address</label>
            <input type="email" class="searching" name="email" id="email" placeholder="Email Address"><br/>
            <h3>Generations</h3>
            <input type="radio" class="gen" name="generations" value="1" checked> Emailed
            <input type="radio" class="gen" name="generations" value="2"> Mailed
            <input type="radio" class="gen" name="generations" value="3"> Printed
            <input type="radio" class="gen" name="generations" value="0"> Opt-out<br/>
            <h3>Associate Account</h3>
            <label class="label" for="associate">Member Number</label>
            <input type="text" class="searching" name="associate" placeholder="Member Number"><br/>
            <h3>Branch Membership</h3>
            <?php
              $sql = "SELECT id, name, price FROM branch";
              $stmt = sqlsrv_query($userConn, $sql);
              while ($row = sqlsrv_fetch_array($stmt)) : ?>
                <label class="branch" for="<?= $row['name'] ?>">
                  <?= $row['name'] ?> <?= sprintf("$%.2f", $row['price']) ?>
                  <?php if ($row['id'] == 3) : ?>
                    (<?= sprintf("$%.2f", $row['price'] * 0.80) ?> for associates)
                  <?php endif ?>
                </label>
                <input type="checkbox" name="branch[]" class="branchbox" value="<?= $row['id'] ?>" id="<?= $row['name'] ?>">
                <br><br>
            <?php endwhile ?>
            <input type="reset" class="submit" name="reset" value="Reset">
            <input type="submit" class="submit" name="Submit" value="Register">
          </form>
          <p>* required</p>
        </div>
      </div>
    </div>
  </body>
</html>
