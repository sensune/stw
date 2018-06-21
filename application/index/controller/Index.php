<?php
namespace app\index\controller;

use think\Controller;
use think\Db;

class Index extends base
{
    public function index()
    {
        return $this->fetch();
    }

    public function index_mo()
    {
        return $this->fetch();
    }

    public function chanlang()
    {
        $lang = input('lang');

        switch($lang)
        {
            case 'en':
                cookie('think_var','en-us');
                break;
            case 'zh':
                cookie('think_var','zh-cn');
                break;
            default:
                break;
        }

        echo json_encode(array('status'=>1));
    }
}
