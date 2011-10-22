<?php
	
function _social_connect_button($service, $route, $alt, $options = array())
{
	$image = isset($options['button-type'])?$options['button-type']:'default';
	$image .= '.png';
  
	return _link($route)->text(_tag('img', array('src'=>'/dmSocialPlugin/images/'.$service.'/'.$image, 'alt' => $alt)   ) );
}
