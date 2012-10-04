<?php

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

?>