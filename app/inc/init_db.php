<?php

$app['db']->query('CREATE TABLE IF NOT EXISTS cellpass_transaction (
    id TEXT NOT NULL,
    offer_id INTEGER NOT NULL,
    customer_editor_id TEXT DEFAULT NULL,
    state TEXT DEFAULT NULL,
    state_value TEXT DEFAULT NULL,
    url_ok TEXT NOT NULL,
    url_ko TEXT DEFAULT NULL,
    success INTEGER DEFAULT NULL,
    ctime TEXT NOT NULL,
    mtime TEXT NOT NULL
)');

$app['db']->query('CREATE TABLE IF NOT EXISTS cellpass_resil_transaction (
    id TEXT NOT NULL PRIMARY KEY,
    subscription_id TEXT NOT NULL,
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
    price INTEGER NOT NULL,
    operator TEXT NOT NULL
)');

$app['db']->query('CREATE TABLE IF NOT EXISTS cellpass_customer (
    id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    editor_id TEXT NOT NULL
)');

$app['db']->query('CREATE TABLE IF NOT EXISTS cellpass_subscription (
    id TEXT NOT NULL PRIMARY KEY,
    customer_id INTEGER NOT NULL,
    offer_id INTEGER NOT NULL,
    date_sub TEXT NOT NULL,
    date_unsub TEXT DEFAULT NULL,
    date_eff_unsub TEXT DEFAULT NULL
)');
