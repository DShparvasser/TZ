<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>

    <link rel="stylesheet" href="css/style.css">

    <script>
        function clearGetParams() {
            window.location.href = window.location.pathname;
        }
    </script>
</head>
<body>
<header class="fcf-body">
    <a class="fcf-label" href="/"> Dashboard </a>
</header>
<div class="fcf-body" style="margin-top: 15px">
    <form method="get">
        <div class="fcf-form-group">
            <label for="date_start" class="fcf-label">Date start</label>
            <div class="fcf-input-group">
                <input type="date" id="date_start" name="date_start" class="fcf-form-control" required>
            </div>
        </div>
        <div class="fcf-form-group">
            <label for="date_end" class="fcf-label">Date end</label>
            <div class="fcf-input-group">
                <input type="date" id="date_end" name="date_end" class="fcf-form-control" required>
            </div>
        </div>
        <div style="display: flex; justify-content: center">
            <button type="submit" id="fcf-button" class="fcf-btn fcf-btn-primary fcf-btn-lg fcf-btn-block"
                    style="margin-top: 15px">
                Start filter
            </button>
            <button onclick="clearGetParams()" type="reset" id="fcf-button" class="fcf-btn-reset fcf-btn-reset fcf-btn-lg fcf-btn-block"
                    style="margin-top: 15px; margin-left: 10px">
                Reset filter
            </button>
        </div>
    </form>
</div>
<div style='margin: 0 auto; display: flex; justify-content: center; flex-direction: column;'>
    <?php

    if (!class_exists('DB'))
        include_once 'DB.php';

    $db = DB::initConnection();

    $startDate = date('Y-m-d H:i:s', strtotime($_GET['date_start']));
    $endDate = date('Y-m-d H:i:s', strtotime($_GET['date_end']));

    if (!empty($_GET['date_start']) && !empty($_GET['date_end'])) {
        $result = $db->select('leads', 'id, email, status, ftd, created_at', "created_at >= '$startDate' AND created_at <= '$endDate'");
    } else {
        $result = $db->select('leads', 'id, email, status, ftd, created_at');
    }

    if ($result) {
        echo "<table class='fcf-body' style='margin-top: 15px'><tr><th>Id</th><th>Email</th><th>Status</th><th>Ftd</th><th>Created at</th></tr>";
        foreach ($result as $row) {
            echo "<tr>";
            echo "<td>" . $row["id"] . "</td>";
            echo "<td>" . $row["email"] . "</td>";
            echo "<td>" . $row["status"] . "</td>";
            echo "<td>" . $row["ftd"] . "</td>";
            echo "<td>" . $row["created_at"] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        $result->free();
    } else {
        echo "Ошибка: " . $db->error;
    }
    ?>
    <form method="post">
        <button type="submit" id="fcf-button" class="fcf-btn fcf-btn-primary fcf-btn-lg fcf-btn-block"
                style="margin-top: 15px">
            Get statuses
        </button>
    </form>

    <?php
    if (!class_exists('TestIntegration'))
        require_once 'TestIntegration.php';

    if (!class_exists('CustomLogger'))
        require_once 'CustomLogger.php';

    $logger = new CustomLogger();

    $testIntegration = new TestIntegration($logger, $db);

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $testIntegration->updateStatuses();
    }

    ?>
</div>
</body>
</html>