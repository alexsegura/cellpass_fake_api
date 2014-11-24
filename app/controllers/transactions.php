<?php

use Symfony\Component\HttpFoundation\Request;

$app->get('/admin/transactions/', function() use ($app) {
    $transactions = $app['repository.transaction']->findAll();
    return $app['twig']->render('transactions/list.twig', [
        'transactions' => $transactions
    ]);
});

$app->get('/admin/transactions/{id}/', function($id) use ($app) {
    $transaction = $app['repository.transaction']->find($id);
    return $app['twig']->render('transactions/view.twig', [
        'transaction' => $transaction
    ]);
});
