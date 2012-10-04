<?php

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

?>