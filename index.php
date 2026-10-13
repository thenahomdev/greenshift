<?php
session_start();
if(isset($_GET['page']))
{
    $page = $_GET['page'];
}
else
{
    $page = 'login';
}
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>

<html lang = "nl"> 
<head>
    <link rel="stylesheet" href="./CSS/styles.css" />
</head>
<body>
<header>

    <?php
    include 'includes/navbar.inc.php';
    include 'includes/alerts.inc.php';
    ?>

</header>
<main>
    <?php
    include __DIR__ . '/config/connection.php';
    include 'includes/' . $page . '.inc.php';
    ?>
</main>
</body>