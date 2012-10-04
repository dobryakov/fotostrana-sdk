<?php

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

?>