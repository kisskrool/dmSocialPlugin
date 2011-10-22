<?php

class BasedmSocialActions extends dmSocialBaseActions
{
  /**
   *
   * @param dmWebRequest $request
   *
   * Store access token and manage user
   *
   */
  public function executeAccess(dmWebRequest $request)
  {
    $network = $this->getNetwork();

    $network->setCallback('@social_access?service='.$network->getName());
    $access_token = $network->getAccessToken($this->getCode());

    $network->setToken($access_token);

    $user = null;

    if($this->getUser()->isAuthenticated())
    {
      $user = $this->getUser()->getGuardUser();

      $conflict = !$network->getUserFactory()->isCompatible($user);
      $event = new sfEvent($this, 'socialnetwork.filter_user', array('network' => $network, 'conflict' => $conflict));
      $dispatcher = $this->getContext()->getEventDispatcher();
      $user = $dispatcher->filter($event, $user)->getReturnValue();
    }
    else
    {
      $old_token = $this->getOrmAdapter('DmSocialToken')->findOneByNameAndIdentifier($network->getName(), $network->getIdentifier());

      //try to get user from the token
      if($old_token)
      {
        $user = $old_token->getUser();
      }

      //try to get user by network
      if(!$user)
      {
        $user = $this->getGuardAdapter()->findByNetwork($network);
      }

      $create_user = sfConfig::get('app_social_create_user', false);
      $redirect_register = sfConfig::get('app_social_redirect_register', false);

      $create_user = $network->getConfigParameter('create_user', $create_user);
      $redirect_register = $network->getConfigParameter('redirect_register', $redirect_register);

      //create a new user if needed
      if(!$user && ( $create_user || $redirect_register))
      {
        $user = $network->getUser();
        if($redirect_register)
        {
          $this->getUser()->setAttribute('social_user', serialize($user));
          $this->getUser()->setAttribute('network', serialize($network));

          $this->redirect($redirect_register);
        }
        else
        {
		  if ($user->getEmail()!='')
			$user->save();
        }
      }
    }

    if($user && $user->getEmail()!='')
    {
      $access_token->setUserId($user->getId());

      if(!$this->getUser()->isAuthenticated())
      {
        $this->getUser()->signIn($user, sfConfig::get('app_social_remember_user', true));//user signIn instead of signin
      }
    }

    $this->getUser()->addToken($access_token);

    $this->redirect($this->getCallback());
  }
}
