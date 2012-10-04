<?php

require_once('fotostrana.config.php');
require_once('fotostrana.base.php');
require_once('fotostrana.object.php');
require_once('fotostrana.user.php');
require_once('fotostrana.wall.php');
require_once('fotostrana.community.php');
require_once('fotostrana.pet.php');
require_once('fotostrana.request.php');
require_once('fotostrana.subrequest.php');
require_once('fotostrana.requestscounter.php');
require_once('fotostrana.requestscache.php');
require_once('fotostrana.error.php');

/**
 * Основной класс Fotostrana SDK
 */
class fotostrana
{

    private $cache = array();

    function __construct()
    {

        $this->selfTest();

        if (!defined('FOTOSTRANA_DEBUG')) {
            define('FOTOSTRANA_DEBUG', 0);
        }

        if (!defined('FOTOSTRANA_API_BASEURL')) {
            define('FOTOSTRANA_API_BASEURL','http://fotostrana.ru/apifs.php');
        }

        $this->flushCache();
    }

    /*function getOAuthLink($client_id=false, $scope=false, $callback=false)
    {

        if (!$client_id) { $client_id = FOTOSTRANA_APPID; }
        if (!$scope)     { $scope     = FOTOSTRANA_REQUIRED_PERMISSIONS; }
        if (!$callback)  { $callback  = FOTOSTRANA_OAUTH_CALLBACK; }
        return FOTOSTRANA_URL . '/api/oauth/authorize/?client_id=' . $client_id . '&scope=' . $scope . '&redirect_uri=' . $callback;
    }*/

    function getUser($user_id)
    {
        if (!array_key_exists($user_id, $this->cache['users'])) {
            $this->cache['users'][$user_id] = new fotostranaUser($user_id);
        }
        return $this->cache['users'][$user_id];
    }

    function getWall($user_id)
    {
        if (!array_key_exists($user_id, $this->cache['walls'])) {
            $this->cache['walls'][$user_id] = new fotostranaWall($user_id);
        }
        return $this->cache['walls'][$user_id];
    }

    function getAppBalance()
    {
        $r = new fotostranaRequest();
        $r->setMethod('Billing.getAppBalance');
        $apiresult = $r->get();
        if (isset($apiresult['response']['balance'])) {
            return $apiresult['response']['balance'];
        }
    }

    function searchUsersAsArray($params=array())
    {

        if (array_key_exists('prefix'.serialize($params), $this->cache['search'])) {
            return $this->cache['search']['prefix'.serialize($params)];
        } else {

            $r = new fotostranaRequest();
            $r->setMethod('User.getFromSearch');
            $r->setParams($params);
            $apiresult = $r->get();
            $final = $apiresult['response'];

            $this->cache['search']['prefix'.serialize($params)] = $final;
            return $final;
        }
    }

    function searchUsers($params=array())
    {
        $result = $this->searchUsersAsArray($params);
        $final = array();
        if (is_array($result) && $result) {
            foreach ($result as $u) {
                $final[$u['user_id']] = $this->getUser($u['user_id']);
            }
        }
        return $final;
    }

    function flushCache()
    {
        $this->cache=array();
        $this->cache['users']=array();
        $this->cache['search']=array();
        $this->cache['walls']=array();
    }

    function selfTest()
    {

        // тестируем текущее окружение
        // должны быть разрешены file_get_contents и CURL

        $t = true;

        if (!ini_get('allow_url_fopen')) {
            $t = false;
        }
        if (ini_get('safe_mode')) {
            $t = false;
        }
        if (!in_array('curl', get_loaded_extensions())) {
            $t = false;
        }
        if (!$t) {
            echo ("Check your configuration: you must disable safe_mode and enable allow_url_fopen in php.ini, and install CURL extension to PHP.<br>\n");
            die;
        }

    }

}

?>