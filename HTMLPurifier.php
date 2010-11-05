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
            $this->config = \HTMLPurifier_Config::createDefault();
            $this->config->set('Cache.SerializerPath', $this->options['cache_dir']);
            if(isset($this->options['enable_youtube']) && $this->options['enable_youtube']) {
                $this->config->set('Filter.YouTube', true);
            }
            if(isset($this->options['allowed_html']) && $this->options['allowed_html'] !== null) {
                $this->config->set('HTML.Allowed', $this->options['allowed_html']);
            }
            if(isset($this->options['base_uri']) && $this->options['base_uri'] !== null) {
                $this->config->set('URI.Base', $this->options['base_uri']);
            }
            if(isset($this->options['absolute_uri']) && $this->options['absolute_uri'] !== null) {
                $this->config->set('URI.MakeAbsolute', $this->options['absolute_uri']);
            }
        }
        $purifier = new \HTMLPurifier($this->config);

        return $purifier->purify($data);
    }
}
