<?php

use Symfony\Component\HttpFoundation\Request;

$app->get('/admin/transactions/', function() use ($app) {
    $transactions = $app['repository.transaction']->findAll();
    return $app['twig']->render('transactions/list.twig', [
        'transactions' => $transactions
    ]);
});
