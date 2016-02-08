<?php
    require_once __DIR__ . '/include/php/autoload.php';
    require_once __DIR__ . '/api/v1/init.php';

    $klein = new \Klein\Klein();

    $klein->respond('GET', $GLOBALS['config']['serverRoot'] . 'api/languages[/]?', function ($request, $response) {
        $responseData = \API\Languages\Response();
        $response->json($responseData);
    });

    $klein->respond('GET', $GLOBALS['config']['serverRoot'] . 'api/languages/template/[i:id][/]?', function ($request, $response) {
        $responseData = \API\Languages\Template\Response($request->id);
        $response->json($responseData);
    });

    $klein->respond('GET', $GLOBALS['config']['serverRoot'] . 'api/languages/sample/[i:id][/]?', function ($request, $response) {
        $responseData = \API\Languages\Sample\Response($request->id);
        $response->json($responseData);
    });

    $klein->respond('GET', $GLOBALS['config']['serverRoot'] . 'api/maintain[/]?', function ($request, $response) {
        $responseData = \API\Maintain\Response();
        $response->json($responseData);
    });

    $klein->respond('GET', $GLOBALS['config']['serverRoot'] . 'api/submissions/[a:id][/]?', function ($request, $response) {
        $responseData = \API\Submissions\Response($request->id);
        $response->json($responseData);
    });

    $klein->respond('POST', $GLOBALS['config']['serverRoot'] . 'api/submissions[/]?', function ($request, $response) {
        $responseData = \API\Submissions\Response();
        $response->json($responseData);
    });

    $klein->respond('GET', $GLOBALS['config']['serverRoot'] . 'docs/', function () {
        return file_get_contents(__DIR__ . '/docs/index.html');
    });

    $klein->dispatch();

    exit();