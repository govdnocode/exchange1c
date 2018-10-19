<?php
/**
 * This file is part of bigperson/exchange1c package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

require_once './../vendor/autoload.php';

$configValues = [
    'import_dir'    => '1c_exchange',
    'login'         => 'admin',
    'password'      => 'admin',
    'use_zip'       => false,
    'file_part'     => 0,
    'models'        => [
        \Bigperson\Exchange1C\Interfaces\GroupInterface::class   => \Tests\Models\GroupTestModel::class,
        \Bigperson\Exchange1C\Interfaces\ProductInterface::class => \Tests\Models\ProductTestModel::class,
        \Bigperson\Exchange1C\Interfaces\OfferInterface::class   => \Tests\Models\OfferTestModel::class,
    ],
];
$config = new \Bigperson\Exchange1C\Config($configValues);
$request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
$dispatcher = new \Symfony\Component\EventDispatcher\EventDispatcher();
$modelBuilder = new \Bigperson\Exchange1C\ModelBuilder();
$loaderService = new \Bigperson\Exchange1C\Services\FileLoaderService($request, $config);
$authService = new \Bigperson\Exchange1C\Services\AuthService($request, $config);
$categoryService = new \Bigperson\Exchange1C\Services\CategoryService($request, $config, $dispatcher, $modelBuilder);
$offerService = new \Bigperson\Exchange1C\Services\OfferService($request, $config, $dispatcher, $modelBuilder);
$catalogService = new \Bigperson\Exchange1C\Services\CatalogService($request, $config, $authService, $loaderService, $categoryService, $offerService);

$mode = $request->get('mode');
$type = $request->get('type');

try {
    if ($type == 'catalog') {
        if (!method_exists($catalogService, $mode)) {
            throw new Exception('not correct request, mode='.$mode);
        }
        $body = $catalogService->$mode();
        $response = new \Symfony\Component\HttpFoundation\Response($body, 200, ['Content-Type', 'text/plain']);
        $response->send();
    } else {
        throw new \LogicException(sprintf('Logic for method %s not released', $type));
    }
} catch (\Exception $e) {
    $body = "failure\n";
    $body .= $e->getMessage()."\n";
    $body .= $e->getFile()."\n";
    $body .= $e->getLine()."\n";

    $response = new \Symfony\Component\HttpFoundation\Response($body, 500, ['Content-Type', 'text/plain']);
    $response->send();
}
