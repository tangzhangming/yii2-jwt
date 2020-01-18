<?php
namespace tangzhangming\jwt;

use Yii;
use yii\di\Instance;
use yii\di\Container;
use yii\web\UnauthorizedHttpException;

use Firebase\JWT\JWT;


class JwtAuth extends \yii\filters\auth\AuthMethod{

    public $jwt;

    public function init(){
        parent::init();

        $this->jwt = Yii::$app->jwt;
    }

    public function authenticate($user, $request, $response){
        //获取客户端提交的 jsonWebToken
        $Authorization = $this->jwt->getAuthorization();

        //获得解析过后的token内容
        $token = $this->jwt->loadToken($Authorization);

        //调用 User 组件，验证用户
        if( $token && $identity = $user->loginByAccessToken($token, 'jwt')){
            return $identity;
        }

        return null;
    }

    /**
     * 如果处理失败
     */
    public function handleFailure($response){
        if( !$this->jwt->getAuthorization() ){
            throw new UnauthorizedHttpException('非法访问, 未系统没有检查到 jwt 凭证');
        }
             
        if( YII_DEBUG ){
            throw new UnauthorizedHttpException('JWT 未通过验证:'.$this->jwt->message);
        }else{
            throw new UnauthorizedHttpException('您的 JWT 无效');
        }
    }
}