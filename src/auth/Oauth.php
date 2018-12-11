<?php
// +----------------------------------------------------------------------
// | Work is a pleasure, Life is a joy!
// 
//                            _ooOoo_  
//                           o8888888o  
//                           88" . "88  
//                           (| -_- |)  
//                            O\ = /O  
//                        ____/`---'\____  
//                      .   ' \\| |// `.  
//                       / \\||| : |||// \  
//                     / _||||| -:- |||||- \  
//                       | | \\\ - /// | |  
//                     | \_| ''\---/'' | |  
//                      \ .-\__ `-` ___/-. /  
//                   ___`. .' /--.--\ `. . __  
//                ."" '< `.___\_<|>_/___.' >'"".  
//               | | : `- \`.;`\ _ /`;.`/ - ` : | |  
//                 \ \ `-. \_ __\ /__ _/ .-` / /  
//         ======`-.____`-.___\_____/___.-`____.-'======  
//                            `=---='  
//  
//         .............................................  
//                  佛祖保佑             永无BUG
// +----------------------------------------------------------------------
// | @author: lishaoen | @email:<lishaoenbh@qq.com>  | @time:2018/12
// +----------------------------------------------------------------------
// | @title: 授权Oauth验证
// +----------------------------------------------------------------------

namespace lsethinkapi\auth;

use think\facade\Request;
use think\Db;
use think\facade\Cache;
use think\Exception;
use lsethinkapi\Send;
use lsethinkapi\exception\UnauthorizedException;

class Oauth
{
    use Send;
    
    /**
     * accessToken存储前缀
     *
     * @var string
     */
    public static $accessTokenPrefix = 'accessToken_';

    /**
     * accessTokenAndClientPrefix存储前缀
     *
     * @var string
     */
    public static $accessTokenAndClientPrefix = 'accessTokenAndClient_';

    /**
     * 过期时间秒数
     *
     * @var int
     */
    public static $expires = 72000;

    /**
     * 客户端信息
     *
     * @var
     */
    public $clientInfo;
    /**
     * 认证授权 通过用户信息和路由
     * @param Request $request
     * @return \Exception|UnauthorizedException|mixed|Exception
     * @throws UnauthorizedException
     */
    final function authenticate()
    {      

        $request = Request::instance();

        try {
            //验证授权
            $clientInfo = $this->getClient();
            $checkclient = $this->certification($clientInfo);
            if($checkclient){
                return $clientInfo;
            }
        } catch (Exception $e) {
            return $this->returnmsg(402,'Invalid1 authentication credentials.');
        }
    }

    /**
     * 获取用户信息
     * @param Request $request
     * @return $this
     * @throws UnauthorizedException
     */
    public function getClient()
    {   
        $request = Request::instance();
        //获取头部信息
        try {
            //========关键信息在头部传入例如key，用户信息，token等，这里不用这种方式请求验证==============
            // $authorization = $request->header('authorization');
            // $authorization = explode(" ", base64_decode($authorization));
            // $authorization = explode(':', $authorization[1]);
            // $app_key = $authorization[0];
            // $access_token = $authorization[1];
            // $user_id = $authorization[2];//$_SERVER['PHP_AUTH_USER']
            // $clientInfo['user_id'] = $user_id;
            // $clientInfo['app_key'] = $app_key;
            // $clientInfo['access_token'] = $access_token;
            $clientInfo = $request->param();
        } catch (Exception $e) {
            return $this->returnmsg(402,$e.'Invalid authentication credentials');
        }
        return $clientInfo;
    }

    /**
     * 获取用户信息后 验证权限
     * @return mixed
     */
    public function certification($data = []){
        //======下面注释部分是数据库验证access_token是否有效，示例为缓存中验证======
        // $time = date("Y-m-d H:i:s",time());
        // $checkclient = Db::name('tb_token')->field('end_time')->where('user_id',$data['user_id'])->where('app_key',$data['app_key'])->where('app_token',$data['access_token'])->find();
        // if(empty($checkclient)){
        //     return $this->returnmsg(402,'App_token does not match app_key');
        // }
        // if($checkclient <= $time){
        //     return $this->returnmsg(402,'Access_token expired');
        // }
        // return true;

        $getCacheAccessToken = Cache::get(self::$accessTokenPrefix . $data['access_token']);  //获取缓存access_token
        if(!$getCacheAccessToken){
            return $this->returnmsg(402,'Access_token expired or error！');
        }
        if($getCacheAccessToken['client']['app_key'] != $data['app_key']){

            return $this->returnmsg(402,'App_token does not match app_key');  //app_key与缓存中的appkey不匹配
        }

        return true;
    }

    /**
     * 生成签名
     * _字符开头的变量不参与签名
     */
    public function makeSign ($data = [],$app_secret = '')
    {   
        unset($data['version']);
        unset($data['signature']);
        foreach ($data as $k => $v) {
            
            if(substr($data[$k],0,1) == '_'){

                unset($data[$k]);
            }
        }
        dump($data);
        return $this->_getOrderMd5($data,$app_secret);
    }

    /**
     * 计算ORDER的MD5签名
     */
    private function _getOrderMd5($params = [] , $app_secret = '') {
        ksort($params);
        $params['key'] = $app_secret;
        return strtolower(md5(urldecode(http_build_query($params))));
    }

}