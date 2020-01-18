# 一个使YII2框架支持jwt的组件
基于[firebase/php-jwt](https://github.com/firebase/php-jwt "firebase/php-jwt") 的封装
仅需一处配置，两行代码，30秒即可让你的YII支持jwt

###安装
    composer install tangzhangming/yii2-jwt

###配置
    <?php 
    /**
     * comfig/web.php
     * or
     * 高级版的 config/main.php
     */
    
    return [
        'components' => [
            'jwt' => [
                'class' => \tangzhangming\jwt\Jwt::class,
                'key' => '00193c3e105beaaf15459df6300c5a86',
            ],
            ...
        ]
        ...
    ];

###生成token
    	/**
		 * 某个控制器的某个action
		 * make传入的内容部分字段是必须的，如iat，请自行了解jwt
		 * 除了这些必须的字段，也可以传入自定义字段，但尽量不要放过于敏感的内容
		*/
    	public function actionCreateToken(){
            $Authorization = Yii::$app->jwt->make([
                "iss"  => "http://example.com",//jwt签发者
                "aud"  => "http://example.org",//接收jwt的一方
                "iat"  => time(),//jwt的签发时间
                "exp"  => time() + 3600,//jwt的过期时间，过期时间必须要大于签发时间
                "nbf"  => 1357000000,//定义在什么时间之前，某个时间点后才能访问
                "user_id" => 45,
            ]);
            return $this->asJson([
                'Authorization' => (string) $Authorization,
            ]);
        }

### User 组件实现通过jwt来认证用户
    <?php
    namespace app\models;
    
    use yii\db\ActiveRecord;
    
    class User extends ActiveRecord implements \yii\web\IdentityInterface
    {
    	...
        public static function findIdentityByAccessToken($token, $type = null){
            /**
             * 此处的token是Yii::$app->jwt->make 时传入的所有数据 
             * 传入uid则是uid，user_id则是user_id
             */
            if( isset($token->user_id) ){
                return static::findOne($token->user_id);
            }
            return null;
        }

###控制器基于jwt认证用户
`    <?php

    class TestController extends Controller{
    
        public function behaviors(){
            $behaviors = parent::behaviors();
            $behaviors['authenticator'] = [
                'class' => \tangzhangming\jwt\JwtAuth::class,
                'optional' => [ //不需要登陆的白名单
                    'index'
                ],
            ];
            return $behaviors;
        }
    
        public function actionIndex(){
            //由于加入了optional，不需要jwt也能访问这里，但是如果jwt过期或错误等情况，请求将被拦截而无法访问到此处
        }
    
        public function actionView(){
            //jwt 必须存在且正确才能访问到这里
        }`
