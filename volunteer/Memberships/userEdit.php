
<?php
  require('../../db/membershipAdminCheck.php');
  require('../../errorReporter.php');
  require('../../db/memberConnection.php');
  require('../../addBranches.php');

  if (isset($_POST['update'])) {
    $memberid = $_POST['memberid'];
    $memtype = $_POST['memtype'];
    $credits = $_POST['credits'];
    $type = $_POST['type'];
    $access = $_POST['access'];
    $newexpiry = $_POST['newexpiry'];
    $newgenerations = $_POST['generations'];

    $newusername = $_POST['newusername'];
    $newfname = $_POST['fname'];
    $newlname = $_POST['lname'];
    $newemail = $_POST['email'];
    $newpost = $_POST['postalcode'];
    $newcity = $_POST['city'];
    $newprovince = $_POST['province'];
    $newaddress = $_POST['address'];
    $newphone = $_POST['phone'];

    $researcher = isset($_POST['researcher']);
    $eStore = isset($_POST['eStore']);
    $specProj = isset($_POST['specProj']);
    $newassocs = isset($_POST['newassoc']) ? $_POST['newassoc'] : null;
    $oldassocs = isset($_POST['deassoc']) ? $_POST['deassoc'] : null;

    $columns = array();
    $values = array();

    if ($newexpiry != '')
      list($columns[], $values[]) = array('expiry = ?', $newexpiry);
    if ($credits != '')
      list($columns[], $values[]) = array('credit = ?', $credits);
    if ($newgenerations != '' && is_numeric($newgenerations))
      list($columns[], $values[]) = array('generations = ?', $newgenerations);
    if ($type != '' && $type != $memtype)
      list($columns[], $values[]) = array('typeofmember = ?', $type);

    if (count($columns) > 0) {
      $sql = implode(' ', array(
        'UPDATE membership SET',
        implode(', ', $columns),
        'WHERE membernum = ?'));

      array_push($values, $memberid);
      $stmt = sqlsrv_query($userConn, $sql, $values);

      if ($stmt === false) {
        $error = sqlsrv_errors();

        if ($error[0]['code'] === 241)
          $_SESSION['error'] = "$newexpiry is not a valid date.";
        else
          errorReport($error, __FILE__, __LINE__);
      }
    }

    $columns = array();
    $values = array();

    if ($newfname != '')
      list($columns[], $values[]) = array('firstname = ?', $newfname);
    if ($newlname != '')
      list($columns[], $values[]) = array('lastname = ?', $newlname);
    if ($newemail != '')
      list($columns[], $values[]) = array('email = ?', $newemail);
    if ($newcity != '')
      list($columns[], $values[]) = array('city = ?', $newcity);
    if ($newprovince != '')
      list($columns[], $values[]) = array('province = ?', $newprovince);
    if ($newpost != '')
      list($columns[], $values[]) = array('countrycode = ?', $newpost);
    if ($newaddress != '')
      list($columns[], $values[]) = array('address = ?', $newaddress);
    if ($newphone != '')
      list($columns[], $values[]) = array('phone = ?', $newphone);

    if (count($columns) > 0) {
      $sql = implode(' ', array(
        'UPDATE memberinfo SET',
        implode(', ', $columns),
        'WHERE membernum = ?'));

      array_push($values, $memberid);
      $stmt = sqlsrv_query($userConn, $sql, $values);
      if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
    }

    if ($researcher && !$_SESSION['researcher']) {
      $sql = "INSERT INTO Researchers VALUES (?)";
      $stmt = sqlsrv_query($userConn, $sql, array($memberid));
      if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
      $_SESSION['researcher'] = true;

    } else if (!$researcher && $_SESSION['researcher']) {
      $sql = "DELETE FROM Researchers WHERE MemberNum = ?";
      $stmt = sqlsrv_query($userConn, $sql, array($memberid));
      if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
      $_SESSION['researcher'] = false;
    }


    if($eStore)
    {
      $sql = "UPDATE Members SET eStore = 1 WHERE MemberNum = ?";
      $stmt = sqlsrv_query($userConn, $sql, array($memberid));
      if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
      $_SESSION['eStore'] = true;
    }
    else if(!$eStore)
    {
      $sql = "UPDATE Members SET eStore = 0 WHERE MemberNum = ?";
      $stmt = sqlsrv_query($userConn, $sql, array($memberid));
      if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
      $_SESSION['eStore'] = false;
    }

    if($specProj)
    {
      $sql = "UPDATE Members SET SpecialProject = 1 WHERE MemberNum = ?";
      $stmt = sqlsrv_query($userConn, $sql, array($memberid));
      if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
      $_SESSION['specProj'] = true;
    }
    else if(!$specProj)
    {
      $sql = "UPDATE Members SET SpecialProject = 0 WHERE MemberNum = ?";
      $stmt = sqlsrv_query($userConn, $sql, array($memberid));
      if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
      $_SESSION['specProj'] = false;
    }

    if ($newassocs != null) {
      $assocs = array();
      $sql = "SELECT associatememberid as id FROM associatemembers WHERE individualmemberid = ?";
      $stmt = sqlsrv_query($userConn, $sql, array($memberid));

      while ($row = sqlsrv_fetch_array($stmt)) $assocs[$row['id']] = 1;

      foreach ($newassocs as $associd) {
        if ($associd != '' && $associd != $memberid) {
          if ($assocs[$associd] != 1) {
            $sql = "INSERT INTO associatemembers (associatememberid, individualmemberid) VALUES (?, ?)";
            $stmt = sqlsrv_query($userConn, $sql, array($associd, $memberid));
            if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
          } else
            $_SESSION['error'] .= "The member with member number '$associd' is already associated with this account. <br>";
        }
      }
    }

    if ($oldassocs != null) {
      foreach ($oldassocs as $associd) {
        $sql = "DELETE FROM associatemembers WHERE associatememberid = ? AND individualmemberid = ?";
        $stmt = sqlsrv_query($userConn, $sql, array($associd, $memberid));
        if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
      }
    }

    $columns = array();
    $values = array();

    if ($newusername != '')
      list($columns[], $values[]) = array('username = ?', $newusername);
    if ($access != '' && $access != $memaccess)
      list($columns[], $values[]) = array('accesslevel = ?', $access);

    if (count($columns) > 0) {
      $sql = implode(' ', array(
        'UPDATE members SET',
        implode(', ', $columns),
        'WHERE membernum = ?'));

      array_push($values, $memberid);
      $stmt = sqlsrv_query($userConn, $sql, $values);
      if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
    }

    if (isset($_POST['branchexpiry'])) {
      $sql = "SELECT branchid FROM branchmembership WHERE memberid = ? ORDER BY branchid";
      $stmt = sqlsrv_query($userConn, $sql, array($memberid));
      if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

      $branches = array();
      while ($row = sqlsrv_fetch_array($stmt)) $branches[] = $row['branchid'];

      for ($i = 0; $i < count($_POST['branchexpiry']); $i++) {
        $branchexpiry = $_POST['branchexpiry'][$i];
        if ($branchexpiry != '') {
          $sql = "UPDATE branchmembership SET expiry = ? WHERE memberid = ? AND branchid = ?";
          $stmt = sqlsrv_query($userConn, $sql, array($branchexpiry, $memberid, $branches[$i]));
          if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
        }
      }
    }

    if (isset($_POST['addbranch']) && count($_POST['addbranch']) > 0) {
      addBranches($memberid, $_POST['addbranch'], $userConn);
    }

    if (isset($_POST['leavebranch']) && count($_POST['leavebranch']) > 0) {
      $branches = implode(', ', $_POST['leavebranch']);
      $placeholders = implode(', ', array_map(function($_){ return '?'; }, $_POST['leavebranch']));
      $sql = "DELETE FROM branchmembership WHERE memberid = ? AND branchid IN ($placeholders)";

      $values = array_merge(array($memberid), $_POST['leavebranch']);
      $stmt = sqlsrv_query($userConn, $sql, $values);
      if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
    }

    header("location: /volunteer/Memberships/userEdit.php?user=$memberid");
  }

  $membernum = $_GET['user'];

  if (!isset($membernum) || !is_numeric($membernum)) {
    $memberInfo = null;
  } else {
    $sql = "SELECT username, expiry, generations.name as genname, credit, firstname, lastname, email,
                    phone, type.id as typeid, type.name as typename, access.id as accessid,
                    access.name as accessname, countrycode, city, province, address
            FROM memberinfo
            LEFT JOIN membership ON memberinfo.membernum = membership.membernum
            LEFT JOIN members ON memberinfo.membernum = members.membernum
            LEFT JOIN typeofmember as type ON membership.typeofmember = type.id
            LEFT JOIN accesslevel as access ON members.accesslevel = access.id
            LEFT JOIN generations on generations.id = membership.generations
            WHERE memberinfo.membernum = ?";

    $stmt = sqlsrv_query($userConn, $sql, array($membernum));
    if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

    $memberInfo = sqlsrv_fetch_array($stmt);

    $sql = "SELECT 1 FROM Researchers WHERE MemberNum = ?";
    $stmt = sqlsrv_query($userConn, $sql, array($membernum), array('Scrollable' => 'static'));
    if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC, SQLSRV_SCROLL_ABSOLUTE);

    $expiry = isset($memberInfo['expiry'])
      ? date_format($memberInfo['expiry'], 'Y-m-d') : '';
    $_SESSION['researcher'] = $row === null ? false : true;
  }
