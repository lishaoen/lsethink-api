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
// | @title: 授权失败异常处理
// +----------------------------------------------------------------------

namespace lsethinkapi\exception;

use think\Exception;

/**
 * 授权失败
 */
class UnauthorizedException extends Exception
{

    public $authenticate;


    public function __construct($challenge = 'Basic', $message = 'authentication Failed')
    {
        $this->authenticate = $challenge;
        $this->message      = $message;
    }

    /**
     * 获取验证错误信息
     * @access public
     * @return array|string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * WWW-Authenticate challenge string
     * @return array
     */
    public function getHeaders()
    {
        return array('WWW-Authenticate' => $this->authenticate);
    }

}