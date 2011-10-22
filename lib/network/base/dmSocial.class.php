<?php
/**
 * Tool Class to create networks and do other stuffs
 *
 */
class dmSocial
{
  /**
   *
   * @param string $name
   * @param array $config
   *
   * create a network using the name in the app.yml file
   *
   */
  public static function getInstance($name, $config = array())
  {
    $default = sfConfig::get('app_social_'.$name, array());

    $config = array_merge($default, $config);

    $provider = strtolower(isset($config['provider'])?$config['provider']:$name);
    $class = 'dm'.sfInflector::camelize($provider.'_network');

    $key = isset($config['key'])?$config['key']:null;
    $secret = isset($config['secret'])?$config['secret']:null;
    $token = isset($config['token'])?$config['token']:null;
    $config['name'] = isset($config['name'])?$config['name']:$name;

    $network = new $class($key, $secret, $token, $config);

    return $network;
  }

  public static function getAllServices()
  {
    $services = array();
    foreach(sfConfig::getAll() as $key => $value)
    {
      $params = explode('_', $key);
      if(in_array('network', $params) && is_array($value) && isset($value['key']) && isset($value['secret']))
      {
        $services[] = substr($key, 11);
      }
    }

    return $services;
  }

  public static function getUserFactory($network)
  {
    $config = $network->getConfig();
    $user_config = isset($config['user'])?$config['user']:array();

    return new dmSocialUserFactory($network, $user_config);
  }

  public static function sleep(&$network)
  {
    $network->getUserFactory()->setService(null);

    $reflection = new ReflectionObject($network);

    $fields = array();
    $ignored_properties = array('controller', 'context', 'logger');

    foreach($reflection->getProperties() as $property)
    {
      if(!in_array($property->getName(), $ignored_properties))
      {
        $fields[] = $property->getName();
      }
    }

    return $fields;
  }

  public static function wakeup(&$network)
  {
    $network->getUserFactory()->setService($network);
  }
}
