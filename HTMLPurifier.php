<?php

namespace Bundle\ExerciseCom\HTMLPurifier;

class HTMLPurifier
{
    protected $purifier;
    protected $config;

    protected $options = array(
        "cache_path" => $this->container->get('kernel.root_dir') . '/cache/htmlpurifier'
    );

    public function __construct(array $options = array())
    {
        $this->options = array_merge($this->options, $options);


    }

    public function purify($data)
    {
        if (strpos($data, '<') === false) {
            return $data;
        }

        if(!$this->config) {
            $config = \HTMLPurifier_Config::createDefault();
            $config->set('Cache.SerializerPath', $this->options['cache_path']);
//                $config->set('Filter.YouTube', true);
        }
        $purifier = new \HTMLPurifier($config);

        return $purifier->purify($data);
    }
}
