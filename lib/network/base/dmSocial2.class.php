<?php
class dmSocial2 extends dmOAuth2
{
  protected $user_factory;

  public function &getUserFactory()
  {
    if(is_null($this->user_factory))
    {
      $this->user_factory = dmSocial::getUserFactory($this);
    }

    return $this->user_factory;
  }

  public function setUserFactory($user_factory)
  {
    $this->user_factory = $user_factory;
  }

  public function getUser()
  {
    return $this->getUserFactory()->getUser();
  }

  public function connect($user, $auth_parameters = array(), $request_params = array())
  {
    $this->requestAuth($auth_parameters);
  }

  public function __sleep()
  {
    return dmSocial::sleep($this);
  }

  public function __wakeup()
  {
    return dmSocial::wakeup($this);
  }
}
