<?php

header ('Content-type: text/html; charset=utf-8');

require_once('fotostrana.config.php');
require_once('fotostrana.sdk.php');
$fotostrana = new fotostrana();

session_start();

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

        var APP_ID = "<?=FOTOSTRANA_APPID?>";
        var APP_CLIENT_KEY = "<?=FOTOSTRANA_CLIENTKEY?>";
        var VIEWER_ID = "<?=FOTOSTRANA_VIEWER_ID?>";
        var SESSION_KEY = "<?=FOTOSTRANA_SESSIONKEY?>";

        var errorCallBack = function() { console.log("API Error!"); };
        var fsapi_url = getURLParameter('fsapi');
        var dumpData = function (ds) { console.log(ds.response); }

        $.ajaxSetup({
            cache: true
        });

        function spendMoney(amount)
        {
            if (api) {
                api.event("spendMoney", withDrawMoney, {amount: amount});
            }
        };

        function buyItem(name, amount, pic_url, exchange)
        {
            if (!exchange) {
                exchange = "<?=FOTOSTRANA_EXCHANGE?>";
            }
            if (api) {
                api.event("buyItem", withDrawMoney, { name: name, amount: amount, pic_url: pic_url, exchange: exchange });
            }
        };

        function withDrawMoney(amount)
        {
            if (amount && amount.money) {
                $.ajax({
                    url: 'withdrawmoney.php?amount=' + amount.money + '&viewerId=' + VIEWER_ID + '&sessionKey=' + SESSION_KEY + '&rand=' + Math.random()
                });
            }
        };

        var api = null;

        var loadApi = function() {
            $.getScript(fsapi_url, function(){
                api = new fsapi(APP_ID, APP_CLIENT_KEY);
                api.init(errorCallBack);
            });
        }

        loadApi();

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
                foreach($wall->get(2) as $item) {
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
                    <input type="button" name="" value=" Дай денег! " onclick='spendMoney(1)'>
                    <input type="button" name="" value=" Купи слона! " onclick='buyItem("Elephant", 1)'>
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

        <script type="text/javascript">
        </script>

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