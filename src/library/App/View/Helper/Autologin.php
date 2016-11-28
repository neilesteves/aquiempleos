<?php

/**
 * Description of Attribs
 *
 * @author eanaya
 */
class App_View_Helper_Autologin extends Zend_View_Helper_HtmlElement
{

    public function Autologin($valor = false)
    {
        $config = Zend_Registry::get('config');
        $AppFacebook = $config->apis->facebook;
        $urlAuthFacebook = $config->app->siteUrl . '/registro/facebook';
        $tkenFacebook = new Zend_Session_Namespace('tokenFacebook');
        $tokenFacebook = $tkenFacebook->token;
        
        $redirectFB = '';
        $redirectIN = '';
        
        if ($valor) { 
            return  "
                <script type=\"text/javascript\">
                window.fbAsyncInit = function() {
                  // init the FB JS SDK
                  FB.init({
                    appId      : '{$AppFacebook->appid}', // App ID from the App Dashboard
                    status     : true, // check the login status upon init?
                    cookie     : true, // set sessions cookies to allow your server to access the session?
                    xfbml      : true  // parse XFBML tags on this page?
                  });

                // Load the SDK's source Asynchronously
                  (function(d){
                     var js, id = 'facebook-jssdk', ref = d.getElementsByTagName('script')[0];
                     if (d.getElementById(id)) {return;}
                     js = d.createElement('script'); js.id = id; js.async = true;
                     js.src = \"//connect.facebook.net/en_US/all.js\";
                     ref.parentNode.insertBefore(js, ref);
                   }(document));     
                 }     
                </script>";
        }

        if (!Zend_Session::namespaceIsset('dataFacebook')) {
            $redirectFB = "window.location.href = 'http://www.facebook.com/dialog/oauth?client_id={$AppFacebook->appid}&redirect_uri=$urlAuthFacebook&scope=email&state=$tokenFacebook';";
        } 
        
        if (!Zend_Session::namespaceIsset('dataLinkedin')) {
            $redirectIN = "window.location.href = '/registro/linkedin';";
        } 
        
        return "
    <script type=\"text/javascript\">
        window.fbAsyncInit = function() {
          // init the FB JS SDK
          FB.init({
            appId      : '{$AppFacebook->appid}', // App ID from the App Dashboard
            status     : true, // check the login status upon init?
            cookie     : true, // set sessions cookies to allow your server to access the session?
            xfbml      : true  // parse XFBML tags on this page?
          });


          // Additional initialization code such as adding Event Listeners goes here
          FB.getLoginStatus(function(response) {        
              if(response.status == 'connected') {
                  $redirectFB
              }            
          });
        };


        // Load the SDK's source Asynchronously
        (function(d){
           var js, id = 'facebook-jssdk', ref = d.getElementsByTagName('script')[0];
           if (d.getElementById(id)) {return;}
           js = d.createElement('script'); js.id = id; js.async = true;
           js.src = \"//connect.facebook.net/es_LA/all.js\";
           ref.parentNode.insertBefore(js, ref);
         }(document));
        
        /*function onLinkedInLoad() {
            if (IN.User.isAuthorized()) {
                $redirectIN
            }                
        }
            
        (function()
        {
        var e = document.createElement('script');
        e.type = 'text/javascript';
        e.async = false;
        e.src = 'http://platform.linkedin.com/in.js?async=true';
        e.onload = function(){
        IN.init({onLoad: 'onLinkedInLoad', api_key: '{$config->apis->linkedin->consumerKey}', authorize: true});
        };
        var s = document.getElementsByTagName('script')[0];
        s.parentNode.insertBefore(e, s);
        })();*/
    </script>";

    }

}