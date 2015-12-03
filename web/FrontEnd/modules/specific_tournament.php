<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/html">
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
    <h2>Tournament Alpha</h2></br>
    <table class="table" id="specific_tournament_table">
        <thead>
        <tr>
            <th>Rank</th>
            <th>Team</th>
            <th>Matches Won</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>1</td>
            <td>Bollocks</td>
            <td>5/10</td>
        </tr>
        <tr>
            <td>2</td>
            <td>Team Green</td>
            <td>3/7</td>
        </tr>
        <tr>
            <td>3</td>
            <td>Team Red</td>
            <td>2/6</td>
        </tr>
        </tbody>
    </table>
    </br></br>
    <button class="btn btn-default" id="btn_view_matches" type="button">View Matches</button>
</div>



</body>

