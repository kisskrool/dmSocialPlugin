<?php
/**
 * Allow to override propel operation for network specific usage
 *
 */
class dmSocialPropelOrmAdapter extends dmSocialOrmAdapter
{
  public function findByNetwork($network)
  {
    $this->checkModels('DmUser', 'findByNetwork');

    $c = new Criteria();

    $user_factory = $network->getUserFactory();
    $config = $user_factory->getConfig();
    $user = $user_factory->getUser();
    $keys = $user_factory->getKeys();

    foreach($keys as $key)
    {
      $constant_key = strtoupper($key);
      $method = 'get'.sfInflector::classify($key);

      $reflection = new ReflectionClass('DmUserPeer');

      if($reflection->hasConstant($constant_key) && is_callable(array($user, $method)))
      {
        $constant = $reflection->getConstants($constant_key);
        $c->add($constant, $user->$method());
      }
      else
      {
        throw new sfException(sprintf('DmUser doesn\'t have field "%s"', $key));
      }
    }

    return DmUserPeer::doSelectOne($c);
  }
}
