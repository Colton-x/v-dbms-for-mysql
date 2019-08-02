<?php
error_reporting(0);
header('Access-Control-Allow-Origin:*'); //允许所有来源访问
header('Access-Control-Allow-Method:POST,GET,OPTIONS'); //允许访问的方式
header('Access-Control-Allow-Headers: x-requested-with,x-token,Content-Type');
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit();
}
if ($_GET['ac']) {
    $_GET['ac']();
}

function login()
{
    $post = file_get_contents('php://input');
    $post = json_decode($post,true);
    $token = md5($post['host'].$post['username'].$post['password']);

    // return json(['code'=>60204,'message'=>'连接失败']);
    return json(['code' => 20000, 'data' =>['token'=>$token], 'upost'=>$post]);
}

function info()
{
    // return json(['code'=>50008,'message'=>'Login failed, unable to get user details.']);
    $res['roles']        = ['admin'];
    $res['introduction'] = 'I am a super administrator';
    $res['avatar']       = 'https://wpimg.wallstcn.com/f778738c-e4f8-4870-b634-56703b4acafe.gif';
    $res['name']         = 'Super Admin';
    return json(['code' => 20000, 'data' => $res]);
}

function logout()
{
    return json(['code' => 20000, 'data' => 'success']);
}

function getDbs()
{
    $json = '
        [
          {
            "path": "",
            "component": "Layout",
            "redirect": "dashboard",
            "children": [
              {
                "path": "dashboard",
                "name": "Dashboard",
                "component": "dashboard/index",
                "meta": {
                  "title": "首页",
                  "icon": "dashboard"
                }
              }
            ]
          },
          {
             "path": "/db_list",
             "component": "Layout",
             "children": [
               {
                 "path": "design",
                 "name": "Form",
                 "component": "db_list/design",
                 "hidden": true,
                 "meta": {
                   "title": "设计表",
                   "icon": "form"
                 }
               }
             ]
           },
           {
             "path": "/db_list",
             "component": "Layout",
             "children": [
               {
                 "path": "add_table",
                 "name": "Form",
                 "component": "db_list/add_table",
                 "hidden": true,
                 "meta": {
                   "title": "新建表",
                   "icon": "form"
                 }
               }
             ]
           },
          {
            "path": "/login",
            "component": "login/index",
            "hidden": true
          },
          {
            "path": "/404",
            "component": "404",
            "hidden": true
          },
          {
            "path": "*",
            "redirect": "/404",
            "hidden": true
          }
        ]';

    $router = json_decode($json, true);

    $DB                = new Db;
    $DB->database_user = 'root';
    $DB->database_pass = '';
    $DB->database_host = '127.0.0.1';
    $DB->connect();

    $res  = $DB->fetchAll("SHOW DATABASES");
    $menu = [];
    if ($res) {
        foreach ($res as $key => $value) {
            $menu[$key]['path']      = '/db_list/' . $key;
            $menu[$key]['component'] = 'Layout';
            $menu[$key]['alias']     = $value['Database'];
            $menu[$key]['children']  = [
                [
                    'path'      => 'index/' . $key,
                    'name'      => 'db' . $key,
                    'component' => 'db_list/index',
                    'meta'      => ['title' => $value['Database'], 'icon' => 'table'],
                ],
            ];

            $tables = $DB->fetchAll("select table_name from information_schema.tables where table_schema='$value[Database]'");
            if ($tables) {
                $children = [];
                foreach ($tables as $key1 => $value1) {
                    $children[$key1]['path']      = 'index/1' . $key . $key1;
                    $children[$key1]['name']      = 'db1' . $key . $key1;
                    $children[$key1]['component'] = 'db_list/index';
                    $children[$key1]['meta']      = ['title' => $value1['table_name'], 'icon' => 'table', 'db' => $value['Database']];
                }
                $menu[$key]['children'] = $children;
                $menu[$key]['meta']     = ['title' => $value['Database'], 'icon' => 'tree'];
            }

        }
    }
    $arr = array_merge($router, $menu);
    cache('menu', $arr);
    return json(['code' => 0, 'data' => ['router' => $arr], 'msg' => 'ok']);
}

