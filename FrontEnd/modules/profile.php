<!DOCTYPE html>
<html lang="en">
<head>

    <!-- Title -->
    <title>Portal</title>

    <?php
    $headerPrepend = '../';
    include_once '../headers.php';
    ?>

</head>
<body>

<?php
include 'nav.php';
?>
<div class="container">
    <h2>fName lName</h2>
    <img src="../resources/spidey.jpg" alt="Your Avatar" class="img-circle"><br><br>
    <div class="form-group">
        <label for="changeJerseyNumber">Jersey Number</label>
        <input type="name" class="form-control" id="changeJerseyNumber" placeholder="Jersey Number">
    </div>
    <button class="btn btn-default" type="button">Save Profile</button>
</div>


</body>

