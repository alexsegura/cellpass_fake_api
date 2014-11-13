<?php

use Symfony\Component\HttpFoundation\Request;

$app->get('/admin/subscriptions/', function() use ($app) {
    $subscriptions = $app['repository.subscription']->findAll();
    return $app['twig']->render('subscriptions/list.twig', [
        'subscriptions' => $subscriptions
    ]);
});
