<?php
class dmGoogleNetwork extends dmSocial2
{
  protected static $apis = array(
                                  'analytics' => 'http://www.google.com/analytics/feeds/',
                                  'google_base' => 'http://www.google.com/base/feeds/',
                                  'book' => 'http://www.google.com/books/feeds/',
                                  'blogger' => 'http://www.blogger.com/feeds/',
                                  'calendar' => 'http://www.google.com/calendar/feeds/',
                                  'contacts' => 'http://www.google.com/m8/feeds/',
                                  'chrome' => 'http://www.googleapis.com/auth/chromewebstore.readonly',
                                  'documents' => 'http://docs.google.com/feeds/',
                                  'finance' => 'http://finance.google.com/finance/feeds/',
                                  'gmail' => 'http://mail.google.com/mail/feed/atom',
                                  'health' => 'http://www.google.com/health/feeds/',
                                  'h9' => 'http://www.google.com/h9/feeds/',
                                  'maps' => 'http://maps.google.com/maps/feeds/',
                                  'moderator' => 'tag:google.com,2010:auth/moderator',
                                  'open_social' => 'http://www-opensocial.googleusercontent.com/api/people/',
                                  'orkut' => 'http://www.orkut.com/social/rest',
                                  'picasa' => 'http://picasaweb.google.com/data/',
                                  'plus' => 'https://www.googleapis.com/plus/v1',
                                  'sidewiki' => 'http://www.google.com/sidewiki/feeds/',
                                  'sites' => 'http://sites.google.com/feeds/',
                                  'spreadsheets' => 'http://spreadsheets.google.com/feeds/',
                                  'userinfo' => 'https://www.googleapis.com/userinfo',
                                  'oauth2' => 'https://www.googleapis.com/oauth2/v1',
                                  'wave' => 'http://wave.googleusercontent.com/api/rpc',
                                  'webmaster_tools' => 'http://www.google.com/webmasters/tools/feeds/',
                                  'youtube' => 'http://gdata.youtube.com'
                                 );

  protected function initialize($config)
  {
    $this->setRequestAuthUrl('https://accounts.google.com/o/oauth2/auth');
    $this->setAccessTokenUrl('https://accounts.google.com/o/oauth2/token');

    $this->setNamespace('default', 'http://www.google.com');
    $this->addNamespaces(self::$apis);
    $this->setCallParameter('alt', 'json');
    $this->setAuthParameter('response_type', 'code');
    $this->setAccessParameter('grant_type', 'authorization_code');
    $this->setAlias('contacts', 'm8/feeds/contacts');

    if(isset($config['scope']))
    {
      $this->setAuthParameter('scope', implode(' ', $config['scope']));
    }

    $this->init($config, 'api', 'use');
  }

  public function useApi($api)
  {
    if(is_array($api))
    {
      foreach($api as $tmp_api)
      {
        $this->useApi($tmp_api);
      }
    }
    else
    {
      if(strlen($this->getAuthParameter('scope')) > 0)
      {
        $scope = explode(' ', $this->getAuthParameter('scope'));
      }
      else
      {
        $scope = array();
      }

      $scope[] = $this->getScopeByApiName($api);
      $scope = array_unique($scope);

      $this->setAuthParameter('scope', implode(' ', $scope));
    }
  }

  public function getScopeByApiName($api)
  {
    $api = strtolower($api);

    return self::$apis[$api];
  }
  
  public function getIdentifier()
  {
    $prevNs = $this->getCurrentNamespace();  
    if ($data = $this->ns('oauth2')->get('userinfo')) {
      $return = $data->id;
    } else {
      $return = null;
    }
    $this->ns($prevNs);
    return $return;
  }
}
