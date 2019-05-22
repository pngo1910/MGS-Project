
<?php if (isset($_GET['header'])) : /* don't indent the switch cases. */ ?>
<?php switch (strtolower(urldecode($_GET['header']))) : ?>
<?php case 'admin' : ?>
  <ul>
    <li><a href='/admin/pdfuploadform.php'>PDF Upload</a></li>
    <li><a href='/admin/tablesDashboard.php'>View Tables</a></li>
    <li><a href='/admin/live/'>Live Dash</a></li>
    <li><a href="/admin/changeInhousePassword.php">Change Inhouse Password</a></li>
    <li><a href='/admin/viewErrors.php'>View Errors</a></li>
    <li><a href='/admin/viewResearch.php'>View Research</a></li>
    <a id="logo-link" href="/admin"></a>
  </ul>
  <?php break ?>

<?php case 'special project' : ?>
  <ul>
    <li><a href='/SpecialProject/pdfuploadform.php'>PDF Upload</a></li>
    <a id="logo-link" href="/admin"></a>
  </ul>
  <?php break ?>


<?php case 'volunteer' : ?>
  <ul>
    <li><a href='/volunteer/uploadDashboard.php'>Upload</a></li>
    <li><a href='/volunteer/tablesDashboard.php'>Edit Tables</a></li>
    <li><a href='/volunteer/help.php'>Help</a></li>
    <a id="logo-link" href="/volunteer"></a>
  </ul>
  <?php break ?>

<?php case 'store management' : ?>
  <ul>
    <a id="logo-link" href="/admin/store/"></a>
  </ul>
  <?php break ?>

<?php case 'store' : ?>
  <ul>
    <li><a href="/store/store.php?name=login">Products</a></li>
    <a id="logo-link" href="/store/index.php?name=login"></a>
  </ul>
  <?php break ?>-->

<?php case 'memberships' : ?>
  <ul>
    <li><a href='/volunteer/memberships/userInfo.php'>Members List</a></li>
    <li><a href='/volunteer/memberships/mailinglist.php'>Mailing List</a></li>
    <li><a href='/volunteer/memberships/userUpdate.php'>Recent Updates</a></li>
    <li><a href='/volunteer/memberships/registerForm.php'>Register a Member</a></li>
    <li><a href="/volunteer/memberships/tablesDashboard.php">Edit Tables</a></li>
    <a id="logo-link" href="/volunteer/Memberships"></a>
  </ul>
  <?php break ?>

<?php case 'my account' : ?>
  <ul>
    <li><a href='/myAccount/accountInfo.php'>Account Info</a></li>
    <li><a href='/myAccount/personalInfo.php'>Personal Info</a></li>
    <li><a href='/myAccount/credits_renewals.php'>Purchase/Renewal</a></li>
    <li><a href='/myAccount/listPurchases.php'>View Purchases</a></li>
    <a id="logo-link" href="/myAccount"></a>
  </ul>
  <?php break ?>

<?php case 'search' : ?>
  <ul>
    <a href="/member/generations.php">Generations</a>
    <a id="logo-link" href="/member"></a>
  </ul>
  <?php break ?>

<?php default : ?>
  <ul></ul>
  <?php break ?>

<?php endswitch ?>
<?php endif ?>
