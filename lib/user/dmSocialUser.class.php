<?php
/**
 *
 *
 *
 * Allow to easily connect a user
 *
 */
class dmSocialUser extends dmCoreUser
{
  protected $tokens;

  /**
   *
   * @param string $service
   * @param boolean $force
   *
   * connect to a service. if already connected redirect to the callback
   *
   */
  public function socialConnect($service, $config= array(), $force = false)//new method, I changed its
  //original name connect() to socialConnect(), as it enters in conflict with dmCoreUser connect() method
  {
    $network = dmSocial::getInstance($service, $config);

    if(!$this->isConnected($service) || $force)
    {
      $this->removeTokens($service);

      $network->setCallback('@social_access?service='.$service);
      $network->connect($this);
    }
    else
    {
      $network->getController()->redirect($network->getCallback());
    }
  }

  public function signIn(DmUser $user, $remember = false, $con = null)//override dmSecurityUser
  {
    parent::signIn($user, $remember, $con);

    $this->refreshTokens();
  }


  public function signOut()//override dmSecurityUser
  {
    $this->getAttributeHolder()->removeNamespace('SocialNetwork');
    parent::signOut();
  }

  /**
   *
   * @param string $service
   *
   * check if the user is connected to the service
   *
   * @return boolean
   *
   */
  public function isConnected($service)//new method
  {
    $token = $this->getToken($service);

    return !is_null($token) && $token->isValidToken();
  }

  /**
   *
   *
   *
   * Allow to retrieve name of services which is connected with the user
   *
   */
  public function getConnectedServices()//new method
  {
    $connected_services = array();

    foreach(dmSocial::getAllServices() as $service)
    {
      if($this->isConnected($service))
      {
        $connected_services[] = $service;
      }
    }

    return $connected_services;
  }

  public function getUnconnectedServices()//new method
  {
    $unconnected_services = array();
    $connected_services = $this->getConnectedServices();

    foreach(dmSocial::getAllServices() as $service)
    {
      if(!in_array($service, $connected_services))
      {
        $unconnected_services[] = $service;
      }
    }

    return $unconnected_services;
  }

  /**
   *
   * @param DmSocialToken $token
   *
   * Add a token to the user
   *
   */
  public function addToken(DmSocialToken $token)//new method
  {
    $service = $token->getName();
    $status = $token->getStatus();

    $tokens = $this->getTokens();

    if(isset($tokens[$status][$service]))
    {
      $this->removeTokens($service, $status);
    }

    if($status == DmSocialToken::STATUS_REQUEST || is_null($token->getUserId()))
    {
      $this->setAttribute($service.'_'.$status.'_token', serialize($token), 'SocialNetwork');
    }
    else
    {
      $token->save();
    }

    $this->tokens[$status][$service] = $token;
  }

  /**
   *
   * @param string $service
   * @param string $status
   * @param boolean $remove_in_session
   *
   * get a token from a service
   *
   */
  public function getToken($service, $status = DmSocialToken::STATUS_ACCESS)//new method
  {
    $token = null;
    $tokens = $this->getTokens();
    if(count($tokens) > 0)
    {
      if(!is_null($status))
      {
        $token = isset($tokens[$status][$service])?$tokens[$status][$service]:null;
      }
      else
      {
        foreach(DmSocialToken::getAllStatuses() as $status)
        {
          if(isset($tokens[$status][$service]))
          {
            $token = $tokens[$status][$service];
            break;
          }
        }
      }
    }

    return $token;
  }

  /**
   *
   * @param string $service
   * @param string $status
   * @param boolean $remove
   *
   * get token from the session
   *
   */
  protected function getSessionTokens($service = null)//new method
  {
    if(is_null($service))
    {
      $services = dmSocial::getAllServices();
    }
    else
    {
      $services = array($service);
    }

    $tokens = array();

    foreach($services as $service)
    {
      foreach(DmSocialToken::getAllStatuses() as $status)
      {
        $token = $this->getAttribute($service.'_'.$status.'_token', null, 'SocialNetwork');
        if($token)
        {
          $tokens[$status][$service] = unserialize($token);
        }
      }
    }

    return $tokens;
  }

  protected function getDbTokens()//new method
  {
    $tokens = array();

    if($this->isAuthenticated())
    {
      $db_tokens = $this->getOrmAdapter('DmSocialToken')->findByUserId($this->getGuardUser()->getId());

      foreach($db_tokens as $token)
      {
        $tokens[$token->getStatus()][$token->getName()] = $token;
      }
    }

    return $tokens;
  }

  /**
   *
   *
   *
   * getTokens store in the database
   *
   * TODO in the session too
   *
   */
  public function getTokens()//new method
  {
    if(is_null($this->tokens))
    {
      $this->tokens = array_merge($this->getSessionTokens(), $this->getDbTokens());
    }

    return $this->tokens;
  }

  /**
   *
   *
   *
   * set null tokens to get tokens again
   *
   */
  public function refreshTokens()//new method
  {
    $this->tokens = null;
  }

  /**
   *
   * @param string $service
   * @param string $status
   *
   * removeTokens from database or session
   *
   */
  public function removeTokens($service, $status = null)//new method
  {
    if(is_null($status))
    {
      if($this->isAuthenticated())
      {
        $this->getOrmAdapter('DmSocialToken')->deleteTokens($service, $this->getGuardUser(), $status);
      }

      $this->getAttributeHolder()->removeNamespace('SocialNetwork');
    }
    else
    {
      if($this->hasAttribute($service.'_'.$status.'_token', 'SocialNetwork'))
      {
        $this->getAttributeHolder()->remove($service.'_'.$status.'_token', 'SocialNetwork');
      }

      if($this->isAuthenticated())
      {
        $this->getOrmAdapter('DmSocialToken')->deleteTokens($service, $this->getGuardUser(), $status);
      }
    }
  }


  /**
   *
   * @param string $service
   * @param array $config
   * @param $in_session
   *
   * get a network
   *
   */
  public function getNetwork($service, $config = array())//new method
  {
    $token = $this->getToken($service);
    $config = array_merge(array('token' => $token), $config);

    return dmSocial::getInstance($service, $config);
  }

  protected function getOrmAdapter($model)//new method
  {
    return dmSocialOrmAdapter::getInstance($model);
  }
}
