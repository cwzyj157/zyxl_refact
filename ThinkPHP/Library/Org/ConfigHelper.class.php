<?php
namespace Org;
class ConfigHelper
{
    public function get_config_lists()
    {
        $config = M('config');
        $condition['status'] = 1;
        $config->where($condition)->field('type,name,value')->select();

        if ($config) {
            foreach ($config as $value) {
                $config[$value['name']] = $this->parse($value['type'], $value['value']);
            }
        }
        return $config;
    }

    private function parse($type, $value)
    {
        $result = array();
        if ($type == 3) {
            $temp = explode('\r\n', $value);
            foreach ($temp as $key => $value) {
                $result[] = explode(':', $value);
            }
        }
        return $result;
    }
}