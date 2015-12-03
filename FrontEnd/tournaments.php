<!DOCTYPE html>
<html lang="en">
<head>

    <!-- Title -->
    <title>Tournament Maker</title>

    <?php
    include_once __DIR__.'/headers.php';
    ?>

</head>

<body>
    <nav class="navbar navbar-inverse navbar-fixed-top">
        <div class="container">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="#">Project name</a>
            </div>
            <div id="navbar" class="collapse navbar-collapse">
                <ul class="nav navbar-nav">
                    <li class="active"><a href="#">Home</a></li>
                    <li><a href="#about">About</a></li>
                    <li><a href="#contact">Contact</a></li>
                </ul>
            </div><!--/.nav-collapse -->
        </div>
    </nav>
    <div id="content" class="container">

        <div class="table-responsive">
            <table class="table" id="tournaments_table">
                <tr><th>Tournaments</th></tr>
                <tr><td>T1</td></tr>
                <tr><td>T2</td></tr>
                <tr><td>T3</td></tr>
            </table>
        </div>

    </div>
</body>