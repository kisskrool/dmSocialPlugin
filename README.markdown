# dmSocialPlugin #


dmSocialPlugin brings:

 * Basic classes to permit authentification to social networks using OAuth1 or OAuth2 (in lib/dmOAuth)
 * Model of the token used during connections
 * Easy connection between other service supports OAuth like Facebook, Yahoo, Google
 * For example you can use facebook api without fbml or javascript Api. It's can be an easy way to use facebook connect

The current Diem development is based on three plugins:

  * [sfMelodyPlugin](http://github.com/chok/sfMelodyPlugin)
  * [sfDoctrineOAuthPlugin](http://github.com/chok/sfDoctrineOAuthPlugin)
  * [OAuth](http://code.google.com/p/oauth/)

The Propel plugin wasn't used in the Diem version, as only Doctrine is needed. However the original Propel plugin can still be found at:
  * [sfDoctrineOAuthPlugin](http://github.com/chok/sfPropelOAuthPlugin)

## Installation ##

 * In a console, from your project root dir, run:
        git clone git://github.com/kisskrool/dmSocialPlugin.git plugins/dmSocialPlugin

 * In config/ProjectConfiguration.class.php, add dmSocialPlugin to the list of enabled plugins:

    class ProjectConfiguration extends dmProjectConfiguration
    {  
      public function setup()  
      {  
        parent::setup();
 
        $this->enablePlugins(array(  
          // your enabled plugins  
          'dmSocialPlugin'  
        ));

 * Install
 
        php symfony doctrine:generate-migrations-diff
        
        php symfony doctrine:migrate
 
 	    php symfony dm:setup
	 
 * Clear cache
 
	    php symfony cc
	  
 * Override the user in lib/vendor/diem/dmFrontPlugin/lib/user/dmFrontUser.php
  and/or lib/vendor/diem/dmAdminPlugin/lib/user/dmAdminUser.php 
 if you wish to allow social connections either in front or/and admin:
 
        - dmFrontUser: class dmFrontUser extends dmSocialUser
        - dmAdminUser: class dmAdminUser extends dmSocialUser
 
## Networks ##


At this time dmSocial has 8 networks:

 * Facebook
 * Google
 * Yahoo 
 * LinkedIn
 * MySpace
 * Twitter
 * Neutral1  #A base class to support OAuth1
 * Neutral2  #A base class to support OAuth2
 
 
 ## Configuration ##
 
 
Available options in app.yml  :

    all:
      social:
        create_user: false            # create a new user if not logged when you connect to a service
        redirect_register: false      # you can redirect to a register form or anything else by specify
                                      # a valid route like "@user_register" 
        name:
          key:  my_service_key
          secret: my_secret_key
          callback: @mymodule_index   # or absolute url to which it will be redirected after social authentication & authorisation
          
          #needed for google or facebook
          api: [contacts, ...]        # only for google - easy way to set scopes
          scope: [permission_1, ...]  # for google and facebook to set permissions - for google prefere api parameter
          
          #optional
          user:                       # to create a user
            field_1:
              call: xx
              call_parameters: [x, x, x]
              path: xx.xy.zz
              prefix: xx_
              suffix: _xx
              key: false
            ...
            
          provider: provider          #like google, facebook -optional if 'name' config key is the name of the provider
          request_token_url: url      # override the url - for OAuth 1 implementation only
          request_auth_url: url
          access_token_url: url
          namespaces:                 # namespaces are used to make some api calls - see namespace Section
            ns1: http://my.name.space
            ...
          aliases:                    # Alias is an easy way to simplify api calls
            me: my/alias   
            ...     
          auth_parameters:
            param1: value
          call_parameters:
            param1: value
          access_parameters:
            param1: value
          output_format: json
          create_user: true      
          redirect_register: false
          
          #optional only for OAuth 1
          request_parameters:
            param1: value
          request_token_url: url
          
          
In an action:

    $this->getUser()->socialConnect('name');
    
    
This action redirects to the callback specified in the app.yml



In an other action when you have the autorization:

    $this->getUser()->getNetwork('name')->getMe();  #see section Api for more informations
  
  
## Example ##


We try a sample with facebook, Register an application (see section Register application to have url to register apps).
Then put the config in app.yml:

    all:
      social:
        create_user: true               # to create a user for all networks
        facebook:
          key: my_api_key
          secret: application_secret
          callback: @homepage
          scope: [email]                #optional - https://developers.facebook.com/docs/reference/api/permissions/ 
                                        # needed to create an user based on his email_address
          user:
            username:                   # the name of a field of DmUser
              call: me                  # api call
              path: id                  # path could be user.object.email for example to retrieve the right inforamtion
              prefix: Facebook_
            first_name:                 # optional, because added to DmUser model
              call: me
              path: first_name          
            last_name:                  # optional, because added to DmUser model
              call: me
              path: last_name           
            email:                      # this field is mandatory as otherwise a new user won't be saved in database
              call: me                  
              path: email               
              key: true                 # it's a key to retrieve user from other services based on this information
                                        # if no field is a key all are keys by default.        
          
        facebook_plus:                  # you can manage more than one config for a service
          provider: facebook            # to manage permissions for example            
          ...
        google:
          ...
          


For example we put a link in a _signin partial:

    <?php echo _link('@social_connect?service=facebook')->text('Connect to facebook'); ?>
    
or we use the helper, passing the parameter values from the executeSignin method in components.class.php :

    <?php echo _social_connect_button($service, $route, __('Connect with').' '.$service, $options); ?>

In actions.class.php :

    public function executeConnect(dmWebRequest $request)
    {
      $service=$this->getRequestParameter('service');
      $this->getUser()->socialConnect($service);
    }
    
    public function executeFacebook(dmWebRequest $request)
    {
      $this->me = $this->getUser()->getNetwork('facebook')->getMe();
    }
    
    
## User ##

The user creation is a security point because for example if you create user based on email addresses. If a user create 
an account by a classic way and if the email specified is not validated he can retrieve rights on the user which have the email.

So you have to specify the user creation by putting some informations in the config file (app.yml).

The informations under the user key in config allow to create a user with theses informations. The keys (key:true) allow to retrieve 
existing user to make links between services or to signin a user according his created account.

### How it works : ###

If a user is authenticated and request access to a service. The rights are attached to him. 

There is an event (socialnetwork.filter_user) to filter a user if authenticated. This event has params network and conflict. 
network is the current network and conflict represents if the user match the key rules (app.yml). So in this filter you can change the user
or do anything you want in this situation.
 

If the user is not authenticated, it tries to retrieve the user by the token then it try to retrieve the user by the keys(config). 
And then create the user if needeed.

Before saving the user, if you specify redirect_register with a valid routing rule, you can redirect the workflow to your action.

In this case, you can retrieve the user created but not saved :

  * unserialize($this->getUser()->getAttribute('social_user'))
  
And the network :

  * unserialize($this->getUser()->getAttribute('network'))

    
    
## Api ##

When you call:
  
    $this->getUser()->getNetwork('facebook')->getMe();
    
In fact it's use the default namespace of facebook : https://graph.facebook.com

It calls the method dmOAuth->get($action, $aliases, $params, $method = 'GET')

getMe means get('me')

getMeFriends -> get('me/friends')


an alias is a way to have simpliest call for example :

    $fb_network->getFriends(array('friends' => 'me/friends'));
    
you can put your own aliases in the app.yml to have simplier calls.

## Apis ##

To make api calls, you have to know api for each service provider :

  * Google : http://code.google.com/intl/fr/apis/gdata/docs/directory.html
  * Facebook: http://developers.facebook.com/docs/reference/api/
  * Yahoo! : http://developer.yahoo.com/everything.html#apis
  * ...
  
## Register Application ##

 * Google: https://www.google.com/accounts/ManageDomains
 * Facebook: http://www.facebook.com/developers/apps.php
 * Yahoo! : https://developer.apps.yahoo.com/projects
 
 
## Namespaces ##

To change the namespace in use just use: 
  
    $google_network->ns('contacts');

 * Facebook
 
    * default : https://graph.facebook.com
    
    
 * Google:
 
    * default: http://www.google.com
    * analytics: http://www.google.com/analytics/feeds/
    * google_base: http://www.google.com/base/feeds/
    * book: http://www.google.com/books/feeds/
    * blogger: http://www.blogger.com/feeds/
    * calendar: http://www.google.com/calendar/feeds/
    * contacts: http://www.google.com/m8/feeds/
    * chrome: http://www.googleapis.com/auth/chromewebstore.readonly
    * documents: http://docs.google.com/feeds/
    * finance: http://finance.google.com/finance/feeds/
    * gmail: http://mail.google.com/mail/feed/atom
    * health: http://www.google.com/health/feeds/
    * h9: http://www.google.com/h9/feeds/
    * maps: http://maps.google.com/maps/feeds/
    * moderator: tag:google.com,2010:auth/moderator
    * open_social: http://www-opensocial.googleusercontent.com/api/people/
    * orkut: http://www.orkut.com/social/rest
    * picasa: http://picasaweb.google.com/data/
    * sidewiki: http://www.google.com/sidewiki/feeds/
    * sites: http://sites.google.com/feeds/
    * spreadsheets: http://spreadsheets.google.com/feeds/
    * wave: http://wave.googleusercontent.com/api/rpc
    * webmaster_tools: http://www.google.com/webmasters/tools/feeds/
    * youtube: http://gdata.youtube.com

You can use all these namespaces in the api config to have permissions to use them.
     
  * Twitter
  
      * default: http://api.twitter.com

  * Yahoo !

      * default:  http://social.yahooapis.com/v1
      
   * LinkedIn

      * default:  https://api.linkedin.com/v1
      
   * MySpace

      * default:  http://api.myspace.com/v1
      
      
## Aliases ##

Default aliases :

  * Google
    
      * contacts: m8/feeds/contacts
      * me: default/full
      
  * Yahoo !
    
      * uid: [userid]
      * me: user/[userid]

  * LinkedIn
    
      * me: people/~
      
  * MySpace
    
      * me: user.json      


## Contribute ##

If you want more providers or more default aliases. Feel free to ask or contribute on Github at http://github.com/chok/sfMelodyPlugin
for example adding support to some networks (ie. melodies by this plugin), or at http://github.com/kisskrool/dmSocialPlugin
 
## TODO dmSocialPlugin ##

 * Tests for other networks than Facebook
 * Some kind of widget to transmit some parameters from the app.yml (like which network to use, the image, etc) to the Signin button
 * Other ideas are welcome :-)

## TODO sfMelodyPlugin ##

 * Better management of tokens (refresh, etc...)
 * ...
