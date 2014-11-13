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

//
// Routing
//

$app->get('/admin/', function() use ($app) {
    return $app['twig']->render('index.twig');
});

require __DIR__ . '/../app/controllers/services.php';
require __DIR__ . '/../app/controllers/offers.php';
require __DIR__ . '/../app/controllers/transactions.php';
require __DIR__ . '/../app/controllers/subscriptions.php';
require __DIR__ . '/../app/controllers/customers.php';

$app->get('/router/', function(Request $request) use ($app) {

    $transaction_id = $request->query->get('transaction_id');

    $transaction = $app['repository.transaction']->find($transaction_id);
    $offer = $app['repository.offer']->find($transaction['offer_id']);

    $operator = $offer['operator'];

    return $app->redirect("/operator/{$operator}/?transaction_id={$transaction_id}");
});

$app->get('/operator/{operator}/', function(Request $request) use ($app) {

    $transaction_id = $request->query->get('transaction_id');

    if (!$transaction = $app['repository.transaction']->find($transaction_id)) {
        // TODO 404
    }

    $app['repository.transaction']->updateState($transaction_id, 'route_to_billing');

    $offer = $app['repository.offer']->find($transaction['offer_id']);

    return $app['twig']->render('operator.twig', [
        'transaction' => $transaction,
        'offer' => $offer
    ]);
});

$app->post('/operator/{operator}/', function(Request $request) use ($app) {

    $success = (null !== $request->request->get('confirm'));
    $transaction_id = $request->query->get('transaction_id');

    $app['repository.transaction']->updateSuccess($transaction_id, $success);

    $transaction = $app['repository.transaction']->find($transaction_id);

    if ($success) {

        $customer = $app['repository.customer']->create([
            'editor_id' => $transaction['customer_editor_id']
        ]);

        $app['repository.subscription']->create([
            'id' => $transaction['id'],
            'customer_id' => $customer['id'],
            'offer_id' => $transaction['offer_id']
        ]);

        $redirect_url = $transaction['url_ok'];
    } else {
        $redirect_url = $transaction['url_ko'] ?: $transaction['url_ok'];
    }

    $url = $redirect_url . '?transaction_id=' . $transaction_id;

    return $app->redirect($url);
});

$app->get('/cellpass/init/', function (Request $request) use ($app) {

    $data = $request->query->all();

    $offerRepository = $app['repository.offer'];
    $offers = $offerRepository->findByServiceId($data['service_id']);

    shuffle($offers);
    $offer = array_shift($offers);

    $data['offer_id'] = $offer['id'];

    $transaction_id = $app['repository.transaction']->create($data);

    $json = [
        'transaction_id' => $transaction_id,
        'url' => 'http://' . $_SERVER['HTTP_HOST'] . '/router/?transaction_id=' . $transaction_id,
        'status' => 'success'
    ];

    return $app->json($json);
});

$app->get('/cellpass/end/', function (Request $request) use ($app) {

    $transaction_id = $request->query->get('transaction_id');

    if (!$transaction = $app['repository.transaction']->find($transaction_id)) {
        // TODO 404
    }

    $app['repository.transaction']->updateState($transaction_id, 'end');

    $offer = $app['repository.offer']->find($transaction['offer_id']);

    $json = [
        'id' => $transaction_id,
        'url_ok' => $transaction['url_ok'],
        'url_ko' => $transaction['url_ko'],
        'customer_operator_id' => null,
        'customer_ip' => $_SERVER['REMOTE_ADDR'],
        'state' => 'end',
        'state_value' => '', // Client cancel the billing
        'error' => '',
        'error_code' => !$transaction['success'] ? 'CLIENT_CANCEL' : '', // CLIENT_CANCEL
        'ctime' => $transaction['ctime'],
        'mtime' => $transaction['mtime'],
        'success' => $transaction['success'] === null ? null : (bool) $transaction['success'],
        'service_id' => $offer['service_id'],
        'type_asked' => null,
        'mode_asked' => '',
        'customer_id' => 123,
        'customer_editor_id' => $transaction['customer_editor_id'],
        'offer_id' => $offer['id'],
        'mode_used' => 'auto_best',
        'customer_handset_type' => 'PC',
        'customer_operator' => 'SFR',
        'customer_operator_type' => 'box',
        'customer_operator_country' => 'FR',
        'mc' => '',
        'status' => 'success'
    ];

    return $app->json($json);
});

$app->get('/cellpass/synchro/', function (Request $request) use ($app) {

    $sql = 'SELECT s.id, o.service_id, s.offer_id, s.customer_id, c.editor_id AS customer_editor_id, o.operator AS offer_operator, s.date_sub, s.date_unsub, s.date_eff_unsub'
    . ' FROM cellpass_subscription s'
    . ' JOIN cellpass_customer c ON s.customer_id = c.id'
    . ' JOIN cellpass_offer o ON s.offer_id = o.id';

    $stmt = $app['db']->prepare($sql);
    $stmt->execute();

    $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

    array_walk($rows, function(&$row) {
        $row['type'] = 'sub';
    });

    $json = [
        'data' => $rows,
        'status' => 'success'
    ];

    return $app->json($json);
});

$app->run();
