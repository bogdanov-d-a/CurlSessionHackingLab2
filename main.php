<?php

include('post_facade.php');

echo(PostFacade::LoginAndPost($_GET['username'], $_GET['password'], $_GET['comment']));

?>
