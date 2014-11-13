<?php

use Symfony\Component\HttpFoundation\Request;

$app->get('/admin/services/', function() use ($app) {
    $services = $app['repository.service']->findAll();
    return $app['twig']->render('services/list.twig', [
        'services' => $services
    ]);
});

$app->get('/admin/services/new/', function() use ($app) {
    return $app['twig']->render('services/form.twig');
});

$app->post('/admin/services/new/', function(Request $request) use ($app) {
    $data = $request->request->all();
    $app['repository.service']->create($data);
    return $app->redirect('/admin/services/');
});

$app->get('/admin/services/{id}/', function($id) use ($app) {
    $service = $app['repository.service']->find($id);
    return $app['twig']->render('services/form.twig', [
        'service' => $service
    ]);
});

$app->post('/admin/services/{id}/', function($id, Request $request) use ($app) {
    $data = $request->request->all();
    $app['repository.service']->update($id, $data);
    return $app->redirect('/admin/services/');
});
