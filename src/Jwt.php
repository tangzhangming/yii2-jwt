<?php
namespace tangzhangming\jwt;

use Yii;
use yii\base\BaseObject;
use yii\web\UnauthorizedHttpException;
use Firebase\JWT\JWT as FirebaseJWT;

class Jwt extends BaseObject{

    // JWT 签名的Key
    public $key;

    // JWT 签名算法
    public $algorithm = [
        'HS256'
    ];

    private $Authorization;

    public $message;

    public function init(){
        parent::init();
        // ... 应用配置后进行初始化
    }

    /**
     * 获取 jwt Token
     */
    public function getAuthorization(){
        if( Yii::$app->request->get('jwt') ){
            $this->Authorization = Yii::$app->request->get('jwt');
        }

        return $this->Authorization;
    }

    /**
     * 解析 jwt token
     */
    public function loadToken($Authorization){
        try {
            $token = FirebaseJWT::decode(
                $Authorization, 
                $this->key, 
                $this->algorithm
            );

        } catch (\InvalidArgumentException $e) {
            //jwt 未设置key
            $this->message = $e->getMessage();

        } catch (\UnexpectedValueException $e) {
            //Jwt 无效等多种原因
            $this->message = $e->getMessage();

        } catch (\Exception $e) {
            //其他原因，如jwt格式错误
            $this->message = $e->getMessage();

        }

        if( !isset($token) ){
            return null; 
        }

        return $token;
    }

    /**
     * 生成 jwt 令牌
     * $data = [
     *      "iss"     => "http://example.com",//jwt签发者
     *      "aud"     => "http://example.org",//接收jwt的一方
     *      "iat"     => time(),//jwt的签发时间
     *      "exp"     => time() + 3600,//jwt的过期时间，过期时间必须要大于签发时间
     *      "nbf"     => 1357000000,//定义在什么时间之前，某个时间点后才能访问
     *      "user_id" => 38,
     *  ]
     */
    public function make($data){
        return FirebaseJWT::encode($data, $this->key);
    }
}