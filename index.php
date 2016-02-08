<?php
    require_once __DIR__ . '/include/php/autoload.php';
    require_once __DIR__ . '/api/v1/init.php';

    $klein = new \Klein\Klein();

    $klein->respond(array('GET', 'POST'), $GLOBALS['config']['serverRoot'], function () {
        return "<h2>Welcome to the Cloud Compiler API</h2><p>Check out the documentation for the API <a href='docs/'>here</a></p>";
    });

    $klein->respond(array('GET', 'POST'), $GLOBALS['config']['serverRoot'] . 'docs/', function () {
        return file_get_contents(__DIR__ . '/docs/index.html');
    });

    $klein->respond(array('GET', 'POST'), $GLOBALS['config']['serverRoot'] . 'api/languages[/]?', function ($request, $response) {
        $responseData = \API\Languages\Response();
        $response->code($responseData['code']);
        $response->json($responseData['body']);
    });

    $klein->respond(array('GET', 'POST'), $GLOBALS['config']['serverRoot'] . 'api/languages/template/[i:id][/]?', function ($request, $response) {
        $responseData = \API\Languages\Template\Response($request->id);
        $response->code($responseData['code']);
        $response->json($responseData['body']);
    });

    $klein->respond(array('GET', 'POST'), $GLOBALS['config']['serverRoot'] . 'api/languages/sample/[i:id][/]?', function ($request, $response) {
        $responseData = \API\Languages\Sample\Response($request->id);
        $response->code($responseData['code']);
        $response->json($responseData['body']);
    });

    $klein->respond(array('GET', 'POST'), $GLOBALS['config']['serverRoot'] . 'api/maintain[/]?', function ($request, $response) {
        $responseData = \API\Maintain\Response();
        $response->code($responseData['code']);
        $response->json($responseData['body']);
    });

    $klein->respond(array('GET', 'POST'), $GLOBALS['config']['serverRoot'] . 'api/submissions/[a:id][/]?', function ($request, $response) {
        $responseData = \API\Submissions\Response($request->id);
        $response->code($responseData['code']);
        $response->json($responseData['body']);
    });

    $klein->respond(array('GET', 'POST'), $GLOBALS['config']['serverRoot'] . 'api/submissions[/]?', function ($request, $response) {
        $responseData = \API\Submissions\Response();
        $response->code($responseData['code']);
        $response->json($responseData['body']);
    });

    $klein->onHttpError(function ($code, $router) {
        switch ($code) {
            case 400:
                // Leave no message as a json file will be returned
                break;
            case 404:
                $router->response()->body(
                    'Unable to find the requested page. Please verify the url.'
                );
                break;
            case 405:
                $router->response()->body(
                    'Requested method is not allowed.'
                );
                break;
            case 500:
                // Leave no message as a json file will be returned
                break;
            default:
                $router->response()->body(
                    'Something wrong happened and caused a '. $code . ' error.'
                );
        }
    });

    $klein->dispatch();

    exit();