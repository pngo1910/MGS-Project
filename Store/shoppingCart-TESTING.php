<?php
    $name = isset($_GET['name'])? $_GET['name']: 'store';
    switch($name){
        case 'login':
            require('../db/loginCheck.php');
            break;
        default:
            session_name($name);
            session_start();
    }
    require('../db/storeConnection.php');
    require('shoppingCartPHP.php');
?>

<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
    <head>
        <meta charset="utf-8">
        <title> Manitoba Genealogical Society</title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width">

        <!-- Place favicon.ico and apple-touch-icon.png in the root directory -->

        <link rel="stylesheet" href="/css/normalize.css">
        <link rel="stylesheet" href="/css/main.css">
        <script src="/js/vendor/modernizr-2.6.2.min.js"></script>
        <?php require('shoppingCartJS.php'); ?>
    </head>
    <body>
        <!--[if lt IE 7]>
            <p class="chromeframe">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">activate Google Chrome Frame</a> to improve your experience.</p>
        <![endif]-->

        <!-- Add your site or application content here -->
        <div id="resultsbackground">       
        <!--<div id="homebackground"></div> -->       
            <div id="container" class="home">
                <?php require '../header.php'; ?> 
                <!--<div id="homesearch">-->
                <div id="searchresults">
                    <!---<div class='navDash'>-->
                    <p id="message"></p>
                    <p>NEW FEATURE!</p>
                    <p><a href="store.php?name=<?= $name ?>">Return to Store</a></p>
                    <!-- Check that the cart isn't empty -->
                    <?php if(!empty($_SESSION['values'])): ?>
                        <form id="removeForm" onsubmit="return updatePage(submit)" method="post" action="shoppingCart.php?name=<?= $name ?>">
                        <dl>
                            <input type="hidden" id="ids" name="ids" value="" />
                            <input type="hidden" id="quantities" name="quantities" value="" />
                            <?php $i = 0; ?>
                            <?php while($i < count($newarray)): ?>
                            <dt><input class="checkbox" type="checkbox" name="<?= $newarray[$i] ?>" value="<?= $newarray[$i] ?>" /> <b>Name: <?= $newarray[$i+1] ?></b></dt>
                            <?php $i++; ?>
                            <?php $names[] = $newarray[$i] ?>
                            <?php $i++; ?>
                            <dd><b>Description:</b> <?= $newarray[$i] ?></dd>
                            <?php $i++; ?>
                            <?php $amounts[] = $newarray[$i]; ?>
                            <dd><b>Price:</b> $<?= $newarray[$i] ?></dd>
                            <?php $total += (float)$newarray[$i] * $quantities[$index]; ?>
                            <?php $i++; ?>
                            <?php $shipping[] = $newarray[$i]; ?>
                            <?php $totalQuantity += $quantities[$index]; ?>
                            <?php if(!isset($_SESSION['municipality']) || $_SESSION['municipality'] == ''): ?>
                                <?php $i++; ?>
                                <dd><b>Category:</b> <?= $newarray[$i] ?></dd>
                            <?php endif; ?>
                            <dd><b>Quantity:</b> <input type="number" class="quantity" value="<?= $quantities[$index] ?>" min="1" /></dd>
                            <?php $index++; ?>
                            <?php $i++; ?>
                            <?php endwhile; ?>
                        </dl>
                            <p><input type="submit" id="refresh" name="refresh" onclick="submit=this.value" value="Update Quantity" title="To add a new quantity to the total, click this button."/> <input type="submit" id="remove" name="remove" onclick="submit=this.value" value="Remove Product(s)" /></p>
                        </form>
                        <p><b>Total:</b> $<?= number_format($total, 2, '.', '') ?> <b>Total Items:</b> <?= $totalQuantity ?></p>
                        
                        <form action="createPayPalCart.php?name=<?= $name ?>" method="post" onsubmit="return purchaseProducts()">
                            <!--The page was continually loading because of this url: http://www.paypal.com/en_US/i/btn/x-click-but01.gif so I saved a copy of the paypal button and used
                            that instead-->
                            <input type="image" src="/img/btn_checkout_pp_142x27.png" name="submit" alt="Make payments with PayPal - it's fast, free and secure!">
                            <p>**Note: Clicking the PayPal Checkout button will take you to the PayPal log in screen. You'll have the option to log in to PayPal to complete the payment if you have an account. If you don't then click 'Don't have a PayPal account?'. You'll have two options: create a PayPal account or enter your payment information without an account and your credit card information will not be saved by PayPal.</p>
                        </form>
                    <?php else: ?>
                        <p>You have no items in your cart.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </body>
</html>