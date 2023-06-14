<?php

session_start();

if (!class_exists('TestIntegration'))
    require_once 'TestIntegration.php';

if (!class_exists('CustomLogger'))
    include_once 'CustomLogger.php';


if (!class_exists('DB'))
    include_once 'DB.php';

$logger = new CustomLogger();
$db = DB::initConnection();

$testIntegration = new TestIntegration($logger, $db);

if (!empty($_POST)) {
    $testIntegration->sendLead($_POST);

    $logger->writeLog('Debug', 'Success');
} else {
    $logger->writeLog('Debug', 'Post is empty');
}

?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>

    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
<header class="fcf-body">
    <a class="fcf-label" href="table.php"> Statuses </a>
</header>
<main>
    <div class="fcf-body">

        <div id="fcf-form">
            <form id="fcf-form-id" class="fcf-form-class" method="post">

                <div class="fcf-form-group">
                    <label for="first_name" class="fcf-label">Your first name</label>
                    <div class="fcf-input-group">
                        <input type="text" id="first_name" name="first_name" class="fcf-form-control" required>
                    </div>
                </div>

                <div class="fcf-form-group">
                    <label for="last_name" class="fcf-label">Your last name</label>
                    <div class="fcf-input-group">
                        <input type="text" id="last_name" name="last_name" class="fcf-form-control" required>
                    </div>
                </div>

                <div class="fcf-form-group">
                    <label for="email" class="fcf-label">Your email address</label>
                    <div class="fcf-input-group">
                        <input type="email" id="email" name="email" class="fcf-form-control" required>
                    </div>
                </div>

                <div class="fcf-form-group">
                    <label for="phone" class="fcf-label">Your phone</label>
                    <div class="fcf-input-group">
                        <input type="tel" id="phone" name="phone" class="fcf-form-control" required>
                    </div>
                </div>

                <div class="fcf-form-group">
                    <button type="submit" id="fcf-button" class="fcf-btn fcf-btn-primary fcf-btn-lg fcf-btn-block">
                        Send
                    </button>
                </div>

            </form>
        </div>

    </div>
</main>
</body>
</html>