function getTables()
{
    $DB                = new Db;
    $DB->database_name = $_GET['db'];
    $DB->database_user = 'root';
    $DB->database_pass = '';
    $DB->database_host = '127.0.0.1';
    $DB->connect();

    $db_name     = $_GET['db'];
    $table_name  = $_GET['tb'];
    $page        = ($_GET['page']) ? $_GET['page'] : 1;
    $pageSize    = ($_GET['pageSize']) ? $_GET['pageSize'] : 20;
    $COLUMN_NAME = [];

    if ($table_name) {
        $res = $DB->fetchAll("select COLUMN_NAME,column_comment from information_schema.COLUMNS where table_name = '$table_name'");
        if ($res) {
            foreach ($res as $key => $value) {
                $COLUMN_NAME[$key]['prop']  = $value['COLUMN_NAME'];
                $COLUMN_NAME[$key]['label'] = ($value['column_comment']) ? $value['COLUMN_NAME'] . '(' . $value['column_comment'] . ')' : $value['COLUMN_NAME'];
            }
        }
        $count = $DB->fetch("select count(*) from $table_name");
        $start = getStart($page, $pageSize);
        $datas = $DB->fetchAll("SELECT * FROM $table_name limit $start,$pageSize");

        return json(['code' => 20000, 'data' => ['field' => $COLUMN_NAME, 'datas' => $datas, 'count' => intval($count['count(*)'])], 'message' => 'ok']);
    }

    return json(['code' => 1, 'data' => [], 'message' => 'error']);
}

function getStart($page = 1, $pageSize = 10)
{
    if ($page == 1) {
        $start = 0;
        return $start;
    }
    $start = ($page - 1) * $pageSize;
    return $start;
}

function getStructure()
{
    $DB                = new Db;
    $DB->database_name = $_GET['db'];
    $DB->database_user = 'root';
    $DB->database_pass = '';
    $DB->database_host = '127.0.0.1';
    $DB->connect();

    $db_name    = $_GET['db'];
    $table_name = $_GET['tb'];

    if ($table_name) {
        $res = $DB->fetchAll("select COLUMN_NAME, DATA_TYPE, CHARACTER_MAXIMUM_LENGTH, IS_NULLABLE, COLUMN_KEY, EXTRA, COLUMN_DEFAULT, CHARACTER_SET_NAME, COLLATION_NAME, COLUMN_COMMENT FROM INFORMATION_SCHEMA.COLUMNS where table_name = '$table_name'");
        return json(['code' => 20000, 'data' => $res, 'msg' => 'ok']);
    }
    return json(['code' => 1, 'data' => [], 'msg' => 'error']);
}

function addField()
{
    $DB                = new Db;
    $DB->database_name = $_GET['db'];
    $DB->database_user = 'root';
    $DB->database_pass = '';
    $DB->database_host = '127.0.0.1';
    $DB->connect();
    // alter table   table1 add id int unsigned not Null auto_increment primary key
    $db_name        = $_GET['db'];
    $table_name     = $_GET['tb'];
    $fieldName      = $_GET['fieldName'];
    $fieldType      = $_GET['fieldType'];
    $auto_increment = $_GET['auto_increment'];
    $unsigned       = $_GET['unsigned'];
    $len            = ($_GET['len']) ? $_GET['len'] : '1';
    $isnull         = ($_GET['isnull']) ? '' : 'not Null';
    $primary        = ($_GET['primary']) ? 'primary key' : '';
    if ($table_name) {
        $res = $DB->execute("alter table $table_name add $fieldName $fieldType($len) $unsigned $isnull $auto_increment $primary");
        return json(['code' => 20000, 'data' => $res, 'message' => 'ok']);
    }
    return json(['code' => 1, 'data' => [], 'message' => 'error']);
}

function delField()
{
    $DB                = new Db;
    $DB->database_name = $_GET['db'];
    $DB->database_user = 'root';
    $DB->database_pass = '';
    $DB->database_host = '127.0.0.1';
    $DB->connect();
    $db_name    = $_GET['db'];
    $table_name = $_GET['tb'];
    $fieldName  = $_GET['fieldName'];
    if ($table_name) {
        $res = $DB->execute("ALTER TABLE $table_name DROP $fieldName");
        return json(['code' => 20000, 'data' => $res, 'msg' => 'ok']);
    }
    return json(['code' => 1, 'data' => [], 'msg' => 'error']);
}

