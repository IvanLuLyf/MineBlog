<?php
if (file_exists("./config/install.lock")) {
    $err_msg = "检测到./config/install.lock请先删除后安装本程序";
    include 'install/error.html';
} else {
    $step = isset($_GET['step']) ? $_GET['step'] : 0;
    switch ($step) {
        case 1:
            include 'install/step1.html';
            break;
        case 2:
            $dsn = "mysql:host=" . $_POST['db_host'] . ";dbname=" . $_POST['db_name'] . ";charset=utf8mb4";
            $option = array(PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC);
            try {
                $pdo = new PDO($dsn, $_POST['db_user'], $_POST['db_pass'], $option);
                if ($pdo != null) {
                    session_start();
                    $db_info = [
                        'host' => $_POST['db_host'],
                        'username' => $_POST['db_user'],
                        'password' => $_POST['db_pass'],
                        'database' => $_POST['db_name']
                    ];
                    $_SESSION['db_info'] = $db_info;
                    include 'install/step2.html';
                    break;
                } else {
                    $err_msg = "无法连接数据库请检查配置";
                    include 'install/error.html';
                }
            } catch (Exception $e) {
                $err_msg = "无法连接数据库请检查配置";
                include 'install/error.html';
            }
            break;
        case 3:
            session_start();
            $db_info = $_SESSION['db_info'];
            $dsn = "mysql:host=" . $db_info['host'] . ";dbname=" . $db_info['database'] . ";charset=utf8mb4";
            $username = $_POST['username'];
            $password = md5($_POST['password']);
            $email = $_POST['email'];
            $site_name = $_POST['site_name'];
            $option = array(PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC);
            try {
                $pdo = new PDO($dsn, $db_info['username'], $db_info['password'], $option);
                $pdo->exec("CREATE TABLE `tp_user` (`id` int(20) NOT NULL AUTO_INCREMENT,`username` varchar(16) NOT NULL,`password` varchar(32) NOT NULL,`nickname` varchar(32) DEFAULT NULL,`email` text NOT NULL,`token` text NOT NULL,`expire` text,primary key (`id`));");
                $pdo->exec("CREATE TABLE `tp_blog` (`tid` int(20) NOT NULL AUTO_INCREMENT,`username` varchar(16) NOT NULL,`nickname` varchar(32) DEFAULT NULL,`title` text NOT NULL,`message` text,`timeline` text NOT NULL,`view_num` int(11) DEFAULT '0',`comment_num` int(11) DEFAULT '0',`like_num` int(11) DEFAULT '0',primary key (`tid`));");
                $statement = $pdo->prepare("INSERT INTO `tp_user` (`username`, `password`, `nickname`, `email`,`token`) VALUES (:username,:password,:username,:email,:password);");
                $statement->bindValue(':username',$username);
                $statement->bindValue(':password',$password);
                $statement->bindValue(':email',$email);
                $statement->execute();
                $config_file = fopen("./config/config.php", "w") or die("Unable to open file!");
                fwrite($config_file, "<?php\n");
                fwrite($config_file, '$config[\'db\'][\'host\'] = \'' . $db_info['host'] . "';\n");
                fwrite($config_file, '$config[\'db\'][\'username\'] = \'' . $db_info['username'] . "';\n");
                fwrite($config_file, '$config[\'db\'][\'password\'] = \'' . $db_info['password'] . "';\n");
                fwrite($config_file, '$config[\'db\'][\'database\'] = \'' . $db_info['database'] . "';\n");
                fwrite($config_file, '$config[\'site_name\'] = \'' . $site_name . "';\n");
                fwrite($config_file, '$config[\'site_url\'] = \'' . $_SERVER['SERVER_NAME'] . "';\n");
                fwrite($config_file, '$config[\'controller\'] = \'Index' . "';\n");
                fwrite($config_file, '$config[\'action\'] = \'index' . "';\n");
                fwrite($config_file, '$config[\'storage\'][\'name\'] = \'File' . "';\n");
                fwrite($config_file, 'return $config;');
                fclose($config_file);

                $lock_file = fopen("./config/install.lock", "w") or die("Unable to open file!");
                fclose($lock_file);
                include 'install/success.html';
            } catch (Exception $e) {
                $err_msg = "无法连接数据库请检查配置";
                include 'install/error.html';
            }
            break;
        default:
            include 'install/index.html';
            break;
    }
}