<?php
namespace YesWiki;

class Security extends \YesWiki\Wiki
{ 
	function FormOpen($method = '', $tag = '', $formMethod = 'post', $class='') {
		if ($method=='edit') {
			$result  = '<form id="ACEditor" name="ACEditor" enctype="multipart/form-data" action="'.$this->href($method, $tag).'" method="'.$formMethod.'"';
			$result .= !empty($class) ? ' class="'.$class.'"' : '';
			$result .= ">\n";
            if (isset($this->config['password_for_editing']) and !empty($this->config['password_for_editing'])
                and isset($_POST['password_for_editing'])) {
                $result .= '<input type="hidden" name="password_for_editing" value="'.$_POST['password_for_editing'].'" />'."\n";
            }
		} else {
			$result = '<form action="'.$this->href($method, $tag).'" method="'.$formMethod.'"';
			$result .= !empty($class) ? ' class="'.$class.'"' : '';
			$result .= ">\n";
		}

		if (!$this->config["rewrite_mode"]) {
            $result .= '<input type="hidden" name="wiki" value="'.$this->MiniHref($method, $tag).'" />'."\n";
        }
		return $result;
	}
}
