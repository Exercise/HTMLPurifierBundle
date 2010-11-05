<?php

namespace Bundle\ExerciseCom\HTMLPurifierBundle;

class HTMLPurifier
{
    protected $purifier;
    protected $config;

    public function __construct(array $options = array())
    {
        $this->options = $options;
    }

    public function purify($data)
    {
        if (strpos($data, '<') === false) {
            return $data;
        }

        if(!$this->config) {
            $config = \HTMLPurifier_Config::createDefault();
            $config->set('Cache.SerializerPath', $this->options['cache_dir']);
//                $config->set('Filter.YouTube', true);
        }
        $purifier = new \HTMLPurifier($config);

        return $purifier->purify($data);
    }
}
