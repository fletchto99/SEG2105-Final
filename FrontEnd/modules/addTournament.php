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
    <h2>Create Tournament</h2></br>
    <div class="form-group">
        <label for="tournamentName">Tournament Name</label>
        <input type="name" class="form-control" id="tournamentName" placeholder="Tournament Name">
    </div>
    <button class="btn btn-default" type="submit" id="addTournamentSubmit">Submit</button>
</div>
</body>

