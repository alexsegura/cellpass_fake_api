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

$db = new Cellpass\Db($app['db']);

//
// Routing
//

$app->get('/db/', function() use ($app) {

    $stmt = $app['db']->prepare('SELECT * FROM cellpass_transaction');
    $stmt->execute();

    $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

    array_walk($rows, function(&$row) {
        $row['success'] = $row['success'] === null ? null : (bool) $row['success'];
    });

    return $app['twig']->render('db.twig', [
        'rows' => $rows
    ]);
});

$app->post('/db/clear/', function() use ($app) {

    $stmt = $app['db']->prepare('DELETE FROM cellpass_transaction');
    $stmt->execute();

    return $app->redirect('/db/');
});

$app->get('/operator/', function(Request $request) use ($app) {

    $transaction_id = $request->query->get('transaction_id');

    $stmt = $app['db']->prepare('SELECT url_ok, url_ko FROM cellpass_transaction WHERE transaction_id = :transaction_id');
    $stmt->bindValue(':transaction_id', $transaction_id);
    $stmt->execute();

    if (!$row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
        // TODO Error handling
    }

    return $app['twig']->render('operator.twig', [
        'transaction_id' => $transaction_id,
        'url_ok' => $row['url_ok'],
        'url_ko' => $row['url_ko']
    ]);
});

$app->post('/operator/', function(Request $request) use ($app) {

    $confirm = $request->request->get('confirm');
    $transaction_id = $request->query->get('transaction_id');

    $stmt = $app['db']->prepare('UPDATE cellpass_transaction SET success = :success, mtime = DATETIME("NOW") WHERE transaction_id = :transaction_id');
    $stmt->bindValue(':success', $confirm ? 1 : 0);
    $stmt->bindValue(':transaction_id', $transaction_id);
    $stmt->execute();

    $stmt = $app['db']->prepare('SELECT url_ok, url_ko FROM cellpass_transaction WHERE transaction_id = :transaction_id');
    $stmt->bindValue(':transaction_id', $transaction_id);
    $stmt->execute();

    $row = $stmt->fetch(\PDO::FETCH_ASSOC);

    $url = ($confirm ? $row['url_ok'] : $row['url_ko']) . '?transaction_id=' . $transaction_id;

    return $app->redirect($url);
});

$app->get('/cellpass/init/', function (Request $request) use ($app) {

    $transaction_id = md5(time());
    $service_id = $request->query->get('service_id');
    $editor_id = $request->query->get('editor_id');
    $customer_editor_id = $request->query->get('customer_editor_id');
    $url_ok = $request->query->get('url_ok');
    $url_ko = $request->query->get('url_ko');

    if (!$url_ko) {
        $url_ko = $url_ok;
    }

    $sql = 'INSERT INTO cellpass_transaction (transaction_id, service_id, editor_id, customer_editor_id, state, url_ok, url_ko, ctime, mtime)'
        . ' VALUES '
        . '(:transaction_id, :service_id, :editor_id, :customer_editor_id, :state, :url_ok, :url_ko, DATETIME("now"), DATETIME("now"))';

    $stmt = $app['db']->prepare($sql);
    $stmt->bindValue(':transaction_id', $transaction_id);
    $stmt->bindValue(':service_id', $service_id);
    $stmt->bindValue(':editor_id', $editor_id);
    $stmt->bindValue(':customer_editor_id', $customer_editor_id);
    $stmt->bindValue(':state', 'init');
    $stmt->bindValue(':url_ok', $url_ok);
    $stmt->bindValue(':url_ko', $url_ko);
    $stmt->execute();

    $json = [
        'transaction_id' => $transaction_id,
        'url' => 'http://' . $_SERVER['HTTP_HOST'] . '/operator/?transaction_id=' . $transaction_id,
        'status' => 'success'
    ];

    return $app->json($json);
});

$app->get('/cellpass/end/', function (Request $request) use ($app, $db) {

    $transaction_id = $request->query->get('transaction_id');
    $editor_id = $request->query->get('editor_id');

    $db->endTransaction($transaction_id, $editor_id);

    if (!$row = $db->getTransaction($transaction_id)) {
        // TODO Error handling
    }

    $json = [
        'id' => $transaction_id,
        'url_ok' => $row['url_ok'],
        'url_ko' => $row['url_ko'],
        'customer_operator_id' => null,
        'customer_ip' => $_SERVER['REMOTE_ADDR'],
        'state' => 'end',
        'state_value' => '', // Client cancel the billing
        'error' => '',
        'error_code' => !$row['success'] ? 'CLIENT_CANCEL' : '', // CLIENT_CANCEL
        'ctime' => $row['ctime'],
        'mtime' => $row['mtime'],
        'success' => $row['success'] === null ? null : (bool) $row['success'],
        'service_id' => $row['service_id'],
        'type_asked' => null,
        'mode_asked' => '',
        'customer_id' => null,
        'customer_editor_id' => $row['customer_editor_id'],
        'offer_id' => null,
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

$app->run();
