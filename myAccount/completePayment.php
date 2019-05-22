<?php
    header('X-UA-Compatible: IE=edge,chrome=1');
    require('../db/loginCheck.php');
    require('../db/memberConnection.php');

    if($_GET['confirm'] === "true"){
        //Get the users new total of credits
        $qry = "SELECT Credit FROM Membership WHERE MemberNum = (SELECT MemberNum FROM Members WHERE Username = ?)";
        $stmt = sqlsrv_query($userConn, $qry, array($_SESSION['uname']), array("Scrollable" => "static"));
        $credits = sqlsrv_fetch_array($stmt)['Credit'];
    }
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
    <script src="/js/vendor/modernizr-2.6.2.min.js"></script>
    </script>
</head>
<body>
    <div id="resultsbackground">
        <div id="container" class="home">
            <div id="searchresults">
      <?php require('../header.php'); ?>
            </div>
        </div>
        <?php if($_GET['confirm'] === "true"): ?>
            <p>You have successfully purchased <?= $_SESSION['numCredits'] ?> credits.</p>
            <p>Your new credit balance is <?= $credits ?>. You can view your credits by going to My Account -> Account Info.</p>
        <?php else: ?>
            <p>You have chosen not to purchase the credits.</p>
        <?php endif; ?>
    </div>
</body>
</html>