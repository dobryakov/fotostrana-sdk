<?php

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

    function getOAuthLink($client_id=false, $scope=false, $callback=false)
    {

        if (!$client_id) { $client_id = FOTOSTRANA_APPID; }
        if (!$scope)     { $scope     = FOTOSTRANA_REQUIRED_PERMISSIONS; }
        if (!$callback)  { $callback  = FOTOSTRANA_OAUTH_CALLBACK; }
        return FOTOSTRANA_URL . '/api/oauth/authorize/?client_id=' . $client_id . '&scope=' . $scope . '&redirect_uri=' . $callback;
    }

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

/**
 * Класс базовых операций низкого уровня (кэширование php-объектов и т.д.)
 */
class fotostranaBase
{

    protected $ocache = array(); // кэш объектов php

    function getFromOCache($key)
    {
        if (array_key_exists($key, $this->ocache)) {
            return $this->ocache[$key];
        }
    }

    function putToOCache($key, $object)
    {
        $this->ocache[$key]=$object;
    }

}

/**
 * Класс абстрактного объекта API Фотостраны (пользователя, изображения, стены и т.д.)
 */
class fotostranaObject extends fotostranaBase
{

    protected $data = array();

    function request()
    {
        if (!$this->getFromOCache('request')) {
            $this->putToOCache('request',new fotostranaRequest());
        }
        return $this->getFromOCache('request');
    }

    function loadData()
    {

    }

    function flushData()
    {
        $this->data = array();
    }

    function __get($key)
    {
        if (!$this->data) {
            $this->loadData();
        }
        if (!is_array($this->data)) {
            $this->data = array();
        }
        if (array_key_exists($key, $this->data)) {
            return $this->getByKey($key);
        }
    }

    protected function getByKey($key)
    {
        return $this->data[$key];
    }

}

/**
 * Класс объекта-пользователя
 */
class fotostranaUser extends fotostranaObject
{

    private $user_id;

    function loadData()
    {
        $r = $this->request();
        $r->setMethod('User.getProfiles');
        $r->setParam('userIds', $this->user_id);
        $r->setParam('fields','user_name,user_lastname,user_link,sex,birthday,photo_small,photo_97,photo_192,photo_big,photo_box,pet_id,city_id,city_name,slogan,vip_end,is_payable');
        $apiresult = $r->get();

        if ($apiresult['response'][$this->user_id]) {
            $this->data = $apiresult['response'][$this->user_id];
        } else {
            $this->data = array();
            throw new fotostranaError('API object is not loaded.');
        }
    }

    function __construct($user_id)
    {
        $this->user_id=$user_id;
    }

    function __get($key)
    {
        switch ($key) {
            case 'id':
                return $this->user_id;
                break;
            case 'registrationDate':
                return $this->getRegistrationDate();
                break;
            case 'friends':
                return $this->getFriends();
                break;
            case 'ofriends':
                return $this->getFriendsAsObjects();
                break;
            case 'isAppWidgetUser':
                return $this->getIsAppWidgetUser();
                break;
            case 'settings':
                return $this->getUserSettings();
                break;
            case 'installed':
                return $this->settings['installed'];
                break;
            case 'balance':
                return $this->settings['balance'];
                break;
            case 'bitmask':
                return $this->settings['0'];
                break;
            default:
                return parent::__get($key);
        }
    }

    function getRegistrationDate()
    {
        if (!array_key_exists('registrationDate',$this->data)) {
            $r = $this->request();
            $r->setMethod('User.getRegistrationDate');
            $r->setParam('userId', $this->user_id);
            $apiresult = $r->get();
            $this->data['registrationDate'] = $apiresult['response'];
        }
        return $this->data['registrationDate'];
    }

    function getFriends()
    {
        if (!array_key_exists('friends',$this->data)) {
            $r = $this->request();
            $r->setMethod('User.getFriendsAny');
            $r->setParam('userId', $this->user_id);
            $apiresult = $r->get();
            $this->data['friends'] = $apiresult['response'];
        }
        return $this->data['friends'];
    }

    function getFriendsAsObjects()
    {
        if (!array_key_exists('friends_objects', $this->data)) {
            $friends = $this->getFriends();
            $this->data['friends_objects'] = array();
            if (is_array($friends)) {
                foreach ($friends as $friend_id) {
                    $this->data['friends_objects'][] = new fotostranaUser($friend_id);
                }
            }
        }
        return $this->data['friends_objects'];
    }

