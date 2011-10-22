<?php
/**
 *
 *
 *
 * Set of methods to use if need to override the default module
 *
 */
class dmSocialBaseActions extends myFrontModuleActions
{
  /**
   * callback is the original callback
   * from the current network
   *
   * @var string
   */
  protected $callback;

  /**
   * @var dmSocial(1|2)
   */
  protected $network = -1;

  /**
   *
   * @param string $callback
   *
   * setter callback
   *
   */
  protected function setCallback($callback)
  {
    $this->callback = $callback;
  }

  /**
   * getter callback
   * Allow to retrieve the original callback from the current network
   *
   */
  protected function getCallback()
  {
    if(is_null($this->callback))
    {
      $this->callback = $this->getNetwork()->getCallback();
    }

    return $this->callback;
  }

  /**
   *
   * @param dmSocial(1|2) $network
   *
   * setter network
   *
   */
  protected function setNetwork($network)
  {
    $this->network = $network;
  }

  /**
   *
   * @param string $service
   *
   * getter network
   * Allow to retrieve the current network by the default parameter service
   * or by a targeted service
   *
   */
  protected function getNetwork($service = null)
  {
    if((is_numeric($this->network) && $this->network == -1) || !is_null($service))
    {
      if(is_null($service))
      {
        $service = $this->getRequestParameter('service');
      }

      if(!is_null($service))
      {
        $request_token = $this->getUser()->getToken($service, DmSocialToken::STATUS_REQUEST);

        $this->network = dmSocial::getInstance($service, array('token' => $request_token));
        $this->setCallback($this->network->getCallback());
      }
      else
      {
        $this->network = null;
      }
    }

    return $this->network;
  }

  protected function getOrmAdapter($model)
  {
    return dmSocialOrmAdapter::getInstance($model);
  }

  protected function getGuardAdapter()
  {
    return $this->getOrmAdapter('DmUser');
  }

  /**
   *
   * @param dmSocial(1|2) $network
   *
   * get Access code according OAuth version
   *
   */
  protected function getCode()
  {
    if($this->getNetwork()->getVersion() == 1)
    {
      $code = $this->getRequestParameter('oauth_verifier');
    }
    else
    {
      $code = $this->getRequestParameter('code');
    }

    return $code;
  }
}
