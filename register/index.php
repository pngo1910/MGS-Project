<?php
  header('X-UA-Compatible: IE=edge,chrome=1');

  // start the session
  session_name('register');
  session_start();

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
  </head>
  <body>
    <div id="resultsbackground">
      <div id="container" class="home">
        <div id="searchresults">
          <?php require('header.php'); ?>
        </div>
        <!--<div class="toMemberLogin"><a href="index.php">Home</a></div>-->
        <div id="registerMain">
          <h2>Enter Your Information</h2><br/>
          <p>This is step one. You need to either create an online account or log into an existing one in order to enter your personal information. If you are already a member or were one in the past, but never created an online account you still need to set one up below. Members who created a MANI account in the past please log into the system to pay membership under the MyAccount button. When your membership expired your MANI account is still accessible to allow you to renew.</p>
          <form action="maniRegister.php" method="post">
            <p class="errorColor"><?= $error ?></p>
            <fieldset id="mani">
              <label class="label" for="uname">Username*</label>
              <!-- <input type="text" class="searching" name="uname" id="uname" placeholder="Username" required autofocus><br/> -->
              <input type="text" class="searching" name="uname" id="uname" placeholder="Username" required autofocus <?php if(isset($_SESSION['username'])) : ?><?= "value= '";?><?= $_SESSION['username'];?><?= "'";?><?php endif ?>><br/>
              <label class="label" for="email">Email Address*</label>
              <!-- <input type="email" class="searching" name="email" id="email" placeholder="Email Address" required><br/> -->
              <input type="email" class="searching" name="email" id="email" placeholder="Email Address" required <?php if(isset($_SESSION['email'])) : ?><?= "value= '";?><?= $_SESSION['email'];?><?= "'";?><?php endif ?>><br/>
              <label class="label" for="password">Password*</label>
              <!-- <input type="password" class="searching" name="password" id="password" placeholder="Password" required><br/> -->
              <input type="password" class="searching" name="password" id="password" placeholder="Password" required <?php if(isset($_SESSION['password'])) : ?><?= "value= '";?><?= $_SESSION['password'];?><?= "'";?><?php endif ?>><br/>
              <label class="label" for="confirmpassword">Confirm Password*</label>
              <!-- <input type="password" class="searching" name="confirmpassword" id="confirmpassword" placeholder="Confirm Password" required><br/> -->
              <input type="password" class="searching" name="confirmpassword" id="confirmpassword" placeholder="Confirm Password" required <?php if(isset($_SESSION['password'])) : ?><?= "value= '";?><?= $_SESSION['password'];?><?= "'";?><?php endif ?>><br/>
              <h3>Existing <strong>MGS</strong> Membership**</h3>
              <label class="membership">Do you already have a <strong>MGS</strong> membership Number?</label>
              <input type="radio" name="membership" id="yes" value="yes" checked>
              <label class="membership" for="yes">Yes</label>
              <!-- <input type="radio" name="membership" id="no" value="no"> -->
              <input type="radio" name="membership" id="no" value="no" <?php if(isset($_SESSION['existingAccount']) && !($_SESSION['existingAccount'])) : ?><?= "checked";?><?php endif ?>>
              <label class="membership" for="no">No</label><br/><br/>
              <!-- <input type="reset" class="submit" name="reset" value="Reset"> -->
              <input type="submit" class="submit" name="reset" value="Reset">
              <input type="submit" class="submit" name="Submit" value="Next >">
            </fieldset>
          </form>
          <p>* required</p>
          <p>** If you already have a MGS membership number and select the NO option you'll be <strong> required to purchase a new membership</strong> and create a new account. This will be separate from your previous account and it will have a different membership number and payment period.</p>
          <p>Check you email for a payment notification from PayPal before you  asume that your registration did not go through.</p> 
          <p>If you experience problems email membership@mbgenealogy.com.</p>
        </div>
      </div>
    </div>
  </body>
</html>