    function getIsAppWidgetUser()
    {
        if (!array_key_exists('isAppWidgetUser',$this->data)) {
            $r = $this->request();
            $r->setMethod('User.isAppWidgetUser');
            $r->setParam('userId', $this->user_id);
            $apiresult = $r->get();
            $this->data['isAppWidgetUser'] = $apiresult['response'];
        }
        return $this->data['isAppWidgetUser'];
    }

    function getUserSettings()
    {
        if (!array_key_exists('settings',$this->data)) {
            $r = $this->request();
            $r->setMethod('User.getUserSettingsAny');
            $r->setParam('userId', $this->user_id);
            $apiresult = $r->get();
            $this->data['settings'] = $apiresult['response'];
        }
        return $this->data['settings'];
    }

    function sendNotification($text, $params)
    {
        $r = $this->request();
        $r->setMethod('User.sendNotification');
        $r->setParam('userIds',$this->user_id);
        $r->setParam('text',$text);
        $r->setParam('params',$params);
        $apiresult = $r->get();
        return $apiresult;
    }

    function giveAchievment($achievment_id)
    {
        $r = $this->request();
        $r->setMethod('User.giveAchievment');
        $r->setParam('userId',$this->user_id);
        $r->setParam('achievId',$achievment_id);
        $apiresult = $r->get();
        return $apiresult;
    }

    function getMarketDiscount()
    {
        $r = $this->request();
        $r->setMethod('User.getMarketDiscount');
        $r->setParam('userId',$this->user_id);
        $apiresult = $r->get();
        if (isset($apiresult['response'])) {
            return $apiresult['response'];
        }
    }

    function getOfferUrl()
    {
        $r = $this->request();
        $r->setMethod('User.getOfferUrl');
        $r->setParam('userId',$this->user_id);
        $apiresult = $r->get();
        if (isset($apiresult['response'])) {
            return $apiresult['response'];
        }
    }

    function getFriendsPets()
    {
        if (!$this->data['friends_pets']) {
            $this->loadData();
            $this->data['friends_pets'] = array();
            $r = $this->request();
            $r->setMethod('Pet.getFriendsPets');
            $r->setParam('userId',$this->user_id);
            $r->setParam('fields', 'name,birthday,class,image200,image,user_name,user_photo_small,user_photo_97,user_photo_192,user_photo_big');
            $apiresult = $r->get();
            if (isset($apiresult['response']) && is_array($apiresult['response'])) {
                foreach ($apiresult['response'] as $_pet) {
                    $pet = new fotostranaPet(intval($_pet['pet_id']));
                    $pet->setUser($this);
                    $this->data['friends_pets'][] = $pet;
                }
            }
        }
        return $this->data['friends_pets'];
    }

    function wall()
    {
        if (!$this->getFromOCache('wall')) {
            $this->putToOCache('wall', new fotostranaWall($this->user_id));
        }
        return $this->getFromOCache('wall');
    }

    function pet()
    {
        if (!$this->getFromOCache('pet')) {
            $this->putToOCache('pet', new fotostranaPet($this));
        }
        return $this->getFromOCache('pet');
    }

}

/**
 * Класс объекта-стены
 */
class fotostranaWall extends fotostranaObject
{

    private $user_id;

    function post($text, $linkParams=array())
    {
        $r = $this->request();
        $r->setMethod('WallUser.appPost');
        $r->setParam('text',$text);
        $r->setParam('linkParams',$linkParams);
        $apiresult = $r->get();
        return $apiresult;
    }

    function postImage($text, $img, $linkParams=array())
    {
        if (strpos($img,'http')===0) {
            // простой запрос
            $r = $this->request();
            $r->setMethod('WallUser.appPostImage');
            $r->setParam('text',$text);
            $r->setParam('linkParams',$linkParams);
            $r->setParam('imgUrl',$img);
            $apiresult = $r->get();
        } else {
            // POST-запрос CURL-ом
            $r = $this->request();
            $r->setMethod('WallUser.appPostImage');
            $r->setParam('text',$text);
            $r->setParam('linkParams',$linkParams);
            $r->setParam('foto-img',"@".$img);
            $r->setMode('POST');
            $apiresult = $r->get();
        }
        return $apiresult;
    }

    function __construct($user_id)
    {
        $this->user_id=$user_id;
    }

