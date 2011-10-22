<?php

if(!$sf_user->isAuthenticated()){
	echo _social_connect_button($service, $route, __('Connect with').' '.$service, $options);
}
