
<?php if (isset($_GET['header'])) : /* don't indent the switch cases. */ ?>
<?php switch (strtolower(urldecode($_GET['header']))) : ?>
<?php case 'admin' : ?>
  <ul>
    <li><a href='/admin/pdfuploadform.php'>PDF Upload</a></li>
    <li><a href='/admin/tablesDashboard.php'>View Tables</a></li>
    <li><a href='/admin/live/'>Live Dash</a></li>
    <li><a href="/admin/changeInhousePassword.php">Change Inhouse Password</a></li>
    <li><a href='/admin/viewErrors.php'>View Errors</a></li>
    <a id="logo-link" href="/admin"></a>
  </ul>
  <?php break ?>

<?php case 'volunteer' : ?>
  <ul>
    <li><a href='/volunteer/uploadDashboard.php'>Upload</a></li>
    <li><a href='/volunteer/tablesDashboard.php'>Edit Tables</a></li>
    <a id="logo-link" href="/volunteer"></a>
  </ul>
  <?php break ?>

<?php case 'store' : ?>
  <ul>
    <li><a href='/admin/store'>Store Management</a></li>
    <a id="logo-link" href="/store"></a>
  </ul>
  <?php break ?>

<?php case 'memberships' : ?>
  <ul>
    <li><a href='/volunteer/memberships/userInfo.php'>Members List</a></li>
    <li><a href='/volunteer/memberships/mailinglist.php'>Mailing List</a></li>
    <li><a href='/volunteer/memberships/userUpdate.php'>Recent Updates</a></li>
    <li><a href='/register/registerForm.php?login'>Register a Member</a></li>
    <a id="logo-link" href="/volunteer/Memberships"></a>
  </ul>
  <?php break ?>

<?php case 'my account' : ?>
  <ul>
    <li><a href='/myAccount/accountInfo.php'>Account Info</a></li>
    <li><a href='/myAccount/personalInfo.php'>Personal Info</a></li>
    <a id="logo-link" href="/myAccount"></a>
  </ul>
  <?php break ?>

<?php case 'search' : ?>
  <ul>
    <a id="logo-link" href="/member"></a>
  </ul>
  <?php break ?>

<?php default : ?>
  <ul></ul>
  <?php break ?>

<?php endswitch ?>
<?php endif ?>