    function get($limit = 3)
    {
        $r = $this->request();
        $r->setMethod('WallUser.getListsCached');
        $r->setParam('userIds',$this->user_id);
        $apiresult = $r->get();
        if (array_key_exists('response',$apiresult)) {
            if (array_key_exists(FOTOSTRANA_VIEWER_ID,$apiresult['response'])) {
                return array_slice($apiresult['response'][FOTOSTRANA_VIEWER_ID], 0, $limit);
            }
        }
    }
}

/**
 * Класс объекта Pet
 */
class fotostranaPet extends fotostranaObject
{

    private $user;
    private $pet_id;
    private $types = array(
        16 => 'собака',
        18 => 'кот',
        19 => 'кошка',
        20 => 'енот',
        23 => 'кролик',
        26 => 'панда'
    );

    /**
     * Конструктор принимает на входе объект-пользователь либо идентификатор питомца
     * @param $p
     */
    function __construct($p)
    {
        if (is_integer($p)) {
            $this->setPetId($p);
            //echo("integer<br/>");
        }
        if ($p instanceof fotostranaUser) {
            $this->setUser($p);
            //echo("object<br/>");
        }
    }

    function loadData()
    {
        $r = $this->request();
        if ($this->user instanceof fotostranaUser) {
            //echo("loading pet by user object<br/>");
            $r->setMethod('Pet.getPetsByUserIds');
            $r->setParam('userIds',$this->user->id);
            $r->setParam('fields','name,birthday,class,image200,image,user_name,user_photo_small,user_photo_97,user_photo_192,user_photo_big,pet_money');
            $apiresult = $r->get();
            if ($apiresult && is_array($apiresult) && count($apiresult)>0) {
                $p = current($apiresult);
                $this->data = current($p);
            }
        }
        if ($this->pet_id) {
            //echo("loading pet by pet_id<br/>");
            $r->setMethod('Pet.getPets');
            $r->setParam('petIds',$this->pet_id);
            $r->setParam('fields','name,birthday,class,image200,image,user_name,user_photo_small,user_photo_97,user_photo_192,user_photo_big,pet_money');
            $apiresult = $r->get();
            if ($apiresult && is_array($apiresult) && count($apiresult)>0) {
                $p = current($apiresult);
                $this->data = current($p);
                $this->user = new fotostranaUser($this->data['user_id']);
            }
        }
    }

    function getTypeName()
    {
        return iconv('windows-1251','utf-8',$this->types[$this->class]);
    }

    function setPetId($pet_id)
    {
        $this->pet_id = intval($pet_id);
    }

    function setUser(fotostranaUser $user)
    {
        $this->user = $user;
    }

    function user()
    {
        return $this->user;
    }

}

/**
 * Класс, формирующий запросы к API
 */
class fotostranaRequest
{

    private $mode='GET';
    private $method;
    private $params=array();
    private $result_raw;
    private $result_formatted;
    private $cache;
    private $cache_allowed = true;
    private $error;

    function __construct()
    {

        if (!defined('FOTOSTRANA_REQUEST_LOG')) {
            define('FOTOSTRANA_REQUEST_LOG', dirname(__FILE__).'/requests.log');
        }

        $this->flushResult();
        $this->cache = new fotostranaRequestsCache();

    }

    function setMethod($method)
    {
        $this->flushResult();
        $this->method=$method;
    }

    function setParam($name,$value)
    {
        if ($value) {
            $this->params[$name] = $value;
        }
    }

    function setParams($params=array())
    {
        $this->params=$params;
    }

    function setMode($mode)
    {
        if (strtoupper($mode)=='GET') {
            $this->mode='GET';
        } else {
            $this->mode='POST';
        }
    }

    function allowCache()
    {
        $this->cache_allowed = true;
    }

    function disallowCache()
    {
        $this->cache_allowed = false;
    }

    function get()
    {
        if (!$this->result_formatted) {
            $this->runRequest();
            $this->formatResult();
        }
        $r = $this->result_formatted;
        return $r;
    }

    function getError()
    {
        return $this->error;
    }

    private function formatResult()
    {
        if ($this->result_raw) {

            $this->result_formatted = json_decode($this->result_raw, true);

            if (array_key_exists('error',$this->result_formatted)) {
                $this->error = $this->result_formatted['error'];
                throw new fotostranaError('Error: '.$this->error['error_subcode'] . ': ' . $this->error['error_msg']);
            }

        }
    }