function addTable()
{
    $DB                = new Db;
    $DB->database_name = $_GET['db'];
    $DB->database_user = 'root';
    $DB->database_pass = '';
    $DB->database_host = '127.0.0.1';
    $DB->connect();
    $db_name    = $_GET['db'];
    $table_name = $_GET['tb'];

    $fields  = $_GET['fields'];
    $comment = $_GET['comment'];
    $comment = ($_GET['comment']) ? "comment'$comment'" : '';
    if (count($fields) < 1) {
        return json(['code' => 1, 'data' => [], 'message' => 'error']);
    }
    foreach ($fields as $key => $value) {
        $fields[$key] = json_decode($value,true);
    }
    $fields = array_reverse($fields);
    $string = '';
    // CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
    foreach ($fields as $key => $value) {
        $CHARACTER_SET_NAME = ($value['CHARACTER_SET_NAME']) ? 'CHARACTER SET ' . $value['CHARACTER_SET_NAME'] : '';
        $COLLATION_NAME     = ($value['COLLATION_NAME']) ? 'COLLATE ' . $value['COLLATION_NAME'] : '';
        $IS_NULLABLE        = ($value['IS_NULLABLE']) ? 'NOT NULL' : '';
        $EXTRA              = ($value['EXTRA']) ? 'AUTO_INCREMENT' : '';
        $COLUMN_KEY         = ($value['COLUMN_KEY']) ? 'primary key' : '';
        $COLUMN_DEFAULT     = ($value['COLUMN_DEFAULT']) ? "Default'" . $value['COLUMN_DEFAULT'] . "'" : '';
        $COLUMN_COMMENT     = ($value['COLUMN_COMMENT']) ? "comment'" . $value['COLUMN_COMMENT'] . "'" : '';
        $string .= $value['COLUMN_NAME'] . ' ' . $value['DATA_TYPE'] . ' (' . $value['CHARACTER_MAXIMUM_LENGTH'] . ') ' . $IS_NULLABLE . ' ' . $EXTRA . ' ' . $COLUMN_DEFAULT . ' ' . $COLUMN_COMMENT . ',';
    }
    $string = trim($string, ',');

    $sql = "CREATE TABLE $table_name ($string) $comment";

    if ($table_name) {
        $res  = $DB->execute($sql);
        if (!$res) {
            return json(['code' => 1, 'data' => [], 'message' => '操作失败']);
        }
        $menu = cache('menu');
        foreach ($menu as $key => $value) {
            if (isset($value['alias'])) {

                if ($value['alias'] == $db_name) {
                    $chi              = $value['children'];
                    $path             = $chi[count($chi) - 1]['path'];
                    $arr              = explode('/', $path);
                    $ol               = $arr[1] + 1;
                    $chi[count($chi)] = [
                        'path'      => 'index/' . $ol,
                        'name'      => 'db' . $ol,
                        'component' => 'db_list/index',
                        'meta'      => [
                            'title' => $table_name,
                            'icon'  => 'table',
                            'db'    => $db_name,
                        ],
                    ];
                    $menu[$key]['children'] = $chi;
                }
            }
        }
        cache('menu',$menu);
        return json(['code' => 20000, 'data' => $menu, 'message' => '操作成功']);
    }
    return json(['code' => 1, 'data' => [], 'message' => '操作失败']);
}

/************** Common Function **************/

function dump($value)
{
    echo ('<pre>');
    var_dump($value);
}

function json($result)
{
    exit(json_encode($result, JSON_UNESCAPED_UNICODE));
}

function cache($name, $data = '')
{
    if (!$data) {
        $file = file_get_contents($name . '.txt');
        $str  = json_decode($file, true);
        if ($str) {
            return $str;
        }
        return $file;
    }
    $str = '';
    if (is_array($data)) {
        $str = json_encode($data, JSON_UNESCAPED_UNICODE);
    } else {
        $str = $data;
    }
    file_put_contents($name . '.txt', $str);
}

/************** DbClass **************/

class Db
{
    public $pdo;
    private $error;

    public $database_name;
    public $database_user;
    public $database_pass;
    public $database_host;
    // function __construct() {
    //   $this->connect();
    // }
    public function prep_query($query)
    {
        return $this->pdo->prepare($query);
    }
    public function connect()
    {
        if (!$this->pdo) {
            $dsn      = 'mysql:dbname=' . $this->database_name . ';host=' . $this->database_host . ';charset=utf8';
            $user     = $this->database_user;
            $password = $this->database_pass;
            try {
                $this->pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_PERSISTENT => true));
                return true;
            } catch (PDOException $e) {
                $this->error = $e->getMessage();
                die($this->error);
                return false;
            }
        } else {
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
            return true;
        }
    }
    public function table_exists($table_name)
    {
        $stmt = $this->prep_query('SHOW TABLES LIKE ?');
        $stmt->execute(array($table_name));
        return $stmt->rowCount() > 0;
    }
    public function execute($query, $values = null)
    {
        if ($values == null) {
            $values = array();
        } else if (!is_array($values)) {
            $values = array($values);
        }
        $stmt = $this->prep_query($query);
        $stmt->execute($values);
        return $stmt;
    }
    public function fetch($query, $values = null)
    {
        if ($values == null) {
            $values = array();
        } else if (!is_array($values)) {
            $values = array($values);
        }
        $stmt = $this->execute($query, $values);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function fetchAll($query, $values = null, $key = null)
    {
        if ($values == null) {
            $values = array();
        } else if (!is_array($values)) {
            $values = array($values);
        }
        $stmt    = $this->execute($query, $values);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // Allows the user to retrieve results using a
        // column from the results as a key for the array
        if ($key != null && $results[0][$key]) {
            $keyed_results = array();
            foreach ($results as $result) {
                $keyed_results[$result[$key]] = $result;
            }
            $results = $keyed_results;
        }
        return $results;
    }
    public function lastInsertId()
    {
        return $this->pdo->lastInsertId();
    }
}
