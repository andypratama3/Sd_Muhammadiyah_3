<?php

namespace Linguise\Script\Core;

defined('LINGUISE_SCRIPT_TRANSLATION') or die();

class Boundary
{
    /**
     * Generated boundary
     *
     * @var string|null
     */
    protected $boundary = null;

    /**
     * Array of boundaries to store
     * @var array
     */
    protected $fields = [];

    /**
     * Array of files to store
     * @var array
     */
    protected $files = [];

    /**
     * Post field content
     *
     * @var string
     */
    protected $content = '';


    public function __construct()
    {
        $this->boundary = '------'.substr(str_shuffle(str_repeat($c='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil(15/strlen($c)) )),1, 10);
    }

    /**
     * Return generated boundary
     *
     * @return string|null
     */
    public function getBoundary()
    {
        return $this->boundary;
    }

    /**
     * Add a field to the fields array
     *
     * @param $name
     * @param $value
     * @return void
     */
    public function addPostFields($name, $value)
    {
        // Check if value is array
        if (is_array($value)) {
            foreach ($value as $key => $val) {
                $this->addPostFields($name.'['.$key.']', $val);
            }
        } else {
            $this->fields[$name] = $value;
        }
    }

    /**
     * Add a field to the fields array
     *
     * @param $file_path
     * @param $file_name
     * @param $file_type
     * @return void
     */
    public function addPostFile($name, $file_path, $file_name = null, $file_type = null)
    {
        $this->files[$name] = [
            'path' => $file_path,
            'name' => $file_name,
            'type' => $file_type,
        ];
    }

    /**
     * Get a field from the fields array
     */
    public function getPostField($name)
    {
        return $this->fields[$name];
    }

    /**
     * Return the end of a boundary
     * @return string
     */
    protected function endPostFields()
    {
        return '--'.$this->boundary.'--';
    }

    /**
     * Retrieve the Content-Disposition header
     *
     * @return string
     */
    public function getContent()
    {
        $content = '';
        foreach ($this->fields as $name => $value) {
            $content .= '--'.$this->boundary."\r\n";
            $content .= "Content-Disposition: form-data; name=\"" . $name . "\"\r\n\r\n" . $value . "\r\n";
        }
        foreach ($this->files as $name => $file) {
            $real_path = realpath($file['path']);
            $post_filename = $file['name'] ?? basename($real_path);
            $post_content_type = $file['type'] ?? mime_content_type($real_path);

            $content .= '--'.$this->boundary."\r\n";
            $content .= "Content-Disposition: form-data; name=\"" . $name . "\"; filename=\"" . $post_filename . "\"\r\n";
            if (!empty($post_content_type)) {
                $content .= "Content-Type: ".$post_content_type."\r\n";
            }
            $content .= "\r\n";
            $content .= file_get_contents($real_path)."\r\n";
        }
        $content .= $this->endPostFields();
        return $content;
    }
}