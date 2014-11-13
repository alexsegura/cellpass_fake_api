<?php

use Symfony\Component\HttpFoundation\Request;

$app->get('/admin/offers/', function() use ($app) {
    $offers = $app['repository.offer']->findAll();
    return $app['twig']->render('offers/list.twig', [
        'offers' => $offers
    ]);
});

$app->get('/admin/offers/new/', function() use ($app) {
    $services = $app['repository.service']->findAll();
    return $app['twig']->render('offers/form.twig', [
        'services' => $services,
        'operators' => $app['operators']
    ]);
});

$app->post('/admin/offers/new/', function(Request $request) use ($app) {
    $data = $request->request->all();
    $app['repository.offer']->create($data);
    return $app->redirect('/admin/offers/');
});

$app->get('/admin/offers/{id}/', function($id) use ($app) {
    $offer = $app['repository.offer']->find($id);
    $services = $app['repository.service']->findAll();
    return $app['twig']->render('offers/form.twig', [
        'offer' => $offer,
        'services' => $services,
        'operators' => $app['operators']
    ]);
});

$app->post('/admin/offers/{id}/', function($id, Request $request) use ($app) {
    $data = $request->request->all();
    $app['repository.offer']->update($id, $data);
    return $app->redirect('/admin/offers/');
});
