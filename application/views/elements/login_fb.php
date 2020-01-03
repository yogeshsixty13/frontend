<?php 
$this->session->set_userdata( array( "FB_HTTP_REFERER" => $_SERVER["REQUEST_URI"] ) );
//$appId = "738573352925856"; 
?>
<div id="fb-root"></div>
<script type="text/javascript">

var isLoaded = false;
  window.fbAsyncInit = function() {
    FB.init({
      appId      : '<?php echo facebookAppID();?>', // App ID
      status     : true, // check login status
      cookie     : true, // enable cookies to allow the server to access the session
      xfbml      : true  // parse XFBML
	  //version    : 'v2.3'
    });
    isLoaded = true;

    //facebook_login(); 
    // Additional initialization code here
  };
  // Load the SDK Asynchronously
  (function(d){
     var js, id = 'facebook-jssdk', ref = d.getElementsByTagName('script')[0];
     if (d.getElementById(id)) {return;}
     js = d.createElement('script'); js.id = id; js.async = true;
     js.src = "//connect.facebook.net/en_US/all.js";
     ref.parentNode.insertBefore(js, ref);
   }(document));
   

function facebook_login() 
{
	if(!isLoaded) 
	{
		//alert("JS-SDK is not yet loaded. Try again after few seconds.");
		setTimeout( function() { facebook_login(); }, 1000 );
		alert("Please wait...");
		return false;
	}
	FB.login(function(response) 
	{
		if (response.authResponse) 
		{
			var fb_access_token = response.authResponse.accessToken;
			FB.api('/me', function(response) 
			{
				document.getElementById('fb_user_fname').value = response.first_name;
				document.getElementById('fb_user_lname').value = response.last_name;
				document.getElementById('fb_user_email').value = response.email;
				document.getElementById('fb_facebook_id').value = response.id;
				document.getElementById('fb_facebook_offline_token').value = fb_access_token;
				document.facebook_login_form.submit();
			});
		}
	},{scope: 'email,publish_stream,user_birthday,status_update,offline_access'});
	return false;
}

</script>
<form method="post" name="facebook_login_form" action="<?php echo site_url('login/facebookSignup');?>">
	<input type="hidden" name="fb_user_fname" id="fb_user_fname" value="" />
	<input type="hidden" name="fb_user_lname" id="fb_user_lname" value="" />
	<input type="hidden" name="fb_user_email" id="fb_user_email" value="" />
	<input type="hidden" name="fb_facebook_id" id="fb_facebook_id" value="" />
	<input type="hidden" name="fb_facebook_offline_token" id="fb_facebook_offline_token" value="" />
</form>