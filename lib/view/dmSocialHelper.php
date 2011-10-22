<?php

class dmSocialHelper extends dmHelper
{
	public function _social_connect_button($service, $route, $alt, $options = array())
	{
		$image = isset($options['button-type'])?$options['button-type']:'default';
		$image .= '.png';
  
		return $this->link($route)->text($this->tag('img', array('src'=>'/dmSocialPlugin/images/'.$service.'/'.$image, 'alt' => $alt) ));
	}
}
