# Keeper
SEG2105 Final Project by Matt Langlois, Matt Yaraskativch, Qufei Chen, Meng Yang

2015/12/8

## A breif History

Keeper is a tournament matchup system used to create knockout, roundrobin and knockout roundrobin tournaments. The original application was going to consist of a server side API which the android application "keeper" would connect to. However for cross platform compatibility we ended up building a website which can be loaded on a plethora of devices. Although it is not completely optimized for low resolution screens it is still manageable.

## Introduction

There are 3 components to the Keeper app. There is the KeeperWeb Android application, which is a webview that loads the actual application. The second component is the front facing website component which can be found at https://fletchto99.com/other/sites/school/seg2105/keeper/#main/ (or in /web/). The final component is the web backend which communicates via a REST api using controllers, this can be found in web/cgi-bin/.

## Technologies Used

* Backend/API
    * PHP
    * MariaDB
* Frontend
    * Javascript
    * CSS
    * Vendor Libraries
        * Bootstrap
        * Bootstrap Notify
        * jQuery (with UI component)
        * FontAwesome

# Requirmenets

1. PHP 5.6.xx
2. MySQL or some equivalent

## Setup

The setup process is fairly straight forward. It consists of 3 parts: setting up DB, settings up backend, setting up frontend. The setup assumes you meet the previous requirements.

#### Database:

1. Create a DB
2. Populate the tables using the SQL scripts found within the "/Database/Blank Tables" folder. Alternatively an "Export with Sample Data.sql" file has been provided which already has some teams and tournaments populated.

#### PHP Configuration:
1. Create a Configuration.php file in "web/cgi-bin/" based on the contents of the Configuration.php.example file.
2. Update the configuration constants to point to the DB created in the database section above.

#### JS Configuration:
1. If the entire contents of the web folder remains unchanged then you should be good to go, proceed to creating an account section.
2. If the cgi-bin directory was relocated then `Keeper.ROOT_URL` will need to be updated to reflect the location of the controllers directory.

#### Account Creation:
*Organizer accounts will need to be set by the database administrator manually. While a controller was created to set a user to the organizer role, it was never implemented on the front end.*

1. Create an account via the frontend.
2. Once the account is created, log out before performing any actions.
3. Find the account created in the `Persons` table and set their `Role_ID` to 1.
4. Login and you will now have organizer status, being able to create players, teams and tournaments.

## Known Issues/Caveats of Algorithms

* No way to create an organizer account with out DB access. Was initially created as a security measure but should be implemented so organizers can create other organizers.
* Knockout roundrobin is a very friendly algorithm taking the top x=2^n teams where x < the number of teams registered. I.E. 9 teams in round robin, then 8 advance.
* Building off of the previous issue, only the top 2^n teams are considered, so if 3 are in the round robin phase and they all tie, then only 2 will advance.