    private function runRequest()
    {

        // готовим запрос
        $r = new fotostranaSubRequest();
        $p = array_merge($this->params, array('method'=>$this->method));

        if ($this->cache_allowed && $cached_result = $this->cache->loadCache($p)) {
            $this->result_raw = $cached_result;
            if (FOTOSTRANA_REQUEST_LOG) {
                file_put_contents(FOTOSTRANA_REQUEST_LOG, date('r').' cache: '.$this->method.' '.serialize($this->params).' '.serialize($cached_result)."\n\n", FILE_APPEND);
            }
            return;
        }

        // делаем паузу, чтобы соблюдать требование MAX_QUERIES PER_TIME
        fotostranaRequestsCounter::addQuery();
        fotostranaRequestsCounter::wait();

        $url = $r->makeApiRequestUrl( $p );

        if (FOTOSTRANA_DEBUG) { echo "Fetching URL ".htmlspecialchars($url)." by ".$this->mode."<br>\n"; }

        // делаем запрос
        if (strtoupper($this->mode)=='GET') {
            $this->result_raw = file_get_contents($url);
        } else {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_VERBOSE, 0);
            curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)");
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->params);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $this->result_raw = curl_exec($ch);
            curl_close($ch);
        }

        if (FOTOSTRANA_REQUEST_LOG) {
            file_put_contents(FOTOSTRANA_REQUEST_LOG, date('r').' request: '.$this->method.' '.serialize($this->params).' '.$this->result_raw."\n\n", FILE_APPEND);
        }

        if ($this->cache_allowed) {
            $this->cache->storeCache($p, $this->result_raw);
        }

        if (FOTOSTRANA_DEBUG) { var_dump($this->result_raw); }
    }

    private function flushResult()
    {
        $this->method = false;
        $this->params = array();
        $this->result_raw = false;
        $this->result_formatted = false;
        $this->error = false;
        $this->mode='GET';
        $this->allowCache();
    }

}

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

/**
 * Вспомогательный класс подсчёта количества запросов, чтобы соблюдать ограничения API Фотостраны
 */
class fotostranaRequestsCounter
{
    static private $queries=array();

    const MAX_QUERIES=10; // 20 запросов
    const PER_TIME=10; // за 10 секунд

    static function addQuery()
    {
        self::$queries[time()]='';
    }

    static function removeQuery($t)
    {
        unset (self::$queries[$t]);
    }

    static function countQueries()
    {
        return count(self::$queries);
    }

    static function agingQueries()
    {
        foreach (self::$queries as $q=>$v) {
            if ($q < (time()-self::PER_TIME)) {
                unset (self::$queries[$q]);
            }
        }
    }

    static function wait()
    {
        if (FOTOSTRANA_DEBUG) { echo ("Query timeout check: query count ".self::countQueries().", max queries ".self::MAX_QUERIES." <br>\n"); }
        while (self::countQueries() >= self::MAX_QUERIES) {
                if (FOTOSTRANA_DEBUG) { echo ("MAX_QUERIES reached (query count ".self::countQueries()."), wait..<br/>\n"); }
                self::agingQueries();
                sleep(1);
        }
    }
}

/**
 * Класс, реализующий кэширование запросов к API
 * Пользователь SDK может переопределить методы хранения данных, например в MySQL
 */
class fotostranaRequestsCache
{

    private $cache_dir;

    function __construct()
    {
        $this->cache_dir = dirname(__FILE__) . '/cache/';
        if (!is_dir($this->cache_dir)) {
            mkdir($this->cache_dir, 0777, true);
        }
    }

    function storeCache($params, $data)
    {
        if ($params) {
            file_put_contents($this->cache_dir . $this->makeCacheKey($params), $this->encryptData($data));
            chmod($this->cache_dir . $this->makeCacheKey($params), 0777);
        }
    }

    function loadCache($params)
    {
        if ($params) {
            $f = $this->cache_dir . $this->makeCacheKey($params);
            if (file_exists($f)) {
                if (filemtime($f) < (time() - FOTOSTRANA_REQUESTS_CACHE_TIMEOUT)) {
                    @unlink($f);
                } else {
                    return $this->decryptData(file_get_contents($f));
                }
            }
        }
    }

    private function makeCacheKey($params)
    {
        if ($params) {
            // убираем всякие рандомные параметры
            unset($params['timestamp']);
            unset($params['rand']);
            return md5(serialize($params));
        }
    }

    // пользователь может добавить шифрование и дешифрование данных по вкусу
    private function encryptData($data) { return serialize($data); }
    private function decryptData($data) { return unserialize($data); }

}

class fotostranaError extends Exception
{

}

?>