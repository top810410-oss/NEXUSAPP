<?php
	$dir = "/www/wwwroot/im/http/public/static/chat/9999/20220331/";
	$db = "im";
		exec("mongodump -h 127.0.0.1 --port 28018 -o  ".$dir);
		exec("cd ".$dir."&& tar -cvf db.tar -R ".$dir.$db);
?>