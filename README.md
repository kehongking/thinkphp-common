# thinkphp公共扩展包
### 1.执行命令安装扩展包
```
composer require kehongking/thinkphp-common
```
### 2.app\middleware.php 中定义请求日志类
##### \KeHongKing\ThinkphpCommon\RequestLog::class,

### 3.app\middleware.php 中定义跨域类
##### \KeHongKing\ThinkphpCommon\CrossDomain::class,

### 4.app\provider.php 中定义异常处理类
##### 'think\exception\Handle' => '\\KeHongKing\\ThinkphpCommon\\HandleException',

### 5.config\middleware.php 中定义中间件
```
    // 别名或分组
    'alias'    => [
        'jwt' => \KeHongKing\ThinkphpCommon\Jwt::class,
        'auth' => \KeHongKing\ThinkphpCommon\Auth::class,
    ],
```
### 6.jwt使用
```
use KeHongKing\ThinkphpCommon\JwtCommon;
    $data = [
        'id' => 1,                       //登录账号唯一标识
        'source' => 'admin',             //登录账户来源
        'is_verify_account' => 0,        //每次验证token时,是否需要验证账号状态 1是 0否
        'table' => 'admin_user',         //is_verify_account传1时,此值传验证码数据的表名
        'condition' => [['id', '=', 1]], //is_verify_account传1时,此值传验证条件二维数组
    ];
$jwt = JwtCommon::instance();
$token =  $jwt->generateToken($data);
```

