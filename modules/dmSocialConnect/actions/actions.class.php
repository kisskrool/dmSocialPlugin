<?php
/**
 * Social connect actions
 */
class dmSocialConnectActions extends myFrontModuleActions
{
    public function executeConnect(dmWebRequest $request)
    {
	  $service=$this->getRequestParameter('service');
      $this->getUser()->socialConnect($service);
    }

}
