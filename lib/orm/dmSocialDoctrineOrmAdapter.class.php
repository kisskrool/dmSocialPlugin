<?php
/**
 * Allow to override propel operation for network specific usage
 *
 */
class dmSocialDoctrineOrmAdapter extends dmSocialOrmAdapter
{
  public function findByNetwork($network)
  {
    $this->checkModels('DmUser', 'findByNetwork');

    $q = Doctrine::getTable('DmUser')
         ->createQuery('u')
         ->limit(1);

    $user_factory = $network->getUserFactory();
    $config = $user_factory->getConfig();
    $user = $user_factory->getUser();
    $keys = $user_factory->getKeys();

    foreach($keys as $key)
    {
      $method = 'get'.sfInflector::classify($key);
      if(is_callable(array($user, $method)))
      {
        $q->addWhere('u.'.$key.' = ?', $user->$method());
      }
    }

    return $q->fetchOne();
  }
}
