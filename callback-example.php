<?php

    define('FOTOSTRANA_URL', 'http://fs98.vs58.net');
    define('FINAL_URL', '/sdk/example.php');

    if ($_REQUEST && isset($_REQUEST['error'])) {
        echo ('Here you can handle OAuth errors.');
        die();
    }

    if ($_REQUEST && isset($_REQUEST['code'])) {

        // формируем запрос на получение токена
        $url = FOTOSTRANA_URL.'/api/oauth/access_token?code='.urlencode($_REQUEST['code']);
        $res = file_get_contents($url);
        //var_dump($res);

        if ($res) {

            $t = json_decode($res);
            $token = $t->access_token;

            if ($token) {

                // формируем запрос на получение credentials
                $url = FOTOSTRANA_URL.'/api/oauth/credentials?access_token='.urlencode($token);
                //var_dump($url);
                $res = file_get_contents($url);
                //var_dump($res);

                if ($res) {

                    $t = json_decode($res);
                    $sessionKey = $t->sessionKey;
                    $viewerId = $t->viewerId;

                    if ($sessionKey && $viewerId) {
                        session_start();
                        $_SESSION['FOTOSTRANA_SESSIONKEY']=$sessionKey;
                        $_SESSION['FOTOSTRANA_VIEWER_ID']=$viewerId;
                        $_SESSION['FOTOSTRANA_TOKEN']=$token;
                        header("Location: ".FINAL_URL);
                    }

                }

            }

        }

    }

?>