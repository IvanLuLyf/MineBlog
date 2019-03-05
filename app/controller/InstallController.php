<?php
/**
 * Created by PhpStorm.
 * User: IvanLu
 * Date: 2018/9/28
 * Time: 17:12
 */

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

    public function ac_step2()
    {
        if (Config::checkLock('install')) {
            $this->assign('err_msg', '检测到./config/install.lock请先删除后安装本程序');
            $this->render('install/error.html');
        } else {
            if ($_POST['db_type'] == 'mysql') {
                $dsn = "mysql:host=" . $_POST['db_host'] . ";dbname=" . $_POST['db_name'] . ";charset=utf8mb4";
                $db_host = $_POST['db_host'];
                $db_port = $_POST['db_port'];
                $db_user = $_POST['db_user'];
                $db_pass = $_POST['db_pass'];
            } elseif ($_POST['db_type'] == 'pgsql') {
                $dsn = "pgsql:host=" . $_POST['db_host'] . ";dbname=" . $_POST['db_name'] . ";port=" . $_POST['db_port'];
                $db_host = $_POST['db_host'];
                $db_port = $_POST['db_port'];
                $db_user = $_POST['db_user'];
                $db_pass = $_POST['db_pass'];
            } else {
                $dsn = "sqlite:" . $_POST['db_name'];
                $db_host = '';
                $db_port = '';
                $db_user = '';
                $db_pass = '';
            }
            $option = array(PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC);
            try {
                $pdo = new PDO($dsn, $db_user, $db_pass, $option);
                if ($pdo != null) {
                    session_start();
                    $db_info = [
                        'type' => $_POST['db_type'],
                        'host' => $db_host,
                        'port' => $db_port,
                        'username' => $db_user,
                        'password' => $db_pass,
                        'database' => $_POST['db_name'],
                        'prefix' => $_POST['db_prefix'],
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

    public function ac_step3()
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


            $username = $_POST['username'];
            $password = md5($_POST['password']);
            $email = $_POST['email'];
            $nickname = (isset($_POST['nickname']) && $_POST['nickname'] != '') ? $_POST['nickname'] : $username;
            $site_name = $_POST['site_name'];
            $db_prefix = $db_info['prefix'];
            
            $models = scandir(APP_PATH . "app/model");
            /**
             * @var $modelClass Model
             */
            foreach ($models as $model) {
                if (substr($model, -9) == "Model.php") {
                    $modelClass = substr($model, 0, -4);
                    $modelClass::create();
                }
            }

            Database::getInstance()->insert(['username' => $username, 'password' => $password, 'email' => $email, 'nickname' => $nickname, 'token' => ''], $db_prefix . 'user');
            $config_file = fopen(APP_PATH . "config/config.php", "w") or die("Unable to open file!");
            $config = Config::make([
                'db' => $db_info,
                'site_name' => $site_name,
                'controller' => 'Index',
                'action' => 'index',
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