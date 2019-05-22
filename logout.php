
<div class="logoutForm">
  <form action="/exeunt.php" method="post">
    <input type="hidden" name="sessionname" value="<?= base64_encode(session_name()) ?>">
    <button type="submit" id="logout" onclick="return confirm('Are you sure you want to logout?')">Logout</button>
  </form>
</div>
