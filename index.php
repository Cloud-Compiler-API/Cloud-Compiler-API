<?php
    require_once __DIR__ . '/include/php/autoload.php';

    $klein = new \Klein\Klein();

    $klein->respond('GET', '/Cloud-Compiler-API/hello-world', function () {
        return 'Hello World!';
    });

    $klein->dispatch();
?>
<!--<!DOCTYPE html>
<html>
<head>
    <title>
        Cloud Compiler API Test
    </title>
</head>
<body>
    <form enctype="multipart/form-data" action="api/submissions/" method="post">
        Source Code:<br>
        <textarea name="sourceCode" cols="30" rows="10"></textarea><br>
        stdin:<br>
        <input type="text" name="stdin"><br>
        Language Id:<br>
        <input type="text" name="langId"><br>
        Time Limit: <br>
        <input type="radio" name="timeLimit" value="0"> 5s
        <input type="radio" name="timeLimit" value="1"> 15s<br>
        <input type="submit" value="Submit">
    </form>
</body>
</html>-->