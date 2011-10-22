<?php
/**
 * Social connect components
 * 
 * No redirection nor database manipulation ( insert, update, delete ) here
 */
class dmSocialConnectComponents extends myFrontModuleComponents
{

  public function executeSignin(dmWebRequest $request)
  {
    $this->service = 'facebook';
    $this->route = '@social_connect?service='.$this->service;
    
    $options=array();
    $options['button-type']= 'light-medium-long';
    
    $this->options=$options;
  }


}
