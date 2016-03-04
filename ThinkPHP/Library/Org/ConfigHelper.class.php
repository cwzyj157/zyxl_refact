<?php
namespace Org;
class ConfigHelper
{
    public function get_config_lists()
    {
        $result = array();

        $config = M('config');
        $condition['status'] = 1;
        $configData = $config->where($condition)->field('type,name,value')->select();

        if ($configData) {
            foreach ($configData as $value) {
                $result[$value['name']] = $this->parse($value['type'], $value['value']);
            }
        }

        return $result;
    }

    private function parse($type, $value)
    {
        if ($type == 3) {
            $temp = explode("\r\n", $value);
            foreach ($temp as $key => $value) {
                $result[] = explode(':', $value);
            }
            return $result;
        }
        return $value;
    }
}