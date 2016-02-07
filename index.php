<?php
    require_once __DIR__ . '/include/php/autoload.php';
    require_once __DIR__ . '/api/init.php';

    $klein = new \Klein\Klein();

    $klein->respond('GET', '/Cloud-Compiler-API/api/languages/', function ($request, $response) {
        $responseData = \API\Languages\Response();
        $response->json($responseData);
    });

    $klein->respond('GET', '/Cloud-Compiler-API/api/languages/template/[i:id]/', function ($request, $response) {
        $responseData = \API\Languages\Template\Response($request->id);
        $response->json($responseData);
    });

    $klein->respond('GET', '/Cloud-Compiler-API/api/languages/sample/[i:id]/', function ($request, $response) {
        $responseData = \API\Languages\Sample\Response($request->id);
        $response->json($responseData);
    });

    $klein->respond('GET', '/Cloud-Compiler-API/api/maintain/', function ($request, $response) {
        $responseData = \API\Maintain\Response();
        $response->json($responseData);
    });

    $klein->respond('GET', '/Cloud-Compiler-API/api/submissions/[:id]/', function ($request, $response) {
        $responseData = \API\Submissions\Response($request->id);
        $response->json($responseData);
    });

    $klein->respond('POST', '/Cloud-Compiler-API/api/submissions/', function ($request, $response) {
        $responseData = \API\Submissions\Response();
        $response->json($responseData);
    });

    $klein->respond('GET', '/Cloud-Compiler-API/docs/', function () {
        return file_get_contents(__DIR__ . '/docs/index.html');
    });

    $klein->dispatch();

    exit();