<?php

$app['db']->query('CREATE TABLE IF NOT EXISTS cellpass_transaction (
    transaction_id TEXT NOT NULL,
    service_id INTEGER NOT NULL,
    editor_id INTEGER NOT NULL,
    customer_editor_id TEXT DEFAULT NULL,
    state TEXT DEFAULT NULL,
    state_value TEXT DEFAULT NULL,
    url_ok TEXT NOT NULL,
    url_ko TEXT DEFAULT NULL,
    success INTEGER DEFAULT NULL,
    ctime TEXT NOT NULL,
    mtime TEXT NOT NULL
)');

$app['db']->query('CREATE TABLE IF NOT EXISTS cellpass_service (
    id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    name TEXT DEFAULT NULL
)');

$app['db']->query('CREATE TABLE IF NOT EXISTS cellpass_offer (
    id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    service_id INTEGER NOT NULL,
    name TEXT NOT NULL,
    type TEXT NOT NULL,
    price INTEGER NOT NULL
)');

$app['db']->query('CREATE TABLE IF NOT EXISTS cellpass_customer (
    id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    editor_id TEXT DEFAULT NULL
)');

$app['db']->query('CREATE TABLE IF NOT EXISTS cellpass_sub (
    id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    customer_id INTEGER NOT NULL,
    offer_id INTEGER NOT NULL,
    date_sub TEXT NOT NULL,
    date_unsub TEXT NOT NULL,
    date_eff_unsub TEXT NOT NULL
)');
