<?php

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
            case 'ofriendspets':
                return $this->getFriendsPets();
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
            case 'marketDiscount':
                return $this->getMarketDiscount();
                break;
            case 'offerUrl':
                return $this->getOfferUrl();
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

?>