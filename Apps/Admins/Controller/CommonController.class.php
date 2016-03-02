<?php
namespace Admins\Controller;

use Org\CommonHelper;
use Think\Controller;

class CommonController extends Controller
{
    protected $is_login = false;
    protected  function _initialize()
    {
        echo '000';

        $commonHelper = new CommonHelper();
        $this->is_login = $commonHelper->is_login();
        if ($this->is_login === false) {
            $this->redirect('Login/index');
        }
    }
}
