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
// | @title: 向客户端发送相应基类
// +----------------------------------------------------------------------

namespace lsethinkapi\controller;

use think\Response;
use think\response\Redirect;
use think\exception\HttpResponseException;

/**
 * 向客户端发送相应基类
 * 
 */
trait Send
{

    /**
     * code字段名称
     *
     * @var string
     */
    protected $codeName = 'code';

    /**
     * msg字段名称
     *
     * @var string
     */
    protected $messageName = 'msg';

    /**
     * 默认返回资源类型
     * @var string
     */
    protected $restDefaultType = 'json';

    /**
     * 设置返回CODE字段名称
     *
     * @param string $code_name code名称
     */
    public function setCodeName($code_name = null)
    {
        $this->codeName = (string)(!empty($code_name)) ? $code_name : $this->codeName;
        return $this;
    }

    /**
     * 设置返回message字段名称
     *
     * @param string $message_name message名称
     */
    public function setMessageName($message_name = null)
    {
        $this->messageName = (string)(!empty($message_name)) ? $message_name : $this->messageName;
        return $this;
    }

    /**
     * 设置响应类型
     * @param null $type
     * @return $this
     */
    public function setType($type = null)
    {
        $this->type = (string)(!empty($type)) ? $type : $this->restDefaultType;
        return $this;
    }

    /**
     * 失败响应
     * @param int $error
     * @param string $message
     * @param int $code
     * @param array $data
     * @param array $headers
     * @param array $options
     * @return Response|\think\response\Json|\think\response\Jsonp|\think\response\Xml
     */
    public function sendError($error = 1, $message = 'error', $code = 400, $data = [], $headers = [], $options = [])
    {
        $responseData[$this->codeName] = (int)$error;
        $responseData[$this->messageName] = (string)$message;
        if (!empty($data)) $responseData['data'] = $data;
        $responseData = array_merge($responseData, $options);
        
        $response = $this->response($responseData, $code, $headers,$options);

        throw  new HttpResponseException($response);
    }

    /**
     * 成功响应
     * @param array $data
     * @param string $message
     * @param int $code
     * @param array $headers
     * @param array $options
     * @return Response|\think\response\Json|\think\response\Jsonp|Redirect|\think\response\Xml
     */
    public function sendSuccess($data = [], $message = 'success', $code = 200, $headers = [], $options = [])
    {
        $responseData[$this->codeName] = 0;
        $responseData[$this->messageName] = (string)$message;
        if (!empty($data)) $responseData['data'] = $data;
        $responseData = array_merge($responseData, $options);
        $response = $this->response($responseData, $code, $headers,$options);

        throw  new HttpResponseException($response);
    }

    /**
     * 重定向
     * @param $url
     * @param array $params
     * @param int $code
     * @param array $with
     * @return Redirect
     */
    public function sendRedirect($url, $params = [], $code = 302, $with = [])
    {
        $response = new Redirect($url);
        if (is_integer($params)) {
            $code = $params;
            $params = [];
        }
        $response->code($code)->params($params)->with($with);

        throw new HttpResponseException($response);
    }

    /**
     * 响应
     * @param $responseData
     * @param $code
     * @param $headers
     * @param $options
     * @return Response|\think\response\Json|\think\response\Jsonp|Redirect|\think\response\View|\think\response\Xml
     */
    public function response($responseData, $code, $headers,$options)
    {
        if (!isset($this->type) || empty($this->type)) $this->setType();

        return Response::create($responseData,$this->type, $code, $headers,$options);
    }
}