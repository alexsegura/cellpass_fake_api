<?php

use Symfony\Component\HttpFoundation\Request;

$app->get('/cellpass/init/', function (Request $request) use ($app) {

    $data = $request->query->all();

    $data['type'] = 'init';

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

$app->get('/cellpass/init_resil/', function (Request $request) use ($app) {

    $data = $request->query->all();

    $data['type'] = 'init_resil';

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

    $customer = $app['repository.customer']->findByEditorId($transaction['customer_editor_id']);

    $json = [
        'id' => $transaction_id,
        'url_ok' => $transaction['url_ok'],
        'url_ko' => $transaction['url_ko'],
        'customer_operator_id' => null,
        'customer_ip' => $_SERVER['REMOTE_ADDR'],
        'state' => 'end',
        'state_value' => $transaction['state_value'],
        'error' => $transaction['error'],
        'error_code' => $transaction['error_code'],
        'ctime' => $transaction['ctime'],
        'mtime' => $transaction['mtime'],
        'success' => $transaction['success'] === null ? null : (bool) $transaction['success'],
        'service_id' => $offer['service_id'],
        'type_asked' => null,
        'mode_asked' => '',
        'customer_id' => $customer['id'],
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

    if (isset($transaction['subscription_id'])) {
        $subscription = $app['repository.subscription']->find($transaction['subscription_id']);
        $json['subscription'] = $subscription;
    }

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

$app->get('/cellpass/id/', function (Request $request) use ($app) {
    $params = $request->query->all();

    return $app->json([
        'url' => 'http://' . $_SERVER['HTTP_HOST'] . '/id/?' . http_build_query($params),
        'status' => 'success'
    ]);
});


