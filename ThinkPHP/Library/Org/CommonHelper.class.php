<?php
namespace Org;
class CommonHelper
{
    /**
     * 检测是否登录
     * @return boolean true-登录，false-未登录
     */
    public function is_login()
    {
        $sessonPrefix = C('SESSION_PREFIX');
        $user = session($sessonPrefix);
        if (empty($user)) return false;

        return true;
    }

    /**
     * 检测当前用户是否为管理员
     * @return boolean true-管理员，false-非管理员
     */
    function is_administrator()
    {
        $loginUserInfo = session(C('SESSION_PREFIX'));
        if ($loginUserInfo) {
            return $loginUserInfo["admin_id"] == C('USER_ADMINISTRATOR');
        }
        return false;
    }


}