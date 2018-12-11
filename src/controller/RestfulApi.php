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
// | @title: RestfulApi 基类
// +----------------------------------------------------------------------

namespace lsethinkapi\controller;

use think\Controller;
use think\facade\Config;
use think\facade\Request;
use think\Exception;
use lsethinkapi\Send;
use lsethinkapi\exception\UnauthorizedException;

abstract class RestfulApi
{
	use Send;
    /**
     * @var array RestfulApi实例
     */
    public static $apiApp;

	/**
     * @var \think\Request Request实例
     */
    protected $request;
	/**
     * 默认关闭验证
     * @var bool
     */
    public $apiAuth = false;
    /**
     * 当前资源类型
     * @var
     */
    protected $type;
    /**
     * 当前请求类型
     * @var
     */
    protected $method; // 当前请求类型
    /**
     * REST 操作
     * @var array
     */
    protected $restActionList = ['index', 'create', 'save', 'read', 'edit', 'update', 'delete'];
    /**
     * 附加方法
     * @var array
     */
    protected $extraActionList = [];
    /**
     * 跳过验证方法
     * @var array
     */
    protected $skipAuthActionList = [];
    /**
     * REST 允许访问的请求类型
     * @var string
     */
    protected $restMethodList = 'get|post|put|delete|head|options';
    /**
     * 默认请求方式
     * @var string
     */
    protected $restDefaultMethod = 'get';
    /**
     * REST 允许响应的资源类型
     * @var string
     */
    protected $restTypeList = 'html|xml|json|rss';
    /**
     * REST 默认响应类型
     * @var string
     */
    protected $restDefaultType = 'json';
    /**
     * REST允许输出的资源类型列表
     * @var array
     */
    protected $restOutputType = [
        'xml'   => 'application/xml',
        'jsonp' => 'application/jsonp',
        'json'  => 'application/json',

    ];

    /**
     * 初始化构造函数
     * @param Request|null $request
     */
    public function __construct(Request $request)
    {
    	$this->request = $request;
    	//初始化配置
        self::_getConfig();

        $this->initialize();

    }

    /**
	 * 控制器初始化操作
	 */
	protected function initialize()
    {
    	//请求参数
    	$request = $this->request;

		// 设置回调返回参数名称
        $this->setCodeName('code');
        $this->setMessageName('msg');
        
		//所有ajax请求的options预请求都会直接返回200，如果需要单独针对某个类中的方法，可以在路由规则中进行配置
		if($request->isOptions()){
			return $this->sendSuccess($data = [], $message = 'success');
		}

		$ext = $request->ext();
        if ($ext == '') {
            // 自动检测资源类型
            $this->type = $request->type();
        } elseif (!preg_match('/\(' . $this->restTypeList . '\)$/i', $ext)) {
            // 资源类型非法 则用默认资源类型访问
            $this->type = $this->restDefaultType;
        } else {
            $this->type = $ext;
        }
        $this->type = (in_array($this->type, array_keys($this->restOutputType))) ? $this->type : $this->restDefaultType;
        //设置响应类型
        $this->setType($this->type);

        // 请求方式检测
        $method = strtolower($request->method());
        if (stripos($this->restMethodList, $method) === false) {
            return $this->sendError($error = 405, $message = 'Method Not Allowed', $code = 405, $data = [], $headers = ["Access-Control-Allow-Origin" => $this->restMethodList], $options = []);
        }
        $this->method = $method;

    }

    /**
     * [_execAuth description]
     * @throws UnauthorizedException
     */
    protected function _execAuth()
    {
        //请求参数
        $request = $this->request;
        //是否跳过验证
        $action = $request->action();
        //是否跳过开关变量
        $isSkipAuth = false;
        //当前控制器方法是否在跳过方法列表中
        array_map(function ($item) use ($action, &$isSkipAuth) {
            $isSkipAuth = (strtolower($item) == strtolower($action)) ? true : $isSkipAuth;
        }, $this->skipAuthActionList);

        //
        if ($isSkipAuth){
            return;
        }


    }



    /**
     * 检测客户端是否有权限调用接口
     */
    public function checkAuth()
    {	
    	$baseAuth = Factory::getInstance(\app\api\controller\Oauth::class);
    	$clientInfo = $baseAuth->authenticate();
    	return $clientInfo;
    }


    /**
     * 获取配置信息
     * @param null $keys
     * @return mixed
     */
    private static function _getConfig($keys = null)
    {

        if (!self::$app['config']) self::_registerConfig();
        return ($keys == null) ? self::$app['config'] : self::$app['config'][$keys];
    }

    /**
     * 注册配置信息
     */
    private static function _registerConfig()
    {
        $api_config = [];

        $path = realpath(__DIR__ . '/../../config/api.php');
        if（is_file($path)){
            $api_config = file_get_contents($path);
        }
        print_r($api_config);
        exit;
    }


}
