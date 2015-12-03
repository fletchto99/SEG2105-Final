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
    <h2>Teams</h2></br>
    <table class="table" id="teams_table">
        <tbody>
        <tr>
            <td>T1</td>
        </tr>
        <tr>
            <td>T2</td>
        </tr>
        <tr>
            <td>T3</td>
        </tr>
        </tbody>
    </table><br><br>
    <a class="btn btn-default" type="button" id="addTeam" href="addTeam.php">Add Team</a>
</div>
</body>

