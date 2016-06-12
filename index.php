<?php 
$page_title = "User Authentication - Homepage"; 
include_once 'partials/headers.php'; 
?>

<div class="container">

    <div class="flag">
        <h1>User Authentication System</h1>
        <p class="lead">Login and registration system with PHP.<br> 
        		This will be used later to display some site information.</p>

    	<?php if(!isset($_SESSION['username'])): ?>
			<p class="lead">You are currently not signed in <a href="login.php">Login</a> Not yet a member? <a href="signup.php">Signup</a> </P>
		<?php else: ?>
			<p class="lead">You are logged in as <?php if(isset($_SESSION['username'])) echo $_SESSION['username']; ?> <a href="logout.php">Logout</a> </p>
		<?php endif ?>
    </div>
</div><!-- /.container -->


<?php include_once 'partials/footers.php'; ?>

</body>
</html>