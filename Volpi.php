<?php

define('TPL_ROOT', ROOT.'/templates');

class Volpi implements ArrayAccess
{
    private $template_vars = array();
    private $content;
    
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->template_vars[] = $value;
        } else {
            $this->template_vars[$offset] = $value;
        }
    }

    public function offsetExists($offset)
    {
        return isset($this->template_vars[$offset]);
    }

    public function offsetUnset($offset)
    {
        unset($this->template_vars[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->template_vars[$offset]) ? $this->template_vars[$offset] : null;
    }
    
    public static function getPath($path)
    {   
        $tpl_path = str_replace('.', DS, $path);
        $extention = '.tpl';
        return TPL_ROOT.DS.$tpl_path.$extention;
    }
     
    private function insertSections()
    {
        $pattern = '~{{\s*include\(\'(.+?)\'\)\s*}}~';
        function getSection($matches)
        {
            $path = array_pop($matches);
            $file_path =  Volpi::getPath($path);
            return file_get_contents($file_path);
        }
        $this->content = preg_replace_callback($pattern, "getSection", $this->content);
    }
    
    private function insertVars()
    {
        foreach ($this->template_vars as $key => $variable)
        {
            if(!is_array($variable))
            {
                $pattern = '/{{\s*'.$key.'\s*}}/';
                $replacement = "<?php echo \"{$variable}\"; ?>";
                $this->content = preg_replace($pattern, $replacement, $this->content);
            }
        }
    }
    
    private function insertCycles()
    {   
        $patterns = array(
            '/{{\s*foreach\s*(.+?)\s*as\s*(.+?)\s*}}/' =>
            '<?php foreach( $this->template_vars[\'$1\'] as $$2): ?>',
            
            '/{{\s*endforeach\s*}}/' =>
            '<?php endforeach; ?>',
        
            '/{{\s*(.+?)\s*}}/' =>
            '<?php echo $$1; ?>',
        );
        foreach($patterns as $pattern => $replacement)
        {
            $this->content = preg_replace($pattern, $replacement, $this->content);
        }
    }
    
    private function compile($template)
    {
        $this->content = file_get_contents($template);
        $this->insertSections();
        $this->insertVars();
        $this->insertCycles();
    }
    
    public function show($template)
    {
        $tpl_path = self::getPath($template);
        $this->compile($tpl_path);
        eval('?>'.$this->content);
    }
}