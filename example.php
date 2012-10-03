<?php

header ('Content-type: text/html; charset=utf-8');

define('FOTOSTRANA_APPID', 'sdkdemo');
define('FOTOSTRANA_CLIENTKEY', 'a84aedc74dda60a3cf35a96683ae0947');
define('FOTOSTRANA_SERVERKEY', 'c95e03377ca085d6f82c9b39fbf9336e');

define('FOTOSTRANA_URL', 'http://fs98.vs58.net');
define('FOTOSTRANA_API_BASEURL', 'http://fs98.vs58.net/apifs.php');
define('FOTOSTRANA_OAUTH_CALLBACK', 'http://'.$_SERVER['HTTP_HOST'].'/sdk/callback-example.php');
define('FOTOSTRANA_REQUIRED_PERMISSIONS', 'basic,friends');
define('FOTOSTRANA_DEBUG', 0);

require_once('fotostrana.sdk.php');
$fotostrana = new fotostrana();

session_start();

define('FOTOSTRANA_SESSIONKEY', $_REQUEST['sessionKey']);
define('FOTOSTRANA_VIEWER_ID', $_REQUEST['viewerId']);

if (!FOTOSTRANA_SESSIONKEY || !FOTOSTRANA_VIEWER_ID)
{
    ?>
        <div style="width: 800px; margin: 15% auto;text-align: center;">
            <h1>
                Fotostrana SDK Demo Site
            </h1>
            <p>
                Добро пожаловать на standalone-сайт, демонстрирующий возможности PHP SDK Фотостраны.
            </p>
            <!--p>
                <a href="<?= $fotostrana->getOAuthLink() ?>">Авторизоваться с логином Фотостраны</a>
            </p-->
        </div>
    <?
    die();
}

//define('FOTOSTRANA_SESSIONKEY', $_SESSION['FOTOSTRANA_SESSIONKEY']); // '5069480573313f191f74d1e6768941c6a47895c74b60cf');
//define('FOTOSTRANA_VIEWER_ID', $_SESSION['FOTOSTRANA_VIEWER_ID']); // '60713086');

$user = new fotostranaUser(FOTOSTRANA_VIEWER_ID); // равнозначно $user = $fotostrana->getUser(FOTOSTRANA_VIEWER_ID);
$wall = new fotostranaWall(FOTOSTRANA_VIEWER_ID); // равнозначно вызову $wall = $fotostrana->getWall(FOTOSTRANA_VIEWER_ID); или $wall = $user->wall();
$pet  = $user->pet();

// выдаём ачивку
// var_dump($user->giveAchievment(245)));

// запрашиваем купон
// var_dump($user->getMarketDiscount());

// запрашиваем оффер url
// var_dump($user->getOfferUrl());

// запрашиваем петов друзей
/*$friends_pets = $user->getFriendsPets();
foreach ($friends_pets as $_pet) {
    var_dump($_pet->user()->user_name);
}*/

?>

