<?php

// провер€ем referer запроса, чтобы предотвратить вызов скрипта с других доменов
$host = isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] ? $_SERVER['HTTP_HOST'] : false;
$referer = isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] ? $_SERVER['HTTP_REFERER'] : false;
if ($referer) {
    $u = parse_url($referer, PHP_URL_HOST);
    if ($u) {
        if ($u!==$host) {
            throw new Exception("Calls from other domain is forbidden");
        }
    }
}

// анализируем запрос
if (isset($_GET['amount']) && isset($_GET['viewerId']) && isset($_GET['sessionKey'])) {

    require_once('fotostrana.config.php');
    require_once('fotostrana.sdk.php');

    $amount = round($_GET['amount'], 2);

    $request = new fotostranaRequest();
    $request->setMethod('Billing.withDrawMoneySafe');
    $request->setParam('userId', FOTOSTRANA_VIEWER_ID);
    $request->setParam('money', $amount);
    $request->disallowCache();
    $apiresult = $request->get();

    if (!isset($apiresult['response']['transferred']) || $apiresult['response']['transferred']<>$amount) {
        throw new Exception("Billing problem: ".serialize($apiresult));
    } else {
        echo (serialize($apiresult));
    }

}

?>