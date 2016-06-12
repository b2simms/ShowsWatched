
<?php 
$page_title = "User Authentication - Password Reset"; 
 include_once 'partials/headers.php';
 include_once 'partials/parseForgotPassword.php';
?>

<div class="container">
  <section class="col col-lg-7">
    <h2> Password Reset Form </h2><hr>

    <div>
    <?php if(isset($result)) echo $result; ?>
    <?php if(!empty($form_errors)) echo show_errors($form_errors); ?>
    </div>

    <div class="clearfix"></div>
    
    <form action="" method="post">

      <div class="form-group">
        <label for="emailField">Email Address</label>
        <input type="email" class="form-control" name="email" id="emailField" placeholder="Email">
      </div>

      <div class="form-group">
        <label for="passwordField">New Password</label>
        <input type="password" name="new_password" class="form-control" id="passwordField" placeholder="New Password">
      </div>
      <div class="form-group">
        <label for="passwordField">Confirm Password</label>
        <input type="password" name="confirm_password" class="form-control" id="passwordField" placeholder="Confirm Password">
      </div>

      <button name="passwordResetBtn" type="submit" class="btn btn-primary pull-right">Reset Password</button>

    </form>

  </section>

  <?php include_once 'partials/back_button.php'; ?>

</div>

<?php include_once 'partials/footers.php'; ?>

</body>
</html>