<html>
    <head>
        <title>Fotostrana SDK Example</title>
        <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
        <link rel="stylesheet" type="text/css" media="all" href="http://a5.s.fsimg.ru/base/css/__v16062011_1343818039.common.css"/>
        <style type="text/css">
            body
            {
                background-color: #fffacd;
            }
            #content
            {
                border:0px blue solid;
                border-radius: 10px;
                width: 800px;
                margin: 20px auto;
            }
            .content_inline
            {
                display: inline-block;
                border:1px #999999 solid;
                border-radius: 4px;
                padding: 6px;
                vertical-align: top;
            }
        </style>
    </head>

    <script type="text/javascript">

        window.projectDomain = 'apitest.vs58.net';

        function getURLParameter(name) { return decodeURI((RegExp(name + '=' + '(.+?)(&|$)').exec(location.search)||[,null])[1]); };

        /*function LoadScript(src){
            var el=document.createElement('script');
            el.setAttribute('src',src);
            el.setAttribute('type','text/javascript');
            document.getElementsByTagName('head')[0].appendChild(el);
            return el;
        };*/

        var APP_ID = "<?=FOTOSTRANA_APPID?>";
        var APP_CLIENT_KEY = "<?=FOTOSTRANA_CLIENTKEY?>";
        var errorCallBack = function() { console.log("API Error!"); };
        var fsapi_url = getURLParameter('fsapi');

        /*if (LoadScript(fsapi_url)) {
            var client = new fsapi(APP_ID, APP_CLIENT_KEY);
            client.init(errorCallBack);
        }*/

        /*$.ajaxSetup({
            cache: true
        });

        function callSpendMoney() { alert('x'); };

        function callApiEvent(name, callback, params) {

            $.getScript(fsapi_url, function(){

                var client = new fsapi(APP_ID, APP_CLIENT_KEY);
                client.init(errorCallBack);
                client.event(name, alert('x'), params);

            });
        };*/

        function callSpendMoney(d)
        {
            //console.log('-------------------------------------------------');
            //console.log(d);
            $.ajax({
                url: 'withdrawmoney.php?amount=' + d.money
            });
        }

        $.ajaxSetup({
            cache: true
        });

        function callApiEvent(name, callback, params) {
            $.getScript(fsapi_url, function(){
                var client = new fsapi(APP_ID, APP_CLIENT_KEY);
                client.init(errorCallBack);
                client.event(name, callback, params);
            });
        }

    </script>

    <body>

        <div id="content">

            <div class="fs-content-box profile-header fs-ie nclear">
                <ul class="hBar">
                    <li class="first">
                        <a class="active"
                           href="<?=FOTOSTRANA_URL?>/user/<?= $user->id ?>/?from=profile">Профиль</a>
                    </li>
                    <li>
                        <a
                           href="<?=FOTOSTRANA_URL?>/user/<?= $user->id ?>/blog/?from=profile">Блог</a>
                    </li>
                    <li>
                        <a
                           href="<?=FOTOSTRANA_URL?>/user/<?= $user->id ?>/albums/?from=profile">Фотки</a>
                    </li>
                    <li>
                        <a
                           href="<?=FOTOSTRANA_URL?>/user/<?= $user->id ?>/wall/?from=profile">Стена</a>
                    </li>
                </ul>
                <h2>
                    <a href="<?=FOTOSTRANA_URL?>/user/<?= $user->id ?>/?from=profile"><?= $user->user_name ?></a>
                </h2>

                <div class="fs-bar-city-user">
                    День рождения <?= $user->birthday ?>,<br/>живёт в городе <?= $user->city_name ?></div>
            </div>

            <? if ($pet->class) { ?>
            <div class="fs-content-box profile-header fs-ie nclear">
                <h4>
                    <?= $user->user_name ?> и <?= $user->sex=='m' ? 'его' : 'ее' ?> питомец - лучшие друзья:
                </h4>
                <br/>
                <div class="content_inline"><img src='<?= $user->photo_97; ?>' border="0" alt=""/></div>
                <div class="content_inline"><img src='<?= $user->pet()->image; ?>' border="0" alt="" style="height:97px;"/></div>
                <div class="content_inline" style="border:0px;">
                    <h4>
                        <?= $pet->name ?> (<?= $pet->getTypename(); ?>)
                    </h4>
                    <p>
                        День рождения <?= current(explode(' ',$pet->birthday)) ?>,
                        <br/>
                        в кошельке есть <?= $pet->pet_money ?> заработанных монет.
                    </p>
                </div>
            </div>
            <? } ?>

            <div class="fs-content-box profile-header fs-ie nclear">
                <h4>
                    Друзья:
                </h4>
                <div style="margin-top: 10px; margin-bottom: 5px;">
                    <?
                    foreach ($user->ofriends as $_user) {
                        ?>
                        <div style="display: inline-block;">
                            <a href="/user/<?=$_user->id?>"><img src="<?= $_user->photo_97; ?>" alt="" border="0"/></a>
                            <br/>
                            <?=$_user->user_name?>
                        </div>
                        <?
                    }
                    ?>
                </div>
            </div>

            <div class="fs-content-box profile-header fs-ie nclear">
                <p>
                    Запостить картиночку <?= ($user->sex=='m' ? 'ему' : 'ей') ?> на стену:
                </p>
                <p>
                    <br/>
                    <form action="" method="post" enctype="multipart/form-data">
                        <input style="width:400px;" type="text" name="text" value="Привет, <?= $user->user_name ?>! Вот тебе картиночка на стену :)"/>
                        <input type="file" name="foto-img"/>
                        <input type="submit" value=" Отправить! "/>
                    </form>
                </p>
                <br/>
                <p>
                    Последние сообщения со стены:
                </p>
                <?
                foreach($wall->get() as $item) {
                    ?>
                    <p>
                        <br/>
                        Опубликовано: <?=$item['inserted']?>
                        <br/>
                        <?=strip_tags($item['text'])?>
                        <br/>
                    </p>
                    <?
                }
                ?>
            </div>

            <div class="fs-content-box profile-header fs-ie nclear">
                <p>
                    Попросить денег:
                </p>
                <p>
                    <input type="button" name="" value=" Дай денег! " onclick='callApiEvent("buyItem", callSpendMoney, {name: "test", amount: 1});'>
                </p>
            </div>

            <!--div class="fs-content-box profile-header fs-ie nclear">
                <p>

                    Тестовый запрос к API через token:

                    <?php

                        /*$params = array();
                        $params['params']['s'] = iconv('utf-8','windows-1251','вот эта строчка получена средствами API через токен!');
                        $params['access_token'] = $_SESSION['FOTOSTRANA_TOKEN'];
                        $params['method'] = 'User_Interface_Base::echoTest';

                        $url = FOTOSTRANA_URL.'/api/oauth/request/?'.http_build_query($params);
                        $s = json_decode(file_get_contents($url));

                        echo $s->response;*/

                    ?>

                </p>
            </div-->


            <!--p style="text-align: right;">
                <a href="logout-example.php">Выход</a>
            </p-->

            <p>
                Баланс пользователя:
                <?=$user->balance?>
                ФМ
                <br/>
                Баланс приложения:
                <?=$fotostrana->getAppBalance()?>
                ФМ
            </p>

        </div>

    </body>
</html>

<?php

// постим картинку на стену

if ($_FILES) {
    $ext=explode('/',$_FILES['foto-img']['type']);
    $imagepath = $_SERVER['DOCUMENT_ROOT'].'/sdk/image.'.$ext[1];
    move_uploaded_file ($_FILES['foto-img']['tmp_name'], $imagepath);
    $wall->postImage($_REQUEST['text'],$imagepath);
}

?>