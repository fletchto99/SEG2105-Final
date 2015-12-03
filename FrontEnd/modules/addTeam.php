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
    <h2>Add Team</h2></br>
    <div class="form-group">
        <label for="teamName">Team Name</label>
        <input type="name" class="form-control" id="teamName" placeholder="Team Name">
    </div>
    <button class="btn btn-default" type="submit" id="addTeamSubmit">Submit</button>
</div>
</body>

