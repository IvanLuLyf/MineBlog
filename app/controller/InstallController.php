<?php
/**
 * Created by PhpStorm.
 * User: IvanLu
 * Date: 2018/9/28
 * Time: 17:12
 */

namespace MineBlog\Controller;

use BunnyPHP\Config;
use BunnyPHP\Controller;
use BunnyPHP\Model;
use Exception;
use MineBlog\Model\UserModel;
use PDO;

class InstallController extends Controller
{
    public function ac_index()
    {
        if (Config::checkLock('install')) {
            $this->assign('err_msg', '检测到./config/install.lock请先删除后安装本程序');
            $this->render('install/error.html');
        } else {
            $this->render('install/index.html');
        }
    }

    public function ac_step1()
    {
        if (Config::checkLock('install')) {
            $this->assign('err_msg', '检测到./config/install.lock请先删除后安装本程序');
            $this->render('install/error.html');
        } else {
            $this->render('install/step1.html');
        }
    }

    public function ac_step2($db_type, $db_host, $db_port, $db_name, $db_user, $db_pass, $db_prefix)
    {
        if (Config::checkLock('install')) {
            $this->assign('err_msg', '检测到./config/install.lock请先删除后安装本程序');
            $this->render('install/error.html');
        } else {
            if ($db_type == 'mysql') {
                $dsn = "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4";
            } elseif ($db_type == 'pgsql') {
                $dsn = "pgsql:host=$db_host;dbname=$db_name;port=$db_port";
            } else {
                $dsn = "sqlite:$db_name";
            }
            $option = array(PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC);
            try {
                $pdo = new PDO($dsn, $db_user, $db_pass, $option);
                if ($pdo != null) {
                    session_start();
                    $db_info = [
                        'type' => $db_type,
                        'host' => $db_host,
                        'port' => $db_port,
                        'username' => $db_user,
                        'password' => $db_pass,
                        'database' => $db_name,
                        'prefix' => $db_prefix,
                    ];
                    $_SESSION['db_info'] = $db_info;
                    $this->render('install/step2.html');
                } else {
                    $this->assign('err_msg', '无法连接数据库请检查配置');
                    $this->render('install/error.html');
                }
            } catch (Exception $e) {
                $this->assign('err_msg', '无法连接数据库请检查配置');
                $this->render('install/error.html');
            }
        }
    }

    public function ac_step3($username, $password, $email, $nickname, $site_name, $site_url)
    {
        if (Config::checkLock('install')) {
            $this->assign('err_msg', '检测到./config/install.lock请先删除后安装本程序');
            $this->render('install/error.html');
        } else {
            session_start();
            $db_info = $_SESSION['db_info'];
            define('DB_TYPE', $db_info['type']);
            define('DB_HOST', $db_info['host']);
            define('DB_PORT', $db_info['port']);
            define('DB_NAME', $db_info['database']);
            define('DB_USER', $db_info['username']);
            define('DB_PASS', $db_info['password']);
            define('DB_PREFIX', $db_info['prefix']);

            $nickname = ($nickname != '') ? $nickname : $username;

            $models = scandir(APP_PATH . "app/model");
            /**
             * @var $modelClass Model
             */
            foreach ($models as $model) {
                if (substr($model, -9) == "Model.php") {
                    $modelClass = TP_NAMESPACE.'\\Model\\'.substr($model, 0, -4);
                    $modelClass::create();
                }
            }
            (new UserModel())->register($username, $password, $email, $nickname);
            $config_file = fopen(APP_PATH . "config/config.php", "w") or die("Unable to open file!");
            $config = Config::make([
                'db' => $db_info,
                'site_name' => $site_name,
                'site_url' => $site_url,
                'controller' => 'Index',
                'allow_reg' => isset($_POST['allow_reg']),
            ]);
            fwrite($config_file, $config);
            fclose($config_file);

            $lock_file = fopen(APP_PATH . "config/install.lock", "w") or die("Unable to open file!");
            fclose($lock_file);
            $this->render('install/success.html');
        }
    }
}