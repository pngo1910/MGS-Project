<?php
  header('X-UA-Compatible: IE=edge,chrome=1');

  require('../db/loginCheck.php');
  require('../db/memberConnection.php');
  require('../errorReporter.php');
  require('updateChange.php');
  //add for send email
  require('confirmation_mail.php');

  // if session error is set
  if (!isset($_SESSION['error'])) $_SESSION['error'] = '';
  $sql = "SELECT MemberNum, AccessLevel FROM Members WHERE Username = ?";
  $stmt = sqlsrv_query($userConn, $sql, array($_SESSION['uname']));
  if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
  $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

  $memberNum = $row['MemberNum'];
  $accessLevel = $row['AccessLevel'];

  // if form is submitted
  if(isset($_POST['Update'])){
    $changes = array();
    /*add for send email*/
    $oldValues = array();
    $newValues = array();
    /*add for send email*/
    // if a new username was entered
    if (isset($_POST['newusername']) && $_POST['newusername'] != ''){

      // make sure the new username isn't the same as the old one
      if ($_POST['newusername'] != $_SESSION['uname']) {
        $newusername = $_POST['newusername'];
        $numrows = 0;

        $sql = "SELECT Username FROM Members WHERE Username = ?";
        $stmt = sqlsrv_query($userConn, $sql, array($newusername));
        while (sqlsrv_fetch_array($stmt)) $numrows++;

        // if the new username is not in the database
        if ($numrows == 0) {
          /*add for send email*/
          $oldValues[] = $_SESSION['uname'];
          $newValues[] = $newusername;
          /*add for send email*/
          $sql = 'UPDATE Members SET Username = ? WHERE MemberNum = ?';
          $values = array($newusername, $memberNum);
          $stmt = sqlsrv_query($userConn, $sql, $values);

          if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

          $_SESSION['uname'] = $newusername;

          $changes[] = 'username';
        } else {
          $_SESSION['error'] = 'That username is already used.';
        }
      } else {
        $_SESSION['error'] = 'The new username is the same as your current username.';
      }
    }

    if (isset($_POST['gen']) && $_POST['gen'] != '') {
      /*add for send email*/
      $sql = "SELECT Name FROM Membership, Generations WHERE Membership.Generations = Generations.ID AND MemberNum = ?";
      $stmt = sqlsrv_query($userConn, $sql, array($memberNum));
      if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
      $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

      $oldValues[] = $row['Name'];

      $sql = "SELECT Name FROM Generations WHERE ID = ?";
      $stmt = sqlsrv_query($userConn, $sql, array($_POST['gen']));
      if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
      $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
      $newValues[] = $row['Name'];
      /*add for send email*/
      $sql = "UPDATE Membership SET Generations = ? WHERE MemberNum = ?";
      $values = array($_POST['gen'], $memberNum);
      $stmt = sqlsrv_query($userConn, $sql, $values);

      if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

      $changes[] = 'generations';
    }

    // if new password and old password have been entered
    if(isset($_POST['newPass']) && isset($_POST['oldPass']) && $_POST['newPass'] != '' && $_POST['oldPass'] != ''){

      // check if old password entered matches the password in the session from index.php
      // if not set session error variable to an error message in else statement
      if (hash("sha512",$_POST['oldPass']) == $_SESSION['pword']){
        //  set variabled to the entered variables in the form
        $newpassword = $_POST['newPass'];
        $oldpassword = $_POST['oldPass'];
        $confirm_password = $_POST['confirmPass'];

        //  if new password does not match confirm password
        if($newpassword != $confirm_password){

          // set the session error to an error message
          $_SESSION['error'] = "New password does not match the confirm password.";
        }
        // if matched
        else {
          // encrypt the new password
          $encrypted = hash("sha512", $newpassword);

          // sql statement to update the new password
          $sql = 'UPDATE Members SET Password = ? WHERE MemberNum = ?';
          $values = array($encrypted, $memberNum);

          // execute the query
          $stmt = sqlsrv_query($userConn, $sql, $values);

          // if query does not get executed, kill the script and show errors
          if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

          // destroy the session
          session_destroy();
          // unset the session
          session_unset();
          // locate to the index page
          header('location:../../login.php');
          die();
        }
      }
      // if old password does not match the session password from index.php
      // set session error variable to an error message in else statement
      else{
          $_SESSION['error'] = "The old password you have entered was incorrect. Please try again.";
      }
    }

    updateChange($memberNum, $changes, $userConn);

    /*add for send email*/
    $sql = "SELECT Email FROM MemberInfo WHERE MemberNum = ?";
    $stmt = sqlsrv_query($userConn, $sql, array($memberNum));
    if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    $oldEmail = $row["Email"];
    send_confirm_email($oldValues, $newValues, $changes, $oldEmail);
    /*add for send email*/
  }
  // sql statement to select from membership table using membernum from previous query
  $sql = "SELECT Generations, TypeOfMember, YearJoined, Expiry FROM Membership WHERE MemberNum = ?";
  // execute the query
  $stmt = sqlsrv_query($userConn, $sql, array($memberNum), array('Scrollable' => 'static'));
  // if query does not get executed, print the errors and kill the script
  if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
  // fetch the row from the executed query
  $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC, SQLSRV_SCROLL_ABSOLUTE);
  $colour = isset($row['Expiry'])? date_format($row['Expiry'], 'U') < time() ? 'red' : 'black': 'black';

  $sql = "SELECT Name FROM TypeOfMember WHERE ID = ?";
  $stmt = sqlsrv_query($userConn, $sql, array($row['TypeOfMember']), array('Scrollable' => 'static'));
  if (sqlsrv_fetch($stmt) === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
  $accountType = sqlsrv_get_field($stmt, 0);

  $sql = "SELECT Name FROM AccessLevel WHERE ID = ?";
  $stmt = sqlsrv_query($userConn, $sql, array($accessLevel), array('Scrollable' => 'static'));
  if (sqlsrv_fetch($stmt) === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
  $access = sqlsrv_get_field($stmt, 0);

  $sql = "SELECT 1 FROM Researchers WHERE MemberNum = ?";
  $stmt = sqlsrv_query($userConn, $sql, array($memberNum), array('Scrollable' => 'static'));
  if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
  $researchcheck = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC, SQLSRV_SCROLL_ABSOLUTE);
  $researcher = $researchcheck === null? false: true;

  $sql = "SELECT Name FROM Generations WHERE ID = ?";
  $stmt = sqlsrv_query($userConn, $sql, array($row['Generations']), array('Scrollable' => 'static'));
  if (sqlsrv_fetch($stmt) === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
  $generations = sqlsrv_get_field($stmt, 0);

  $sql = "SELECT Credit FROM Membership WHERE MemberNum = ?";
  $stmt = sqlsrv_query($userConn, $sql, array($memberNum), array('Scrollable' => 'static'));
  if (sqlsrv_fetch($stmt) === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
  $credit = sqlsrv_get_field($stmt, 0);

  $associate = false;
?>

<!DOCTYPE HTML>
<html lang="en-US">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title>MGS Member</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width">
    <link rel="stylesheet" href="/css/normalize.css">
    <link rel="stylesheet" href="/css/main.css">
    <link rel="stylesheet" href="/DataTables-1.10.6/media/css/jquery.dataTables.css">
    <script src="/js/vendor/modernizr-2.6.2.min.js"></script>
  </head>
  <body>
    <div id="resultsbackground">
      <div id="container" class="home">
        <div id="searchresults">
          <?php require('../header.php'); ?>
        </div>
        <div class="account">
          <h3>Account Info</h3>
          <form action="accountInfo.php" method="post">
            <p class="errorColor"><?= $_SESSION['error'] ?></p>
              <?php $_SESSION['error'] = "";?>
            <label class="label" for="newusername">Username:</label>
            <label class="info" for="newusername"><?php echo $_SESSION['uname']; ?></label>
            <input type="text" class="searching" name="newusername" id="newusername" placeholder="new Username" autofocus="autofocus"><br/>
            <label class="label" for="num">Member Number:</label>
            <label class="info" for="num"><?php echo $memberNum; ?></label><br/>
            <label class="label" for="type">Account Type:</label>
            <label class="info" for="type"><?php echo $researcher? $row['TypeOfMember'].' ('.$accountType.')'.'/Researcher': $row['TypeOfMember'].' ('.$accountType.')'; ?></label><br/>
            <label class="label" for="access">Access Level:</label>
            <label class="info" for="access"><?php echo $accessLevel.' ('.$access.')'; ?></label><br/>
            <label class="label" for="access">Credits: </label>
            <label class="info" for="access"><?php echo $credit; ?></label><br/>
            <label class="label" for="gen">Generations:</label>
            <label class="info" for="gen"><?php echo $generations; ?></label>
            <?php if($_SESSION['type'] !== 2): ?>
              <select name="gen" id="gen" class="searching">
                <option value="">Change Subscription</option>
                <option value="0">None</option>
                <option value="1">Mailed</option>
                <option value="2">Emailed</option>
                <option value="3">Printed</option>
              </select>
            <?php endif; ?>
            </br>
            <label class="label" for="joined">Date Joined:</label>
            <label class="info" for="joined"><?php echo isset($row['YearJoined'])? $row['YearJoined']->format('Y-m-d'): ''; ?></label><br/>
            <label class="label" for="expiry">Expiry Date:</label>
            <label class="info" for="expiry" style="color:<?php echo $colour; ?>;"><?php echo isset($row['Expiry'])? $row['Expiry']->format('Y-m-d'): ''; ?></label><br/>
            <label class="label" for="oldPass">Change Password:</label><label class="info" for="oldPass"></label>
            <input type="password" class="searching" name="oldPass" id="oldPass" placeholder="Current Password"><br/>
            <label class="label" for="newPass"></label><label class="info" for="newPass"></label>
            <input type="password" class="searching" name="newPass" id="newPass" placeholder="New Password"><br/>
            <label class="label" for="confimPass"></label><label class="info" for="confirmPass"></label>
            <input type="password" class="searching" name="confirmPass" id="confirmPass" placeholder="Confirm Password"><br/>
            <input type="submit" class="submit" name="Update" value="Update" onclick="return confirm('Are you sure you want this/these change(s)?')">
          </form>
        </div><!--account end-->
        <!-- Branch Membership(s) -->
        <?php
              // sql statement to select branch Id and Expiry from BranchMembership table using the member number found earlier
              $sql = "SELECT BranchID, Name, Expiry FROM BranchMembership
                      LEFT JOIN Branch ON BranchID = ID WHERE MemberID = ? ORDER BY BranchID";
              $stmt = sqlsrv_query($userConn, $sql, array($memberNum));
              if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
        ?>
        <!--?php if(sqlsrv_num_rows($stmt) != 0): ?-->
        <h3>Branch Membership(s)</h3>
        <table cellpadding="0" cellspacing="0" border="0" class="display dataTable" id="example">
          <thead>
            <tr>
              <th>Branch ID</th>
              <th>Name</th>
              <th>Expiry</th>
            </tr>
          </thead>
          <tbody>
              <?php while ($row = sqlsrv_fetch_array($stmt)) : ?>
                <?php $colour = date_format($row['Expiry'], 'U') < time() ? 'red' : 'black'; ?>
                <tr>
                  <td><?= $row['BranchID'] ?></td>
                  <td><?= $row['Name'] ?></td>
                  <td style="color: <?= $colour ?>"><?= $row['Expiry']->format('Y-m-d') ?></td>
                </tr>
            <?php endwhile ?>
          </tbody>
        </table>
        <!-- Associate Account(s) -->
        <?php if ($_SESSION['type'] !== 2) : ?>
          <h3>Associate Account(s)</h3>
          <table class="display dataTable">
            <thead>
              <tr>
                <th>Member Number</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Expiry</th>
              </tr>
            </thead>
            <?php
              $sql = "SELECT associatememberid as id, FirstName, LastName, Expiry FROM associatemembers
                      LEFT JOIN memberinfo ON associatememberid = memberinfo.membernum
                      LEFT JOIN membership ON associatememberid = membership.membernum
                      WHERE individualmemberid = ?";
              $stmt = sqlsrv_query($userConn, $sql, array($memberNum));
              if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
              $associateId = array(); 
              $associateName = array(); 
              while ($row = sqlsrv_fetch_array($stmt)) : ?>
                <?php $colour = date_format($row['Expiry'], 'U') < time() ? 'red' : 'black';
                      $associate = true; 
                      // add for retrive associated member info
                      $associateId[] = $row['id'];
                      $associateName[] = $row['FirstName'] . " " . $row['LastName'];?>
                <tr>
                  <td><?= $row['id'] ?></td>
                  <td><?= $row['FirstName'] ?></td>
                  <td><?= $row['LastName'] ?></td>
                  <td style="color: <?= $colour ?>"><?= $row['Expiry']->format('Y-m-d') ?></td>
                </tr>
            <?php endwhile ?>
          </table><br>
        <?php else : ?>
          <h3>Associate of</h3>
          <table class="display dataTable">
            <thead>
              <tr>
                <th>Member Number</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Expiry</th>
              </tr>
            </thead>
            <?php
              $sql = "SELECT DISTINCT individualmemberid as id, FirstName, LastName, Expiry FROM associatemembers
                      LEFT JOIN memberinfo ON individualmemberid = memberinfo.membernum
                      LEFT JOIN membership ON individualmemberid = membership.membernum
                      WHERE associatememberid = ?";
              $stmt = sqlsrv_query($userConn, $sql, array($memberNum));
              if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

              while ($row = sqlsrv_fetch_array($stmt)) : ?>
                <?php $colour = date_format($row['Expiry'], 'U') < time() ? 'red' : 'black';
                      $associate = true; ?>
                <tr>
                  <td><?= $row['id'] ?></td>
                  <td><?= $row['FirstName'] ?></td>
                  <td><?= $row['LastName'] ?></td>
                  <td style="color: <?= $colour ?>"><?= $row['Expiry']->format('Y-m-d') ?></td>
                </tr>
            <?php endwhile; ?>
          </table><br>
        <?php endif; ?>
        <!-- Associate Account(s) Branch Membership(s)-->
        <?php for($j = 0; $j < count($associateId); $j++): ?>
          <?php
                // sql statement to select branch Id and Expiry from BranchMembership table using the member number found earlier
                $sql = "SELECT BranchID, Name, Expiry FROM BranchMembership
                        LEFT JOIN Branch ON BranchID = ID WHERE MemberID = ? ORDER BY BranchID";
                $stmt = sqlsrv_query($userConn, $sql, array($associateId[$j]));
                if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
          ?>
          <!--?php if(sqlsrv_num_rows($stmt) != 0): ?-->
          <h3>Branch Membership(s) For <?= $associateName[$j];?></h3>
          <table cellpadding="0" cellspacing="0" border="0" class="display dataTable" id="example">
            <thead>
              <tr>
                <th>Branch ID</th>
                <th>Name</th>
                <th>Expiry</th>
              </tr>
            </thead>
            <tbody>
                <?php while ($row = sqlsrv_fetch_array($stmt)) : ?>
                  <?php $colour = date_format($row['Expiry'], 'U') < time() ? 'red' : 'black'; ?>
                  <tr>
                    <td><?= $row['BranchID'] ?></td>
                    <td><?= $row['Name'] ?></td>
                    <td style="color: <?= $colour ?>"><?= $row['Expiry']->format('Y-m-d') ?></td>
                  </tr>
              <?php endwhile ?>
            </tbody>
          </table>
        <?php endfor ?>
      </div><!--container end-->
    </div><!--resultsbackground end-->
  </body>
</html>
