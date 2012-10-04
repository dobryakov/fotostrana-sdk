<?php

if (isset($_GET['amount']) && isset($_GET['viewerId']) && isset($_GET['sessionKey'])) {

    require_once('fotostrana.config.php');
    require_once('fotostrana.sdk.php');

    $amount = round($_GET['amount'], 2);

    $request = new fotostranaRequest();
    $request->setMethod('Billing.withDrawMoneySafe');
    $request->setParam('userId', FOTOSTRANA_VIEWER_ID);
    $request->setParam('money', $amount);
    $apiresult = $request->get();

    if (!isset($apiresult['response']['transferred']) || $apiresult['response']['transferred']<>$amount) {
        throw new Exception("Billing problem: ".serialize($apiresult));
    } else {
        echo (serialize($apiresult));
    }

}

?>