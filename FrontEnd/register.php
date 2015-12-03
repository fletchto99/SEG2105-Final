<!DOCTYPE html>
<html lang="en">
<head>

    <!-- Title -->
    <title>Tournament Maker</title>

    <?php
        include_once 'headers.php';
    ?>

</head>

<body>
<div id="content" class="container">

    <form class="form-signin">
        <h2 class="form-signin-heading">Welcome</h2>
        <label for="inputEmail" class="sr-only">Username</label>
        <input type="text" id="inputEmail" class="form-control" placeholder="Username" required autofocus>
        <label for="inputPassword" class="sr-only">Password</label>
        <input type="password" id="inputPassword" class="form-control" placeholder="Password" required>
        <label for="inputFirstName" class="sr-only">Password</label>
        <input type="text" id="inputFirstName" class="form-control" placeholder="First Name" required>
        <label for="inputLastName" class="sr-only">Password</label>
        <input type="text" id="inputLastName" class="form-control" placeholder="Last Name" required>
        <button class="btn btn-lg btn-primary btn-block" type="submit">Register</button>
    </form>

    <a href="register.php">I don't have an account yet!</a>

</div>
</body>