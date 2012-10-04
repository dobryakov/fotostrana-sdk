<?php

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

?>