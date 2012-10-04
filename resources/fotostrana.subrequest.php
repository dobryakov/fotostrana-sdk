<?php

/**
 * Подкласс, формирующий URL и SIG для запроса к API
 */
class fotostranaSubRequest
{

    private $server_methods = array(
        'User.giveFBAchievment',
        'User.sendNotification',
        'Userphoto.checkAccess',
        'Billing.getUserBalanceAny',
        'Billing.withDrawMoneySafe',
        'User.sendAppEmail',
        'User.giveAchievment',
        'User.getAuthInfo'
        // добавьте методы при необходимости
    );

    private function makeSig(array $params) {

        ksort($params);

        if (in_array($params['method'],$this->server_methods)) {
            $p_string='';
        } else {
            $p_string=FOTOSTRANA_VIEWER_ID;
        }

        foreach ($params as $k=>$v)
        {
            if ($k && $v) {
                if (is_array($v)) {
                    $p_string .= str_replace('&', '', urldecode(http_build_query(array($k => $v))));
                }
                else {
                    $p_string .= $k . '=' . $v;
                }
            }
        }

        if (in_array($params['method'],$this->server_methods)) {
            $p_string.=FOTOSTRANA_SERVERKEY;
        } else {
            $p_string.=FOTOSTRANA_CLIENTKEY;
        }

        if (FOTOSTRANA_DEBUG) { echo "p_string: ".$p_string."<br/><br/>\n"; }

        $sig = md5($p_string);
        return $sig;

    }

    function makeApiRequestUrl(array $params) {

        if (!array_key_exists('appId',$params))     { $params['appId']=FOTOSTRANA_APPID; }
        if (!array_key_exists('timestamp',$params)) { $params['timestamp']=time(); }
        if (!array_key_exists('format',$params))    { $params['format']=1; }
        if (!array_key_exists('rand',$params))      { $params['rand']=rand(1,999999); }

        if (!in_array($params['method'],$this->server_methods)) {
            $params['sessionKey'] = FOTOSTRANA_SESSIONKEY;
            $params['viewerId'] = FOTOSTRANA_VIEWER_ID;
        }

        ksort($params);
        $url=FOTOSTRANA_API_BASEURL.'?sig='.$this->makeSig($params);

        foreach ($params as $k=>$v)
        {
            if ($k && $v) {
                if (is_array($v)) {
                    $url .= '&' . urldecode(http_build_query(array($k => $v)));
                }
                else {
                    $url .= '&' . $k . '=' . urlencode($v);
                }
            }
        }

        if (!in_array($params['method'],$this->server_methods)) {
            $url.='&sessionKey='.FOTOSTRANA_SESSIONKEY.'&viewerId='.FOTOSTRANA_VIEWER_ID;
        }

        if (FOTOSTRANA_DEBUG) { echo "URL: ".htmlspecialchars($url)."<br/><br/>\n"; }

        return $url;

    }

}

?>