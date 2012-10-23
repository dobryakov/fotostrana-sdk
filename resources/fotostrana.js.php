<?php
    require_once('fotostrana.config.php');
?>

<script type="text/javascript">

    function getURLParameter(name) { return decodeURI((RegExp(name + '=' + '(.+?)(&|$)').exec(location.search)||[,null])[1]); };

    var APP_ID = "<?=FOTOSTRANA_APPID?>";
    var APP_CLIENT_KEY = "<?=FOTOSTRANA_CLIENTKEY?>";
    var VIEWER_ID = "<?=FOTOSTRANA_VIEWER_ID?>";
    var SESSION_KEY = "<?=FOTOSTRANA_SESSION_KEY?>";

    var errorCallBack = function() { console.log("API Error!"); };
    var fsapi_url = getURLParameter('fsapi');

    // необходимо для локального тестирования
    window.projectDomain = String(getURLParameter('apiUrl')).split( '%2F' )[2];

    var dumpData = function (ds) { console.log(ds.response); }

    $.ajaxSetup({
        cache:true
    });

    function spendMoney(amount) {
        if (api) {
            api.event("spendMoney", withDrawMoney, {amount:amount});
        }
    };

    function buyItem(name, amount, exchange, item_id, currency_names, pic_url) {
        if (!exchange) {
            exchange = "<?=FOTOSTRANA_EXCHANGE?>";
        }
        if (api) {
            api.event("buyItem", withDrawMoney, { name:name, amount:amount, exchange:exchange, id:item_id, currency_names:currency_names, pic_url:pic_url });
        }
    };

    function withDrawMoney(params) {
        if (params && params.money) {
            $.ajax({
                url:'resources/withdrawmoney.php?amount=' + params.money + '&item=' + params.item + '&viewerId=' + VIEWER_ID + '&sessionKey=' + SESSION_KEY + '&rand=' + Math.random()
            });
        }
    };

    function appSettings(request_permission) {
        if (api) {
            if (request_permission) {
                api.event("appSettings", function () {
                }, {"request_permission":request_permission});
            } else {
                api.event("appSettings");
            }
        }
    };

    var api = null;

    var loadApi = function () {
        $.getScript(fsapi_url, function () {
            api = new fsapi(APP_ID, APP_CLIENT_KEY);
            api.init(errorCallBack);
        });
    };

    loadApi();

</script>

<?php
?>