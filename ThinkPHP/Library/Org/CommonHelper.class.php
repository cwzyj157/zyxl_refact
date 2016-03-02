<?php
namespace Org;
class CommonHelper
{
    public function is_login()
    {
        $sessonPrefix = C('SESSION_PREFIX');
        $user = session($sessonPrefix);
        if (empty($user)) return false;

        return true;
    }

}