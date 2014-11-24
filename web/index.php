<?php

require_once __DIR__.'/../vendor/autoload.php';

ini_set('display_errors', 'on');

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Cellpass;

$app = new Silex\Application();
$app['debug'] = true;

//
// Service Providers
//

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__ . '/../app/views',
));

$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => array(
        'driver'   => 'pdo_sqlite',
        'path'     => __DIR__ . '/../app/db/app.db',
    ),
));

include __DIR__ . '/../app/inc/init_db.php';

//
// Twig filters
//

$app['twig']->addFilter('var_export', new Twig_Filter_Function('var_export'));

$app['repository.service'] = $app->share(function($app) {
    return new Cellpass\ServiceRepository($app['db']);
});

$app['repository.offer'] = $app->share(function($app) {
    return new Cellpass\OfferRepository($app['db']);
});

$app['repository.transaction'] = $app->share(function($app) {
    return new Cellpass\TransactionRepository($app['db']);
});

$app['repository.customer'] = $app->share(function($app) {
    return new Cellpass\CustomerRepository($app['db']);
});

$app['repository.subscription'] = $app->share(function($app) {
    return new Cellpass\SubscriptionRepository($app['db']);
});

$app['operators'] = $app->share(function($app) {
    return ['bt', 'orange', 'sfr', 'free'];
});

define('API_SECRET', '0123456789');

//
// Routing
//

$app->get('/admin/', function() use ($app) {
    return $app['twig']->render('index.twig');
});

require __DIR__ . '/../app/controllers/api.php';
require __DIR__ . '/../app/controllers/services.php';
require __DIR__ . '/../app/controllers/offers.php';
require __DIR__ . '/../app/controllers/transactions.php';
require __DIR__ . '/../app/controllers/subscriptions.php';
require __DIR__ . '/../app/controllers/customers.php';

$app->get('/router/', function(Request $request) use ($app) {

    $transaction_id = $request->query->get('transaction_id');

    if (!$transaction = $app['repository.transaction']->find($transaction_id)) {
        // TODO 404
    }

    switch ($transaction['type']) {
        case 'init' :
            $transaction = $app['repository.transaction']->find($transaction_id);
            $offer = $app['repository.offer']->find($transaction['offer_id']);
            $operator = $offer['operator'];

            return $app->redirect("/operator/{$operator}/?transaction_id={$transaction_id}");

            break;

        case 'init_resil' :
            $subscription = $app['repository.subscription']->find($transaction['subscription_id']);
            $offer = $app['repository.offer']->find($subscription['offer_id']);
            $operator = $offer['operator'];

            return $app->redirect("/operator/{$operator}/resil/?transaction_id={$transaction_id}");

            break;
    }
});

$app->get('/operator/{operator}/', function(Request $request) use ($app) {

    $transaction_id = $request->query->get('transaction_id');

    if (!$transaction = $app['repository.transaction']->find($transaction_id)) {
        // TODO 404
    }

    $app['repository.transaction']->updateState($transaction_id, 'route_to_billing');

    $offer = $app['repository.offer']->find($transaction['offer_id']);

    $states = Cellpass\StateValue::toArray();

    return $app['twig']->render('operator.twig', [
        'transaction' => $transaction,
        'offer' => $offer,
        'states' => $states
    ]);
});

$app->post('/operator/{operator}/', function(Request $request) use ($app) {

    $transaction_id = $request->query->get('transaction_id');
    $success = (null !== $request->request->get('confirm'));

    $app['repository.transaction']->update($transaction_id, [
        'success' => $success ? 1 : 0
    ]);

    $transaction = $app['repository.transaction']->find($transaction_id);

    if ($success) {

        if (!$customer = $app['repository.customer']->findByEditorId($transaction['customer_editor_id'])) {
            $customer = $app['repository.customer']->create([
                'editor_id' => $transaction['customer_editor_id']
            ]);
        }

        $app['repository.subscription']->create([
            'id' => $transaction['id'],
            'customer_id' => $customer['id'],
            'offer_id' => $transaction['offer_id']
        ]);

        $redirect_url = $transaction['url_ok'];
    } else {

        $state = null !== $request->request->get('state') ? $request->request->get('state') : 'CLIENT_CANCEL';
        $app['repository.transaction']->update($transaction_id, [
            'error_code' => $state,
            'state_value' => Cellpass\StateValue::getValue($state)
        ]);

        $redirect_url = $transaction['url_ko'] ?: $transaction['url_ok'];
    }

    $url = $redirect_url . '?transaction_id=' . $transaction_id;

    return $app->redirect($url);
});


$app->get('/operator/{operator}/resil/', function(Request $request) use ($app) {

    $transaction_id = $request->query->get('transaction_id');

    if (!$transaction = $app['repository.transaction']->find($transaction_id)) {
        // TODO 404
    }

    $subscription = $app['repository.subscription']->find($transaction['subscription_id']);
    $offer = $app['repository.offer']->find($subscription['offer_id']);

    return $app['twig']->render('resil_operator.twig', [
        'transaction' => $transaction,
        'offer' => $offer
    ]);
});

$app->post('/operator/{operator}/resil/', function(Request $request) use ($app) {

    $success = (null !== $request->request->get('confirm'));
    $transaction_id = $request->query->get('transaction_id');

    if (!$transaction = $app['repository.transaction']->find($transaction_id)) {
        // TODO 404
    }

    $app['repository.transaction']->update($transaction_id, [
        'success' => $success ? 1 : 0
    ]);

    if ($success) {

        $subscription = $app['repository.subscription']->update($transaction['subscription_id'], [
            'date_unsub' => date('Y-m-d H:i:s'),
            'date_eff_unsub' => date('Y-m-d H:i:s', strtotime('+1 MONTH'))
        ]);

        $redirect_url = $transaction['url_ok'];
    } else {
        $redirect_url = $transaction['url_ko'] ?: $transaction['url_ok'];
    }

    $url = $redirect_url . '?transaction_id=' . $transaction_id;

    return $app->redirect($url);
});

$app->get('/id/', function (Request $request) use ($app) {

    $params = $request->query->all();

    $url = $params['url'];
    unset(
        $params['url'],
        $params['ts'],
        $params['rnd'],
        $params['sign']
    );

    $customer_params = [
        'customer_ip' => $_SERVER['REMOTE_ADDR'],
        'customer_operator' => 'Orange',
        'customer_operator_type' => 'box',
        'customer_operator_country' => 'FR',
        'customer_handset_type' => 'PC'
    ];

    $url .= '&' . http_build_query(array_merge($customer_params, $params));

    return $app->redirect(Cellpass\Utils::signURL($url));
});

$app->run();
