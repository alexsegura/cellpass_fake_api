<?php

use Symfony\Component\HttpFoundation\Request;

$app->get('/admin/customers/', function() use ($app) {
    $customers = $app['repository.customer']->findAll();
    return $app['twig']->render('customers/list.twig', [
        'customers' => $customers
    ]);
});
