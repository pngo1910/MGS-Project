<?php
  header('X-UA-Compatible: IE=edge,chrome=1');

  require('../db/loginCheck.php');
  require('../db/memberConnection.php');
  require('../errorReporter.php');
  $_SESSION['updated'] = '';
  $qry = "SELECT MemberNum FROM Members WHERE Username = ?";
  $stmt = sqlsrv_query($userConn, $qry, array($_SESSION['uname']), array("Scrollable"=>"static"));
  if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
  $memberNum = sqlsrv_fetch_array($stmt)['MemberNum'];
  $_SESSION['memberNum'] = $memberNum;
  // sql statement to select from membership table using membernum from previous query
  $sql = "SELECT Generations, TypeOfMember, YearJoined, Expiry FROM Membership WHERE MemberNum = ?";
  // execute the query
  $stmt = sqlsrv_query($userConn, $sql, array($memberNum), array('Scrollable' => 'static'));
  // if query does not get executed, print the errors and kill the script
  if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
  // fetch the row from the executed query
  $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC, SQLSRV_SCROLL_ABSOLUTE);
  $colour = date_format($row['Expiry'], 'U') < time() ? 'red' : 'black';

  $type = $row['TypeOfMember'];
  //Keep track of what type of member the user is
  $_SESSION['membertype'] = $type;

  $sql = "SELECT Name FROM TypeOfMember WHERE ID = ?";
  $stmt = sqlsrv_query($userConn, $sql, array($type), array('Scrollable' => 'static'));
  if (sqlsrv_fetch($stmt) === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
  $accountType = sqlsrv_get_field($stmt, 0);
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
    <link rel="stylesheet" href="/css/paypal.css">
    <script src="/js/vendor/modernizr-2.6.2.min.js"></script>
    <script type="text/javascript">
        function change(value){
          document.getElementById("amount").value= value;
        }
    </script>
  </head>
  <body>
    <div id="resultsbackground">
      <div id="container" class="home">
        <div id="searchresults">
          <?php require('../header.php'); ?>
        </div>
        <div id="renewal">
        <p><b>**Note:</b> You will be taken to PayPal to pay. We don't save your credit card information.</p>
        <p><b>**IMPORTANT Note: Credits system is not fully implemented in MANI and is NOT CURRENTLY AVAILABLE do not use PURCHASE CREDITS!!</b></p>
        <div id="titleRenewal" style="width: 172px;">Purchase Credits</div>
        <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
          <input type="hidden" name="cmd" value="_s-xclick">
          <input type="hidden" name="hosted_button_id" value="LEU7E5SDYEW6N">
          <table>
          <tr><td><input type="hidden" name="on0" value="Amount of credits">Amount of credits</td></tr><tr><td><select name="os0">
            <option value="40 Credits">NOT CURRENTLY AVAILABLE 40 Credits $10.00 CAD</option>
            <option value="100 Credits">100 Credits $25.00 CAD</option>
            <option value="200 Credits">200 Credits $50.00 CAD</option>
            <option value="300 Credits">300 Credits $75.00 CAD</option>
            <option value="400 Credits">400 Credits $100.00 CAD</option>
          </select> </td></tr>
          </table>
          <input type="hidden" name="currency_code" value="CAD">
          <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_buynowCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
          <img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
        </form>
        <p><b>**Note:</b>When the payment is completed, a security warning dialog will appear. Click OK.</p>
        <br />
        <form action="purchase.php" method="post">
          <div id="titleRenewal" style="width: 750px;">Membership Purchases/Renewals*</div>
          <br />
           <div name="title">Branch Membership(s)</div>
        <p>Please select a branch you would like to renew or join. You may join multiple branches if you wish.</p>
        <!-- Branch Membership(s) table -->
        <table cellpadding="0" cellspacing="0" border="0" class="display dataTable" id="branchMembership">
          <thead>
            <tr>
              <th>Purchase/Renew</th>
              <th>Price</th>
              <th>Branch ID</th>
              <th>Name</th>
              <th>Expiry</th>
            </tr>
          </thead>
          <tbody>
            <?php            
              //Get the ids of the branches the user is apart of  
              $sql = "SELECT BranchID FROM BranchMembership WHERE MemberID = ? ORDER BY BranchID";
              $stmt = sqlsrv_query($userConn, $sql, array($memberNum), array("Scrollable"=>"static"));
              if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

              $branchids = array();
              if(sqlsrv_num_rows($stmt) >= 1){
                while($row = sqlsrv_fetch_array($stmt))
                  $branchids[] = $row['BranchID'];
              }
              //Get the all the branches
              $qry = "SELECT ID, Name, Price FROM Branch";
              $branchStmt = sqlsrv_query($userConn, $qry, array(), array("Scrollable"=>"static"));
              if ($branchStmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

              while ($row = sqlsrv_fetch_array($branchStmt)) : ?>
                <tr>
                  <td><input type="checkbox" name="branch[]" id="<?= $row['ID'] ?>" value="<?= $row['ID'] ?>"></td>
                  <?php if($associate && $row['ID'] === 3): ?>
                  <td>$10.00</td>
                  <?php else: ?>
                  <td>$<?= $row['Price'] ?></td>
                  <?php endif; ?>
                  <td><?= $row['ID'] ?></td>
                  <td><?= $row['Name'] ?></td>
                  <?php
                      $match = false;
                      for($i = 0; $i < count($branchids); $i++){
                        //Determine if the user is apart of the branch
                          if($branchids[$i] == $row['ID']){
                            //Get the expiry date
                              $qry = "SELECT Expiry FROM BranchMembership WHERE BranchID = ? AND MemberID = ?";
                              $stmt = sqlsrv_query($userConn, $qry, array($branchids[$i], $memberNum));
                              if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

                              $expiry = sqlsrv_fetch_array($stmt);
                              //If the expiry date is expired give the td cell a colour of red
                              $colour = date_format($expiry['Expiry'], 'U') < time() ? 'red' : 'black';
                              //Echo out the cell with the expiry date
                              echo "<td style='color:$colour'>".$expiry['Expiry']->format('Y-m-d')."</td>";
                              $match = true;
                              $i = count($branchids);
                          }
                      }
                      if(!$match)
                        echo "<td></td>";
                  ?>

                </tr>
            <?php endwhile ?>
          </tbody>
        </table>
        <!-- Associate Member(s) table -->
          <?php
                  $sql = "SELECT associatememberid as id, FirstName, LastName, Expiry FROM associatemembers
                          LEFT JOIN memberinfo ON associatememberid = memberinfo.membernum
                          LEFT JOIN membership ON associatememberid = membership.membernum
                          WHERE individualmemberid = ?";
                  $stmt = sqlsrv_query($userConn, $sql, array($memberNum), array("Scrollable"=>"static"));
                  if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

                  $associate = $accountType === 'Associate' ? true : false ?>
           <?php if ($_SESSION['type'] !== 2) : ?>
            <br />
            <?php if($_SESSION['type'] !== 4): ?>
            <label class="branch" for="individual"><input type="checkbox" class="branchbox" name="individual" id="individual" value="individual">Renew Individual Membership $50</label><br/><br />
           <?php endif; ?>

             <div id="title">Associate(s)</div>
            <p>Renew Associate Membership(s) $20 each</p>
            <table class="display dataTable">
              <thead>
                <tr>
                  <th>Renew</th>
                  <th>Member Number</th>
                  <th>First Name</th>
                  <th>Last Name</th>
                  <th>Expiry</th>
                </tr>
              </thead>
              <!-- according to master, there will be only one associate member for each member, so $associateId could be handel like this, otherwise $associateId will be an array-->
              <?php 
                  $associateId = array(); 
                  $associateName = array();
              ?>
              <?php while ($row = sqlsrv_fetch_array($stmt)) : ?>
                <?php $colour = date_format($row['Expiry'], 'U') < time() ? 'red' : 'black'; ?>
                <?php 
                    $associateId[] = $row['id']; 
                    $associateName[] = $row['FirstName'] . ' ' . $row['LastName'];
                ?>
                <tr>
                  <td><input type="checkbox" name="associate[]" value="<?= $row['id'] ?>" id="<?= $row['id'] ?>" /></td>
                  <td><?= $row['id'] ?></td>
                  <td><?= $row['FirstName'] ?></td>
                  <td><?= $row['LastName'] ?></td>
                  <td style="color: <?= $colour ?>"><?= $row['Expiry']->format('Y-m-d') ?></td>
                </tr>
              <?php endwhile ?>
            </table><br>
          <?php else: ?>
            <br />
            <label class="branch" for="individual"><input type="checkbox" class="branchbox" name="individual" id="individual" value="individual">Renew Associate Membership $20</label><br/>
            <br /><br />
          <?php endif ?>
        <div id="title">Branch Membership(s) for Associate(s)</div>
        <p>Please select a branch you would like to renew or join. You may join multiple branches if you wish.</p>
        <!-- Associate Members Branch Membership(s) table -->
        <?php for($j = 0; $j < count($associateId); $j++): ?>
          <table cellpadding="0" cellspacing="0" border="0" class="display dataTable" id="associateMembersBranch<?=$associateId[$j];?>">
            <thead>
              <tr>
                <th colspan="5"><?= $associateName[$j]; ?></th>
              </tr>
              <tr>
                <th>Purchase/Renew</th>
                <th>Price</th>
                <th>Branch ID</th>
                <th>Name</th>
                <th>Expiry</th>
              </tr>
            </thead>
            <tbody>
              <?php            
                //Get the ids of the branches the user is apart of  
                $sql = "SELECT BranchID FROM BranchMembership WHERE MemberID = ? ORDER BY BranchID";
                $stmt = sqlsrv_query($userConn, $sql, array($associateId[$j]), array("Scrollable"=>"static"));
                if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

                $branchids = array();
                if(sqlsrv_num_rows($stmt) >= 1){
                  while($row = sqlsrv_fetch_array($stmt))
                    $branchids[] = $row['BranchID'];
                }
                //Get the all the branches
                $qry = "SELECT ID, Name, Price FROM Branch";
                $branchStmt = sqlsrv_query($userConn, $qry, array(), array("Scrollable"=>"static"));
                if ($branchStmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

                while ($row = sqlsrv_fetch_array($branchStmt)) : ?>
                  <tr>
                  <!-- if want to change the number of associate to greater than 1, change here as well branchesForAssociate -->
                    <td><input type="checkbox" name="branchesForAssociate[]" id="<?= $row['ID'] ?>" value="<?= $associateId[$j] ?> <?= $row['ID'] ?>"></td>
                    <?php if($associate && $row['ID'] === 3): ?>
                    <td>$8.00</td>
                    <?php else: ?>
                    <td>$<?= $row['Price'] ?></td>
                    <?php endif; ?>
                    <td><?= $row['ID'] ?></td>
                    <td><?= $row['Name'] ?></td>
                    <?php
                        $match = false;
                        for($i = 0; $i < count($branchids); $i++){
                          //Determine if the user is apart of the branch
                            if($branchids[$i] == $row['ID']){
                              //Get the expiry date
                                $qry = "SELECT Expiry FROM BranchMembership WHERE BranchID = ? AND MemberID = ?";
                                $stmt = sqlsrv_query($userConn, $qry, array($branchids[$i], $associateId[$j]));
                                if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

                                $expiry = sqlsrv_fetch_array($stmt);
                                //If the expiry date is expired give the td cell a colour of red
                                $colour = date_format($expiry['Expiry'], 'U') < time() ? 'red' : 'black';
                                //Echo out the cell with the expiry date
                                echo "<td style='color:$colour'>".$expiry['Expiry']->format('Y-m-d')."</td>";
                                $match = true;
                                $i = count($branchids);
                            }
                        }
                        if(!$match)
                          echo "<td></td>";
                    ?>

                  </tr>
              <?php endwhile ?>
            </tbody>
          </table>
          <br />
        <?php endfor ?>
        <br />
        <div class="donation-box">
                    <div id="titleRenewal">Donation</div>
                    
                    <div class="amount">
                      <input class="button" type="button" onclick="change(0.00)"" value="Clear">
                      <input class="button" type="button" onclick="change(10.00)" value="$10">
                      <input class="button" type="button" onclick="change(25.00)" value="$25">
                      <input class="button" type="button" onclick="change(50.00)"" value="$50">
                      <div class="button">$<input type="number" min="0" step="1" onkeypress="keycode = (event.keyCode ? event.keyCode : event.which);if (keycode < 48 || keycode > 57) {return false;}" name="donation" id="amount" class="set-amount" placeholder=""> </input></div>
                    </div>  
          </div>
          <br>
          <input type="submit" id="donate-button" name="submit" value="Purchase"/>
        </form>
      </div>
    </div>
</body>
</html>