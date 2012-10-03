<?php

    define('FINAL_URL', '/sdk/example.php');
    session_start();
    unset ($_SESSION['FOTOSTRANA_SESSIONKEY']);
    header("Location: ".FINAL_URL);

?>