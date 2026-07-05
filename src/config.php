<?php
// Configuración principal - CAMBIA TODO lo que corresponda
return [
    'ADMIN_PASSWORD' => 'CAMBIAR_ADMIN_PASSWORD', // cambia esto antes de usar
    'BTC_ADDRESS' => 'TU_DIRECCION_BTC_AQUI',
    'USDT_ADDRESS' => 'TU_DIRECCION_USDT_AQUI',
    'PRICE_USD' => 100,
    'CURRENCY' => 'BTC/USDT',
    'API_KEYS_FILE' => __DIR__.'/keys.json',
    'RATE_DELAY_MS' => 500, // milisegundos entre correos para reducir bloqueo
];
