<?php
class dmSocialOrmAdapter
{
  const DOCTRINE = 'doctrine';
  const PROPEL = 'propel';

  protected $model;
  protected $orm;

  public function __construct($model)
  {
    $this->setModel($model);

    if(class_exists('Doctrine'))
    {
      $this->orm = self::DOCTRINE;
    }
    else
    {
      $this->orm = self::PROPEL;
    }
  }

  public function setModel($model)
  {
    $this->model = $model;
  }

  public function getModel()
  {
    return $this->model;
  }

  public function getOrm()
  {
    return $this->orm;
  }

  public static function getInstance($model)
  {
    if(class_exists('Doctrine'))
    {
      return new dmSocialDoctrineOrmAdapter($model);
    }
    else
    {
      return new dmSocialPropelOrmAdapter($model);
    }
  }

  public function __call($method, $arguments)
  {
    $callable = array($this->getAction(), $method);

    if(is_callable($callable))
    {
      return call_user_func_array($callable, $arguments);
    }
  }

  protected function getAction()
  {
    switch($this->getOrm())
    {
      case self::DOCTRINE:
        return Doctrine::getTable($this->getModel());
      case self::PROPEL:
        return $model.'Peer';
    }
  }

  protected function checkModels($models, $method)//verify that the model of the class is included in $models parameter
  {
    if( (is_array($models) && !in_array($this->getModel(), $models)) || $models != $this->getModel() )
    {
      throw new dmException(sprintf('"%s" doesn\'t have "%s" method', $this->getModel(), $method));
    }
    else return true;
  }
}