?>

<!DOCTYPE HTML>
<html lang="en-US">
  <head>
    <meta charset="utf-8">
    <title>MGS Administrator</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width">
    <link rel="stylesheet" href="/css/normalize.css">
    <link rel="stylesheet" href="/css/main.css">
    <link rel="stylesheet" href="/DataTables-1.10.6/media/css/jquery.dataTables.css">
    <script src="/js/vendor/modernizr-2.6.2.min.js"></script>

    <script type="text/javascript">
      document.addEventListener('DOMContentLoaded', function(){
        document.getElementById('addassoc').addEventListener('click', function(){
          var assocs_elem = document.getElementById('associates').getElementsByTagName('tbody')[0];
          var row = document.createElement('tr');
          var input = document.createElement('input');

          var attrs = {
            'min': 0, 'placeholder': 'member number',
            'type': 'number', 'name': 'newassoc[]',
          };

          for (var key in attrs) input.setAttribute(key, attrs[key]);

          row.appendChild(function(){
            var td = document.createElement('td');
            td.appendChild(input); return td;}());

          for (var i = 0; i < 4; i++)
            row.appendChild(document.createElement('td'));

          assocs_elem.appendChild(row);
        }, false);
      }, false);
    </script>
  </head>
  <body>
    <div id="resultsbackground">
      <div id="container" class="home">
        <div id="searchresults">
          <?php require('header.php'); ?>
        </div>
        <?php if ($memberInfo != NULL) : ?>
          <h2>Edit information for member #<?= $membernum ?></h2>
            <p class="errorColor">
              <?= isset($_SESSION['error']) ? $_SESSION['error'] : '' ?>
              <?php unset($_SESSION['error']) ?>
            </p>
            <form target="_blank" action="sendPackage.php" method="get">
              <input type="hidden" name="memberid" value="<?= $membernum ?>">
              <label class="label" for="send">Generate Registered Member Email</label>
              <input class="submit" type="submit" name="send" value="Generate">
            </form>
            <form action="userEdit.php" method="post">

              <div class="infocol" id="accountinfo">
                <input type="hidden" name="memberid" value="<?= $membernum ?>">
                <input type="hidden" name="memtype" value="<?= $memberInfo['typeid'] ?>">
                <input type="hidden" name="memaccess" value="<?= $memberInfo['accessid'] ?>">

                <?php if ($memberInfo['username'] != NULL) : ?>
                  <label class="label" for="newusername">Username: </label>
                  <label class="info"><?= $memberInfo['username'] ?></label><br>
                  <input type="text" id="newusername" class="searching" name="newusername" placeholder="Change Username"><br>
                <?php else : ?>
                  <label class="label">Username: </label>
                  <label class="info"></label><br>
                  <input type="text" disabled="disabled" class="searching" placeholder="Change Username"><br>
                <?php endif ?>

                <label class="label" for="credits">Account credits:</label>
                <label class="info"><?= sprintf("%.2f", $memberInfo['credit']) ?></label><br>
                <input type="number" class="searching" id="credits" name="credits" min="0"><br>

                <label class="label" for="newexpiry">Membership expiry date:</label>
                <label class="info"><?= $expiry ?></label><br>
                <input type="date" class="searching" id="newexpiry" name="newexpiry" placeholder="yyyy-mm-dd"><br>

                <label class="label">Member type:</label>
                <label class="info"><?= $memberInfo['typeid']." (".$memberInfo['typename'].")" ?></label><br>
                <select class="searching" name="type">
                  <option name="" value=""></option>
                  <?php
                    $stmt = sqlsrv_query($userConn, "SELECT id, name FROM typeofmember ORDER BY id");
                    if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

                    while ($row = sqlsrv_fetch_array($stmt)) : ?>
                      <option value="<?= $row['id'] ?>">
                        <?= $row['id']." (".$row['name'].")" ?>
                      </option>
                  <?php endwhile ?>
                </select><br>

                <?php if ($memberInfo['accessid'] != NULL) : ?>
                  <label class="label">Access Level:</label>
                  <label class="info"><?= $memberInfo['accessid']." (".$memberInfo['accessname'].")" ?></label><br>
                  <select class="searching" name="access">
                    <option name="" value=""></option>
                    <?php
                      $stmt = sqlsrv_query($userConn, "SELECT id, name FROM accesslevel ORDER BY id");
                      if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

                      while ($row = sqlsrv_fetch_array($stmt)) : ?>
                        <option value="<?= $row['id'] ?>">
                          <?= $row['id']." (".$row['name'].")" ?>
                        </option>
                    <?php endwhile ?>
                  </select><br>
                <?php else : ?>
                  <label class="label">Access Level:</label>
                  <label class="info"></label><br>
                  <select class="searching" name="access" disabled></select><br>
                <?php endif ?>

                <label class="label" for="researcher">Researcher:</label>
                <label class="info" style="visibility:hidden"></label><br>
                <input type="checkbox" class="searching" name="researcher" id="researcher"
                  <?php if ($_SESSION['researcher']) echo 'checked'; ?>><br><br>

                <label class="label" for="eStore">e-Store:</label>
                <label class="info" style="visibility:hidden"></label><br>
                <input type="checkbox" class="searching" name="eStore" id="eStore"
                  <?php if ($_SESSION['eStore']) echo 'checked'; ?>><br><br>


                <label class="label" for="specProj">Special Projects:</label>
                <label class="info" style="visibility:hidden"></label><br>
                <input type="checkbox" class="searching" name="specProj" id="specProj"
                  <?php if ($_SESSION['specProj']) echo 'checked'; ?>><br><br>

                <label class="label">Generations:</label>
                <label class="info"><?= $memberInfo['genname'] ?></label><br>
                <select name="generations" class="searching">
                  <option value=""></option>
                  <?php
                    $sql = "SELECT id, name FROM generations ORDER BY name";
                    $stmt = sqlsrv_query($userConn, $sql);
                    if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

                    while ($row = sqlsrv_fetch_array($stmt)) : ?>
                      <option value="<?= $row['id'] ?>"><?= $row['name'] ?></option>
                  <?php endwhile ?>
                </select>
              </div>

              <div class="infocol" id="personalinfo">
                <label class="label" for="fname">First name:</label>
                <label class="info"><?= $memberInfo['firstname'] ?></label><br>
                <input type="text" class="searching" name="fname" id="fname" placeholder="Change First Name">
                <br>

                <label class="label" for="lname">Last name:</label>
                <label class="info"><?= $memberInfo['lastname'] ?></label><br>
                <input type="text" class="searching" name="lname" id="lname" placeholder="Change Last Name">
                <br>

                <label class="label" for="email">Email:</label>
                <label class="info"><?= $memberInfo['email'] ?></label><br>
                <input type="email" class="searching" name="email" id="email" placeholder="Change Email">
                <br>

                <label class="label" for="phone">Phone:</label>
                <label class="info"><?= $memberInfo['phone'] ?></label><br>
                <input type="phone" class="searching" name="phone" id="phone" placeholder="Change Phone Number">
                <br>

                <label class="label" for="postalcode">Postal code:</label>
                <label class="info"><?= $memberInfo['countrycode'] ?></label><br>
                <input type="text" class="searching" name="postalcode" id="postalcode" placeholder="Change Postal Code">
                <br>

                <label class="label" for="address">Address:</label>
                <label class="info"><?= $memberInfo['address'] ?></label><br>
                <input type="text" class="searching" name="address" id="address" placeholder="Change Address">
                <br>

                <label class="label" for="city">City:</label>
                <label class="info"><?= $memberInfo['city'] ?></label><br>
                <input type="text" class="searching" name="city" id="city" placeholder="Change City">
                <br>

                <label class="label" for="province">Province:</label>
                <label class="info"><?= $memberInfo['province'] ?></label><br>
                <input type="text" class="searching" name="province" id="province" placeholder="Change Province">
              </div>
              <hr>
              <div id="associatedaccounts">
                <h3>Associated accounts:</h3>

                <table id="associates" class="display dataTable">
                  <thead>
                    <tr>
                      <th>Member number</th>
                      <th>First Name</th>
                      <th>Last Name</th>
                      <th>Expiry</th>
                      <th>Remove associate</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                      $sql = "SELECT associatememberid as id, firstname, lastname, Expiry FROM associatemembers
                              LEFT JOIN memberinfo ON associatememberid = memberinfo.membernum
                              LEFT JOIN membership ON associatememberid = membership.membernum
                              WHERE individualmemberid = ?";
                      $stmt = sqlsrv_query($userConn, $sql, array($membernum));
                      if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

                      while ($row = sqlsrv_fetch_array($stmt)) : ?>
                        <?php $colour = date_format($row['Expiry'], 'U') < time() ? 'red' : 'black'; ?>
                        <tr>
                          <td><?= $row['id'] ?></td>
                          <td><?= $row['firstname'] ?></td>
                          <td><?= $row['lastname'] ?></td>
                          <td style="color: <?= $colour ?>"><?= $row['Expiry']->format('Y-m-d') ?></td>
                          <td><input type="checkbox" name="deassoc[]" value="<?= $row['id'] ?>"></td>
                        </tr>
                    <?php endwhile ?>

                    <tr>
                      <?php if ($memberInfo['typeid'] != 2 && $memberInfo['typeid'] != 9) : ?>
                        <td><input type="number" min="0" name="newassoc[]" placeholder="member number"></td>
                        <td></td><td></td><td></td><td></td>
                      <?php else : ?>
                        <td><input type="number" disabled="disabled" placeholder="member number"></td>
                      <?php endif ?>
                    </tr>
                  </tbody>
                </table>

                <?php if ($memberInfo['typeid'] != 2 && $memberInfo['typeid'] != 9) : ?>
                  <input type="button" style="width:auto" class="submit" id="addassoc" value="add associate">
                <?php endif ?>
              </div>
              <hr>
              <div id="branchmemberships">
                <h3>Branch memberships:</h3>

                <table cellpadding="0" cellspacing="0" border="0" class="display dataTable" id="example">
                  <thead>
                    <tr>
                      <th>Branch ID</th>
                      <th>Branch name</th>
                      <th>Expiry</th>
                      <th>Change expiry</th>
                      <th>Remove branch</th>
                    </tr>
                  </thead>
                  <?php
                    $sql = "SELECT BranchID, Name, Expiry FROM BranchMembership
                            LEFT JOIN Branch ON BranchID = ID WHERE MemberID = ? ORDER BY BranchID";
                    $stmt = sqlsrv_query($userConn, $sql, array($membernum));
                    if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

                    while ($row = sqlsrv_fetch_array($stmt)) : ?>
                      <?php $colour = date_format($row['Expiry'], 'U') < time() ? 'red' : 'black'; ?>
                      <tr>
                        <td><?= $row['BranchID'] ?></td>
                        <td><?= $row['Name'] ?></td>
                        <td style="color: <?= $colour ?>"><?= $row['Expiry']->format('Y-m-d') ?></td>
                        <td><input type="textbox" placeholder="yyyy-mm-dd" name="branchexpiry[]"></td>
                        <td><input type="checkbox" name="leavebranch[]" value="<?= $row['BranchID'] ?>"></td>
                      </tr>
                  <?php endwhile ?>
                </table>

                <h3>Add branch memberships:</h3><br>
                <?php
                  $sql = "SELECT id, name FROM branch";
                  $stmt = sqlsrv_query($userConn, $sql);
                  while ($row = sqlsrv_fetch_array($stmt)) : ?>
                    <label class="branch" for="<?= $row['name'] ?>">
                      <?= $row['name'] ?>
                    </label>
                    <input type="checkbox" name="addbranch[]" class="branchbox" value="<?= $row['id'] ?>" id="<?= $row['name'] ?>">
                    <br><br>
                <?php endwhile ?>

                <input class="submit" type="submit" name="update" value="Update">
              </div>
            </form>
        <?php else : ?>
          <div class="memberContent">
            <?php if (is_numeric($membernum)) : ?>
              <h3>No information was found for member #<?= $membernum ?>.</h3>
            <?php else : ?>
              <h3>Member number must be a number.</h3>
            <?php endif ?>
          </div>
        <?php endif ?>
      </div>
    </div>
  </body>
</html>
