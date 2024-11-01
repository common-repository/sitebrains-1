<?php /*
Plugin Name: SiteBrainsPlugin
Plugin URI: http://www.sitebrains.com
Description: The SiteBrains plugin.
Version: 6.3.7
Author: SiteBrains
Author URI: http://www.sitebrains.com
License: GPL2
*/
add_action('pre_comment_on_post', 'CheckSignature');

// when a comment is posted this function is called
// to make sure the comment passed sitebrains validation
function CheckSignature($wpv_commentid) 
{
	// Read sitebrains options from wordpress database
	$opt = getAdminOptions();

	// Create a new instance of SiteBrainsEngine
	$SiteBrainsEngine=New Engine($opt['DomainID'],$opt['SecretKey']);

	// Verify report is signed correctly and that 
	// the comment is not spam
	$valid=$SiteBrainsEngine->VerifyScriptValidation();

	$res="<br/>Overall: ";
	if ($valid) $res.="<span style='color:green'>Valid</span>";
	else $res.="<span style='color:red'>Not valid</span>";

	//$res=$res."<br/>Signature valid: ".($SiteBrainsEngine->Response->SignatureValid ? "Yes" : "No");
	//$res=$res."<br/>Validation result: ".$SiteBrainsEngine->Response->ValidationResult;
	//$res=$res."<br/>Recommended action: ".$SiteBrainsEngine->Response->RecommendedActionText;
	//$res=$res."<br/>Trust score: ".$SiteBrainsEngine->Response->TrustScore;
	//$res=$res."<br/>External link trust score: ".$SiteBrainsEngine->Response->ExternalLinkTrustScore;
	//$res=$res."<br/>Spam probability: ".$SiteBrainsEngine->Response->SpamProbability;
	//$res=$res."<br/>Grammar score: ".$SiteBrainsEngine->Response->GrammarScore;
	//$res=$res."<br/>Commercial intent: ".$SiteBrainsEngine->Response->CommercialIntent;
	//$res=$res."<br/>".$opt['DomainID']." - ".$opt['SecretKey'];

	// You can store that information next to the comment
	// or use it to mark for moderation
	
	if(!$valid)
	{
		// Invalid posts are blocked at the client's browser
		// If an invalid comment got here, JavaScript ot the entire page
		// were avoided.
	    	wp_die('Your post could not be validated,<br/>Make sure JavaScript is enabled in your browser and try again.'.$res);
	}
}
 
// Adds reference to sitebrains client side script
function sitebrainstag() {

	// Read sitebrains options
	$opt = getAdminOptions();

	// Create a new instance of SiteBrainsEngine
	$SiteBrainsEngine=New Engine($opt['DomainID'],$opt['SecretKey']);

	// Add script validation
	$SiteBrainsEngine->AddScriptValidation();

}

// Read the sitebrains configurations from the wordpress database
// if non exist, creates a new account and stores the configurations 
function getAdminOptions() {

	//delete_option("AC_Admin_Keys");

	$devloungeAdminOptions = array('DomainID' => '',
	'SecretKey' => '',
	'Password' => '',
	'LogIn' => ''
	);
	$devOptions = get_option("AC_Admin_Keys");

	if (!empty($devOptions)) {
		// Options were found on the wordpress database
		foreach ($devOptions as $key => $option)
			$devloungeAdminOptions[$key] = $option;
	}
	else
	{
		//// No options in database, register a new account
		// Canceled and moved to after user confirmation

		//$admin_email=get_bloginfo ( 'admin_email' );
		//$siteurl=get_bloginfo ( 'siteurl' );
		//$blogname=get_bloginfo ( 'name' );
                               
//$reg=Engine::MakePostRequest("http://www.sitebrains.com/users/auth.ashx", //"action=Owners_CreateAnonimus&email=$blogname&siteurl=$blogname&name=$blogname");

		//list($admin_email, $domainid, $pass, $SecretKey) = split("\|", $reg);

		//$devloungeAdminOptions = array('DomainID' => $domainid,
		//'SecretKey' => $SecretKey,
		//'Password' => $pass,
		//'LogIn' => $admin_email
		//);
		
		// save the new configurations
		//update_option("AC_Admin_Keys", $devloungeAdminOptions);
		
		// use default wp account
		$devloungeAdminOptions = array('DomainID' => 2,
		'SecretKey' => '',
		'Password' => '',
		'LogIn' => get_bloginfo ( 'admin_email' )
		);

	}
	return $devloungeAdminOptions;
}

if ( is_admin() )
{
	add_action('admin_menu', 'sitebrains_ThemeMenu' ); 
}
else
{
	add_action('get_header', 'sitebrainstag');
}

register_activation_hook( __FILE__, 'sitebrainsplugin_activate' );

function sitebrainsplugin_activate()
{
	delete_option("AC_Admin_Keys");
	update_option("AC_Admin_State", "just_activated");
}

function sitebrains_ThemeMenu(){
	$devState = get_option("AC_Admin_State");
	if ($devState=="just_activated")
	{
		update_option("AC_Admin_State", "just_shown");
		wp_redirect("edit-comments.php?page=Index.php");
		return;
	}
	
	global $ThemeName;
	$meta='';
	$opt = getAdminOptions();
	if ($opt['SecretKey']  =='' && (empty($_POST) || $_POST["login"]==''))  $meta=' (att.req.)';

	add_comments_page( $ThemeName . ' Sitebrains Options', $ThemeName . ' SiteBrains'.$meta, 'edit_themes', basename(__FILE__), 'sitebrains_ThemePage' );
}

function sitebrains_ThemePage() {
	$opt = getAdminOptions();
	if ($opt['SecretKey']  =='') // unregistered
	{

	if ( !empty( $_POST["login"] ) )
	{
		if ( empty($_POST) || !wp_verify_nonce($_POST['sitebrainsactivate_nonce_field'],'sitebrainsactivate_action') )
		{
		   wp_die ('Sorry, your nonce did not verify.');
		   exit;
		}
		else
		{
			// create account

			$admin_email=get_bloginfo ( 'admin_email' );
			$siteurl=get_bloginfo ( 'siteurl' );
			$blogname=get_bloginfo ( 'name' );
			$preset=$_POST["hidpreset"];
//                               wp_die($preset);
			$reg=Engine::MakePostRequest("http://www.sitebrains.com/users/auth.ashx", "action=Owners_CreateAnonimus&email=$admin_email&siteurl=$siteurl&name=$blogname&preset=$preset");
	
			list($admin_email, $domainid, $pass, $SecretKey) = split("\|", $reg);

			$devloungeAdminOptions = array('DomainID' => $domainid,
			'SecretKey' => $SecretKey,
			'Password' => $pass,
			'LogIn' => $admin_email
			);
		
			// save the new configurations
			update_option("AC_Admin_Keys", $devloungeAdminOptions);

?>

<br/>
<table style="width:600px;border:1px solid #ababab;background-color:#b1d2fa;font-size:10px;-moz-box-shadow: 3px 3px 3px #aaa;-webkit-box-shadow: 3px 3px 3px #aaa;box-shadow: 3px 3px 3px #aaa;-moz-border-radius: 10px;border-radius: 10px;" cellspacing="0px" cellpadding="0px">
<tr valign="top"><td scope="row">
<div class="wrap" id="custom-background"><?php screen_icon(); ?><h2><?php _e('SiteBrains.com'); ?></h2>
</td></tr>
<tr style="background-color:white;"><td style="padding:10px;">
<b>Your account at sitebrains was activated successfully!</b>
<br/>
<br/>
Server side validation via your dedicated secret key is now active.
</td></tr><tr><td align="right"><span style="color:white;font-size:10px;line-height:14px;" ><i>SiteBrains.com&nbsp;</i></span></td></tr>
</table>
<?php

		}
	}
else
{
?>
<form method="post" action="">
<input type="hidden" name="login" value="login"/>
<input type="hidden" name="hidpreset" id="hidpreset" value="2"/>
<br/>
<table style="width:600px;border:1px solid #ababab;background-color:#b1d2fa;font-size:10px;-moz-box-shadow: 3px 3px 3px #aaa;-webkit-box-shadow: 3px 3px 3px #aaa;box-shadow: 3px 3px 3px #aaa;-moz-border-radius: 10px;border-radius: 10px;" cellspacing="0px" cellpadding="0px">
<tr valign="top"><td scope="row">
<div class="wrap" id="custom-background"><?php screen_icon(); ?><h2><?php _e('SiteBrains.com'); ?></h2>
</td></tr>
<tr style="background-color:white;"><td style="padding:10px;">
<b>Thank you for using the sitebrains spam detection plug-in!</b>
<br/>
<br/>
While client side validations are active on your site, <b>you still need to activate your account</b> to have a dedicated secret key assigned to your domain and to enable the server side validation.
<br/>
<br/>
  <table>
    <tr>
      <td valign="center" style="width:40px;">
        <span style="color:green;font-size:20px;font-weight:bold;background-color:#efefef;border:1px solid grey;padding:2px;">
          <i>&nbsp;i&nbsp;</i>
        </span>
      </td>
      <td valign="center">
        To activate your account <b>click 'Activate account'</b><br/>
        Your email adress and blog name will be sent to our servers <br/>
	and will be used as your login name and default domain.<br/><br/>

      </td>
	<td width='350px'>
	
		<p class="submit" align="center">
		  <input id="btnsubmit" type="submit" class="button-primary" value="<?php _e('Activate account') ?>" />
		</p>

	</td>
    </tr>
    <tr>
      <td colspan="3" align="right">



















	<script>

function showPreset(ind)
{
	document.getElementById('hidpreset').value=ind;
	var indn=ind;//Math.round((ind+5)/20);
	if (indn<1) indn=1;	
	if (indn>5) indn=5;
	for (var i=1;i<6;i++)
	{
		document.getElementById('dvspamhelp'+i).style.display='none';
		if (i==5) break;	
		if (i>=indn) 
		{
			document.getElementById('spqoc'+i).style.color='green';
			document.getElementById('imgqoc'+i).src='http://dev.sitebrains.com/images/allowicon.png';
			if (i!=indn) document.getElementById('chkspam'+i).checked=true;
		}
		else
		{
			document.getElementById('spqoc'+i).style.color='red';
			document.getElementById('imgqoc'+i).src='http://dev.sitebrains.com/images/blockicon.png';
			if (i!=indn) document.getElementById('chkspam'+i).checked=false;
		}
	}
	document.getElementById('dvspamhelp'+indn).style.display='inline';
	
}
function showtab(ind)
{
document.getElementById('dvspam1').style.display='none';
document.getElementById('dvspam2').style.display='none';
document.getElementById('dvspam3').style.display='none';
var i=Math.round((ind[0]+50)/100);
document.getElementById('dvspam'+i).style.display='inline';
}
	</script>

<table cellpadding=0 cellspacing=0; align=center width=850px>
<tr>
<td>
<div id="tblpreset" style="background-image:url(http://dev.sitebrains.com/images/wizbg4.png);width:850px;align:center;">
<table style="width:850px;height:380px;border:1px solid #aabbdd;background-image:url(http://dev.sitebrains.com/images/wizinfobg1.png);background-position:right 7px;background-repeat:no-repeat;">
<tr style='height:50px;'><td colspan='2'><br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Select the quality of conversation you are willing to accept
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<br/><br/>
</td>
<td rowspan=2 style="padding:23px;padding-top:36px;" width=300 valign=top>
	<div id="dvspamhelp1" style='display:none;'>
		<b>Blocks comment spam</b><hr/>
		This preset allowes anything that is not spam.<br/>
		It is suitable for loose blogs with no age limitation or minimum comment quality.<br/>
		<br/>
		<span style='color:grey'>Sample of a blocked comment:</span><br/>
		<img src='http://dev.sitebrains.com/images/quots1.png' style='vertical-align:top;'><span style='color:red'>Check out www.youporn.com</span><img src='http://dev.sitebrains.com/images/quots2.png' style='vertical-align:bottom;'>	<br/>
		<span style='color:grey'>Sample of an approved comment:</span><br/>
		<img src='http://dev.sitebrains.com/images/quots1.png' style='vertical-align:top;'><span style='color:green'>wooooowww!!!</span><img src='http://dev.sitebrains.com/images/quots2.png' style='vertical-align:bottom;'>	<br/>
		<span style='color:grey'>Extream sample of an approved comment:</span><br/>
		<img src='http://dev.sitebrains.com/images/quots1.png' style='vertical-align:top;'><span style='color:green'>Shit fuck, this sucks</span><img src='http://dev.sitebrains.com/images/quots2.png' style='vertical-align:bottom;'>	<br/>		

	</div>
	<div id="dvspamhelp2" style='display:none;'>
		<b>Blocks spam and hard profanity</b><hr/>
		This preset blocks spam and extream profanity or hate talk.<br/>
		It is suitable for loose blogs and forms with no age limitation or minimum comment quality.<br/>
		<br/>
		<span style='color:grey'>Sample of a blocked comment:</span><br/>
		<img src='http://dev.sitebrains.com/images/quots1.png' style='vertical-align:top;'><span style='color:red'>Shit fuck, this sucks</span><img src='http://dev.sitebrains.com/images/quots2.png' style='vertical-align:bottom;'>	<br/>
		<span style='color:grey'>Sample of an approved comment:</span><br/>
		<img src='http://dev.sitebrains.com/images/quots1.png' style='vertical-align:top;'><span style='color:green'>I think this is pure bullshit</span><img src='http://dev.sitebrains.com/images/quots2.png' style='vertical-align:bottom;'>	<br/>
	</div>
	<div id="dvspamhelp3" >
		<b>Blocks spam and any profanity</b><hr/>
		This preset blocks spam and any type of profanity or hate talk.<br/>
		It is suitable for blogs and forms with age limitation and no minimum comment quality.<br/>
		<br/>
		<span style='color:grey'>Sample of a blocked comment:</span><br/>
		<img src='http://dev.sitebrains.com/images/quots1.png' style='vertical-align:top;'><span style='color:red'>What bullshit</span><img src='http://dev.sitebrains.com/images/quots2.png' style='vertical-align:bottom;'>	<br/>
		<span style='color:grey'>Sample of an approved comment:</span><br/>
		<img src='http://dev.sitebrains.com/images/quots1.png' style='vertical-align:top;'><span style='color:green'>wow!</span><img src='http://dev.sitebrains.com/images/quots2.png' style='vertical-align:bottom;'>	<br/>
		
	</div>
	<div id="dvspamhelp4" style='display:none;'>
		<b>Blocks Spam, any profanity, and valueless comments</b><hr/>
		This preset blocks spam, any type of profanity or hate talk, and enfources comment quality.<br/>
		It is suitable for serious blogs and forms where each post should bring value to the conversation.<br/>
		<br/>
		<span style='color:grey'>Sample of a blocked comment:</span><br/>
		<img src='http://dev.sitebrains.com/images/quots1.png' style='vertical-align:top;'><span style='color:red'>Thanks, user123</span><img src='http://dev.sitebrains.com/images/quots2.png' style='vertical-align:bottom;'>	<br/>
		<span style='color:grey'>Sample of an approved comment:</span><br/>
		<img src='http://dev.sitebrains.com/images/quots1.png' style='vertical-align:top;'><span style='color:green'>Have you tryed looking at sitebrains.com? they have a great solution for quality assesment.</span><img src='http://dev.sitebrains.com/images/quots2.png' style='vertical-align:bottom;'>	<br/>
		
	</div>
	<div id="dvspamhelp5" style='display:none;'>
		<b>Well formed, information baring only</b><hr/>
		This preset allowes only high quality comments.<br/>
		It is suitable for loose descussion forums with where comments are expected to bring additional value to the conversation.<br/>
		<br/>
		<span style='color:grey'>Bloacked samples:</span><br/>
		&nbsp;&nbsp;&nbsp;&nbsp;<img src='http://dev.sitebrains.com/images/quots1.png' style='vertical-align:top;'><span style='color:red'>bla asdasd bla miaw</span><img src='http://dev.sitebrains.com/images/quots2.png' style='vertical-align:bottom;'>	<br/>
		
		&nbsp;&nbsp;&nbsp;&nbsp;<img src='http://dev.sitebrains.com/images/quots1.png' style='vertical-align:top;'><span style='color:red'>Thanks!</span><img src='http://dev.sitebrains.com/images/quots2.png' style='vertical-align:bottom;'>	<br/>
		<span style='color:grey'>Approved samples:</span><br/>
		&nbsp;&nbsp;&nbsp;&nbsp;<img src='http://dev.sitebrains.com/images/quots1.png' style='vertical-align:top;'><span style='color:green'>To my opinion it is only a question of time before google comes up with their own hardware to run Android.</span><img src='http://dev.sitebrains.com/images/quots2.png' style='vertical-align:bottom;'>	<br/>		
		
	</div>

</td>
</tr>
<tr>
<td width='100px' style='line-height:36px;' valign='top' align='right'>
Strict&nbsp;&nbsp;&nbsp;<input type='checkbox' checked disabled/>
<br/>&nbsp;<input id='chkspam4' type='checkbox' onchange='if (!this.checked) showPreset(5); else showPreset(4);' checked/>
<br/>&nbsp;<input id='chkspam3' type='checkbox' onchange='if (!this.checked) showPreset(4); else showPreset(3);' checked/>
<br/>&nbsp;<input id='chkspam2' type='checkbox' onchange='if (!this.checked) showPreset(3); else showPreset(2);' checked/>
<br/>&nbsp;<input id='chkspam1' type='checkbox' onchange='if (!this.checked) showPreset(2); else showPreset(1);'/>
<br/>Loose&nbsp;&nbsp;&nbsp;<input type='checkbox' disabled/>
<br/>

</td><td  valign='top' style='line-height:36px;padding-left:15px;'>
<span id='spqoc5' style='color:green;'><img id='imgqoc5' style='vertical-align:middle;' src='http://dev.sitebrains.com/images/allowicon.png'/> &nbsp;&nbsp;&nbsp;Well formed, high quality posts</span><br/>
<span id='spqoc4' style='color:green;'><img id='imgqoc4' style='vertical-align:middle;' src='http://dev.sitebrains.com/images/allowicon.png'/> &nbsp;&nbsp;&nbsp;Poor grammar or syntax</span><br/>
<span id='spqoc3' style='color:green;'><img id='imgqoc3' style='vertical-align:middle;' src='http://dev.sitebrains.com/images/allowicon.png'/> &nbsp;&nbsp;&nbsp;Short chat-like comments.</span><br/>
<span id='spqoc2' style='color:red;'><img id='imgqoc2' style='vertical-align:middle;' src='http://dev.sitebrains.com/images/blockicon.png'/> &nbsp;&nbsp;&nbsp;Mild foul language</span><br/>
<span id='spqoc1' style='color:red;'><img id='imgqoc1' style='vertical-align:middle;' src='http://dev.sitebrains.com/images/blockicon.png'/> &nbsp;&nbsp;&nbsp;Any profanity</span><br/>
<span id='spqoc0' style='color:red;'><img id='imgqoc0' style='vertical-align:middle;' src='http://dev.sitebrains.com/images/blockicon.png'/> &nbsp;&nbsp;&nbsp;Commercial link spam</span><br/>
<p class="submit" align="left">
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;  <input id="btnsubmit" type="submit" class="button-primary" value="<?php _e('Activate account') ?>" />
</p>
</td></tr> 
</table>
</div>






</td></tr></table>
</div>
















    </td>
  </tr>
  </table>
  
</td></tr><tr><td align="right"><span style="color:white;font-size:10px;line-height:14px;" ><i>SiteBrains.com&nbsp;</i></span></td></tr>
</table>
 <?php wp_nonce_field('sitebrainsactivate_action','sitebrainsactivate_nonce_field'); ?>
</form><?php
}		
}
else
{
	if ( !empty( $_POST["login"] ) )
	{

		if ( empty($_POST) || !wp_verify_nonce($_POST['sitebrainsconfig_nonce_field'],'sitebrainsconfig_action') )
		{
		   wp_die ('Sorry, your nonce did not verify.');
		   exit;
		}
		else
		{
			$opt['LogIn'] =$_POST["login"];
			$opt['Password'] =$_POST["pass"];
			$opt['SecretKey'] =$_POST["seckey"];
			$opt['DomainID'] =$_POST["DomainID"];

    $res = Engine::MakePostRequest("http://www.sitebrains.com/users/auth.ashx", "action=Owners_Connect&email=".$opt['LogIn']."&pass=".$opt['Password']."&DomainID=".$opt['DomainID']."&siteurl=".$siteurl."&name=".$blogname);

    if ($res=='Denied')
	  {
?><br/>
    <br/>
    <h2>
      The user name and password <span style="color:red;">do not match</span>
    </h2><?php
      
    }
    else
    {
      list($sk, $di)= split("\|", $res);
 		  $opt['SecretKey'] = $sk;
		  $opt['DomainID'] = $di;

		 update_option("AC_Admin_Keys", $opt);
?><br/><br/><h2>
Your sitebrains settings were saved <span style="color:green;">successfully!</span>
</h2><?php
	    }
	}
}

?><script language="javascript">

  var accountChanged=false;

</script>
<form method="post" action="">
<br/>






















<table style="width:600px;border:1px solid #ababab;background-color:#b1d2fa;font-size:10px;-moz-box-shadow: 3px 3px 3px #aaa;-webkit-box-shadow: 3px 3px 3px #aaa;box-shadow: 3px 3px 3px #aaa;-moz-border-radius: 10px;border-radius: 10px;" cellspacing="0px" cellpadding="0px">
<tr valign=	"top"><td scope="row">
<div class="wrap" id="custom-background"><?php screen_icon(); ?><h2><?php _e('SiteBrains.com'); ?></h2>
</td></tr>	
<tr valign="top" style="background-color:#efefef;"><td scope="row" style="padding:10px;">Your login at sitebrains.com:</td></tr>
<tr style="background-color:#efefef;"><td style="padding:10px;" align="right"><input onchange="accountChanged=true;" style="width:80%;"input type="text" name="login" value="<?php echo $opt['LogIn']; ?>" /></td></tr>
<tr valign="top" style="background-color:#e0e0e0;"><td scope="row" style="padding:10px;">Your password at sitebrains.com:</td></tr>
<tr style="background-color:#e0e0e0;"><td style="padding:10px;" align="right"><input onchange="accountChanged=true;" style="width:50%;margin-right:30%;" type="password" name="pass" value="<?php echo $opt['Password']; ?>" /><br/><a style="cursor:pointer;" onclick="document.getElementById('dvshowpass').style.display='inline';">Show password</a> <span id="dvshowpass" style="display:none;"><?php echo $opt['Password']; ?></span>
</td></tr>
<tr valign="top" style="background-color:#efefef;"><td scope="row" style="padding:10px;">Your SiteBrains DomainID:</td></tr>
<tr style="background-color:#efefef;"><td style="padding:10px;" align="right"><input onchange="accountChanged=true;" style="width:50%;margin-right:30%;" type="text" name="DomainID" value="<?php echo $opt['DomainID']; ?>" /></td></tr>
<tr valign="top" style="background-color:#e0e0e0;display:none;"><td scope="row" style="padding:10px;">Your SiteBrains Key:</td></tr>
<tr style="background-color:#e0e0e0;display:none;"><td style="padding:10px;" align="right">
  <input onchange="accountChanged=true;" style="width:80%;"type="text" name="seckey" value="<?php echo $opt['SecretKey']; ?>" /></td></tr>
<tr style="background-color:white;"><td style="padding:10px;">
  <table>
    <tr>
      <td valign="center" style="width:40px;">
        <span style="color:green;font-size:20px;font-weight:bold;background-color:#efefef;border:1px solid grey;padding:2px;">
          <i>&nbsp;i&nbsp;</i>
        </span>
      </td>
      <td valign="center">
        To connect to an existing account, <br/>
        enter your SiteBrains' username and password and click 'change account'<br/>

      </td>
    </tr>
  </table>
  
  <p class="submit" align="center">
  <input id="btnsubmit" onclick="if (!accountChanged) { alert('You are already connected to that account\r\nEnter the login and password of the new account.');return false;}" type="submit" class="button-primary" value="<?php _e('Change account') ?>" />
 <input type="button" class="button-primary" onclick="this.form.action='http://www.sitebrains.com/editor.aspx';this.form.target='_blank';this.form.submit();this.form.action='';" value="Open Sitebrains Editor"/>
</p></td></tr><tr><td align="right"><span style="color:white;font-size:10px;line-height:14px;" ><i>SiteBrains.com&nbsp;</i></span></td></tr>
</table>
 <?php wp_nonce_field('sitebrainsconfig_action','sitebrainsconfig_nonce_field'); ?>
</form><?php
}
}


//====================================================================================
//=================================== Engine Class ===================================
//====================================================================================

// This class encapsulate all the functionality required to 
// consume sitebrains.com services.
// Initiate it passing your domainID and secret key to the constructor.
// Use the AddField method to add all the fields to be validated.
// Call the Validate() method.
// and the full analysis will be available at the Response 
// property.

class Engine
{
	// Engine constructor
	
	function __construct($DomainID,$SecretKey)
	{
		if (!is_numeric($DomainID))
		{
			$DomainID="0";
		} 
		$this->Init();
		$this->DomainID=$DomainID;
		$this->Secretkey=$SecretKey;
	} 

	// Properties and default values
	// Private members

	var $Fields;
	var $FieldsCount;

	function Init()
	{
//		$this->Fields="";
		$this->FieldsCount=0;
		$this->DomainID=0;
		$this->ResponseType="XML";
		$this->Secretkey="";
		$this->IP=$_SERVER["REMOTE_ADDR"];
		$this->Page=$_SERVER["SCRIPT_NAME"];
		if ($_SERVER["QUERY_STRING"]>"")
		{
		  $this->Page=$this->Page."?".$_SERVER["QUERY_STRING"];
		} 
	} 

	// Public properties
	// Validation configurations

	// The domain ID from sitebrains.com
	// If a zero is passed the default configuration with no 
	// custom rules and reports is used
	var $DomainID;

	// The secret key from sitebrains.com
	// If an empty string is passed, the signature of the report
	// will not be verified
	var $Secretkey;

	// The page part including path and querysting variables
	// that is being validated. 
	// If left empty the page from the referrer server variable
	// is used
	var $Page;

	// The IP of the client machine submitting the form
	// If left empty the IP from the host address server
	// variable is used
	var $IP;

	// The format type of the response
	// The default is XML. If another format is used
	// the response object members will not be loaded
	var $ResponseType;

	// Analysis result

	// This property holds a structure with easy access
	// parameters to all the analysis result values and scores.
	// This member is only loaded after the call to the Validate() 
	// method and only if the default, XML response format is used
	var $Response;

	// Add a text to be validated as a comment
	// It is recommended to use the overload that accepts 
	// the input name and type as well
	// param text:   The text to be validated
	function AddField($inputName,$inputType,$text)
	{
		$this->FieldsCount=$this->FieldsCount+1;
		$this->Fields=$this->Fields."&name".$this->FieldsCount."=".$this->URLEncode($inputName)."&type".$this->FieldsCount."=".$this->URLEncode($inputType)."&text".$this->FieldsCount."=".$this->URLEncode($text);
	} 

	function URLEncode($Data)
	{
		$function_ret=str_replace("&","[param]",$Data);
		return $function_ret;
	} 

	function VerifyScriptValidation()
	{
		if ($this->Secretkey=='') return true;
		//print ("ac_Metadata".$_POST["ac_Metadata"]);
		if ($_POST["ac_Metadata"]>"")
		{
			$acMetadata = $_POST["ac_Metadata"];
			//$acMetadata=str_replace("\\n","\n",$acMetadata);
			$this->Response=new ValidatedPage($this->Secretkey,$this->DomainID, $acMetadata);
			if ($this->Response->SignatureValid && $this->Response->IsValid) return true;
		}
		return false;
	}
	function AddScriptValidation()
	{
		print("<script src='http://www.sitebrains.com/scripts/acBase.ashx?domainid=".$this->DomainID."' ></script>");
		if(is_user_logged_in()) 
		{
			$current_user = wp_get_current_user();
			if ($current_user!=null)
			{
				print("<script language='javascript' >var ac_or_username='".$current_user->user_login."';var ac_or_useremail='".$current_user->user_email."';</script>");

			}
		}
	}
	function MakePostRequest($url,$data)
	{
		$url = parse_url($url);
		if ($url['scheme'] != 'http') 
		{ 
			die('Error: Only HTTP request are supported !');
		}
 
		// extract host and path:
		$host = $url['host'];
		$path = $url['path'];
 
		// open a socket connection on port 80 - timeout: 30 sec
		$er=Eval("

		\$fp = fsockopen(\"".$host."\", 80, \$errno, \$errstr, 30);

		if (\$fp)
		{
			f"."puts(\$fp, \"POST $path HTTP/1.1\\r\\n\");
			f"."puts(\$fp, \"Host: $host\\r\\n\");
			f"."puts(\$fp, \"Content-type: application/x-www-form-urlencoded\\r\\n\");
			f"."puts(\$fp, \"Content-length: ". strlen($data) ."\\r\\n\");
			f"."puts(\$fp, \"Connection: close\\r\\n\\r\\n\");
			f"."puts(\$fp, \"$data\");
			\$result = ''; 
			while(!f"."eof(\$fp)) 
			{
				\$result .= f"."gets(\$fp, 128);
			}
		}
		else 
		{ 
			return 'status '.'err '.'error ';
		}
		f"."close(\$fp);
		\$result = explode(\"\\r\\n\\r\\n\", \$result, 2);
		\$header = isset(\$result[0]) ? \$result[0] : '';
		\$content = isset(\$result[1]) ? \$result[1] : '';
		return \$content;");
		return $er;
	} 

} 





//====================================================================================
//=================================== Validated Page =================================
//====================================================================================


// This class holds all the values and scores
// from sitebrains.com analysis result in a convenient
// structure

class ValidatedPage
{
	// Page result constructor
	function __construct($Secretkey,$DomainID,$acMetadata)
	{
		$this->Init($Secretkey,$DomainID,$acMetadata);
	} 

	// Initializer
	// This initializer populates this class's properties with
	// values from the XML response from sitebrains.com
	// param Secretkey:      A GUID formed key from sitebrains.com
	// param DomainID:       The domain ID from sitebrains.com
	// param acMetadata:     The XML response from sitebrains.com  
	function Init($Secretkey,$DomainID,$acMetadata)
	{
//wp_die($acMetadata);
		// Store the raw response received from sitebrains.com  
		$this->RawResponse=$acMetadata;
		$lXMLDoc=DOMDocument::loadXML($acMetadata);

		// If the requested response format is XML, this property
		// holds the parsed xml document that can be queried using
		// xPath and other XML methods
		$this->XMLDoc=$lXMLDoc;
	
		$lXMLDoc = new DOMXPath($lXMLDoc);
		
		$o=$lXMLDoc->query("/Page/Signature");
		if ($o->length>0)
		{
			// Get the signature from the XML
			$signature=$o->item(0)->nodeValue;

			// The XML was signed before the signature was in it so
			// we remove the signature node
			$acMetadata=str_replace("<Signature>".$signature."</Signature>","",$acMetadata);
			//$acMetadata=str_replace(array("\\"),"",$acMetadata);
			$signature=str_replace("&gt;",">",str_replace("&lt;","<",$signature));
		} 
		
		$this->SignatureValid=true;
		if ($Secretkey>"" && $DomainID>"0" && $acMetadata>"")
		{
			// Indicates if the signature was verified successfully
			// If not secret key is passed, this will be true
			// If the signature cannot be verified, the Validate method 
			// will return False.  

			$this->SignatureValid=$this->CheckSignature($acMetadata,$signature,$Secretkey);
		} 

		// Load the overall page scores and values to
		// a conviniet and accesible structure
	
		$o=$lXMLDoc->query("/Page/PageValidationResult");
		if ($o->length > 0)
		{
			// The overall validation result in a string form
			// Possible values are "Valid" and "Invalid"  

			$this->ValidationResult=$o->item(0)->nodeValue;

			// Indicates whether the post is valid
			// True if all the fields are valid
			// False if any of the fields are not valid  
			
			$this->IsValid=false;
			if ($this->ValidationResult=="Valid") $this->IsValid=true;
		}

		$o=$lXMLDoc->query("/Page/PageRecommendedAction");
		if ($o->length > 0)
		{
			// The recommended action for the validated fields  
			$this->RecommendedActionText=$o->item(0)->nodeValue;
			
			// The recommended action for the validated fields
			// in a string form  

			$this->RecommendedAction=str_replace(" ","_",str_replace(", ","__",$this->RecommendedActionText));
		}
		
		$o=$lXMLDoc->query("/Page/GrammarScore");
		if ($o->length > 0)
		{
			// Returns an integer between 0 and 100 that indicates
			// the grammar quality score of the validated fields
			// that are of textarea type  

			$this->GrammarScore=$o->item(0)->nodeValue;
		} 

		$o=$lXMLDoc->query("/Page/TrustScore");
		if ($o->length > 0)
		{
			// This is the overall score of the entire post
			// It incorporates all the other scores and rules and
			// returns an integer between 0 and 100 that indicates
			// how trust worthy are the validated fields  

			$this->TrustScore=$o->item(0)->nodeValue;
		} 

		$o=$lXMLDoc->query("/Page/ExternalLinkTrustScore");
		if ($o->length > 0)
		{
			// Returns an integer between 0 an 100 that indicates
			// how trust worthy are the links found in the validated 
			// fields.
			// Returns -1 if no links were present  
			
			$this->ExternalLinkTrustScore=$o->item(0)->nodeValue;
		} 

		$o=$lXMLDoc->query("/Page/SpamProbability");
		if ($o->length > 0)
		{
			// Returns an integer between 0 and 100 that indicates
			// the spam probability of the text in the validated fields  

			$this->SpamProbability=$o->item(0)->nodeValue;
		} 

		$o=$lXMLDoc->query("/Page/CommercialIntent");
		if ($o->length > 0)
		{
			// Returns an integer between 0 and 100 that indicates
			// the estimated commercial intent of the validated fields  

			$this->CommercialIntent=$o->item(0)->nodeValue;
		} 


		// Load the scores and values of each validated field
		// This collection holds all the validated fields
		// The keys are the inputs names passed at the AddField method  
		$this->ValidatedInputs = array();
		$xmlnlInputs=$lXMLDoc->query("/Page/Inputs/Input");

		foreach ($xmlnlInputs as $xmlnInput)
		{
			$vi = New ValidatedInput($lXMLDoc,$xmlnInput);
			$this->ValidatedInputs[$vi->InputName]=$vi;
			if (	str_replace("\r","",str_replace("\n","",$vi->OriginalText)) 
				!= str_replace("\r","",str_replace("\n","",$_POST[$vi->InputName]))
				&& str_replace("\r","",str_replace("\n","",$vi->RevisedText)) 
				!= str_replace("\r","",str_replace("\n","",$_POST[$vi->InputName])))
			{
//wp_die("\r\n'".str_replace("\r","/r",str_replace("\n","/n",$vi->RevisedText))."'"."\r\n'".str_replace("\r","/r",str_replace("\n","/n",$_POST[$vi->InputName]))."'");
				$this->SignatureValid=false;
			}
		}
		
		if ($this->ValidatedInputs["comment"]==null)
		{
			$this->SignatureValid=false;
		}

		// override report recommendations if signature is not valid
		if (!$this->SignatureValid)
		{
			$this->RecommendedAction="Block_submission__Handle_as_spam";
			$this->RecommendedActionText="Block submission, Handle as spam";
			$this->ValidationResult="Invalid";
			$this->IsValid=false;
		}
		
	} 

	// Checks the signature of the analysis response 
	// and verifies that it came from sitebrains.com
	// param acmetadata:         The XML response from sitebrains.com
	// param acmetadataMD5:      The MD5 signature
	// param secretkey:          The secret key from sitebrains.com
	function CheckSignature($acmetadata,$acmetadataMD5,$secretkey)
	{
		$acmetadata=str_replace("\r","",str_replace("\n","",$acmetadata));
		$h=$this->getMd5Hash($acmetadata.$secretkey);	
		//echo($secretkey."<br/><textarea>".$acmetadata.$secretkey."</textarea><br/>'".$h."'<br/>'".$acmetadataMD5."'<br/>");
		if ($h==$acmetadataMD5) return true;
		return false;
	} 

	// This method computes the MD5 hash of a string
	// param str:      String to compute the MD5 hash from
	// returns the computed MD5 hash
	function getMd5Hash($str)
	{
		$function_ret=md5($str);
		return $function_ret;
	} 
} 


//====================================================================================
//=================================== Validated Input ================================
//====================================================================================


// This class holds all the values and scores of the specific field
// from sitebrains.com analysis result in a convenient
// and accessible structure

class ValidatedInput
{
	// Input result constructor
	function __construct($lXMLDoc,$inputNode)
	{
		$this->Init($lXMLDoc,$inputNode);
	} 

	// Array properties 
	var $FailedBlockingRules = array();
	var $FailedNonBlockingRules = array();
	var $AllFailedRules = array();
	function ParseFailedRulesHTML()
	{
		if ($this->FailedBlockingRules!=null)
		{
			$res="<span class='acBlock'>";
			foreach ($this->FailedBlockingRules as $info)
			{
			  $res=$res.$info."<br/>";
			  $bfound=true;
			}
			$res=$res."</span>";
		}
		if ($this->FailedNonBlockingRules !=null)
		{
			$res=$res."<span class='acInfo'>";
			foreach ($this->FailedNonBlockingRules as $info)
			{
				$bfound=true;
				$res=$res.$info."<br/>";
			}
			$res=$res."</span>";
		}
		if (!$bfound)
		{
			$res="";
		} 

		return $res;
	} 

	// Initializer
	// This initializer populates the public properties 
	// of the validated input with values from the xml response
	// from sitebrains.com
	// param inputNode:
	// The node that holds the part of the analysis result
	// that targets the validate field.  
	function Init($lXMLDoc,$inputNode)
	{	
		// Indicates whether the text of this field is valid or not  
		
		$this->IsValid=false;
		if($inputNode->getElementsByTagName("ValidationResult")->item(0)->nodeValue=="Valid") $this->IsValid=true;

		// The recommended action for the specific field  
		// in text form  

	    $this->RecommendedActionText=$inputNode->getElementsByTagName("RecommendedAction")->item(0)->nodeValue;

		// The recommended action for the specific field
		
		$this->RecommendedAction=str_replace(" ","_",str_replace(", ","__",$RecommendedActionText));

		// The name of the specific field as passed in the
		// AddField method.This name is used as the field's
		// key in the ValidatedInputs collection in the
		// ValidatedPage class  

		$this->InputName=$inputNode->getElementsByTagName("InputName")->item(0)->nodeValue;

		// The type of the specific field as passed in the
		// AddField method. possible values are: text, textarea and password  

		$this->InputType=$inputNode->getElementsByTagName("InputType")->item(0)->nodeValue;

		// The original text of field as posted by the client  

		$this->OriginalText=$inputNode->getElementsByTagName("OriginalText")->item(0)->nodeValue;
		
		// The revised text of the field after disallowed elements
		// were removed or replaced per the definitions made at
		// sitebrains.com
		// This is the value that should be published.  

		$this->RevisedText=$inputNode->getElementsByTagName("RevisedText")->item(0)->nodeValue;

		// Prepare a collection of all the notifications of all the rules
		// that were broken for this field  

		$iblocked=0; 
		$inonblocked=0;

		$xmlnlFailedRuleActions=$this->NodeXPath($lXMLDoc,$inputNode,"FailedRules/Rule/RuleActions/RuleAction");
		foreach ($xmlnlFailedRuleActions as $xmlnRuleAction)
		{
			$actionType = $xmlnRuleAction->getElementsByTagName("ActionType")->item(0)->nodeValue;
			$notification = $xmlnRuleAction->getElementsByTagName("Notification")->item(0)->nodeValue;
			
			$this->AllFailedRules[$i]=$notification;
			$i++;
			if ((strpos($actionType,"Block",1) ? strpos($actionType,"Block",1)+1 : 0))
			{
				// Prepare a collection of all the notifications of all the rules
				// that were broken for this field and that block the post  

				 $this->FailedBlockingRules[$iblocked]=$notification;
				 $iblocked++;
			} 
			else
			{
				// Prepare a collection of all the notifications of all the rules
				// that were broken for this field and that do not block the post  

				$this->FailedNonBlockingRules[$inonblocked]=$notification;
				$inonblocked++;
			}
		}

		// Prepare a string containing all the failed rules descriptions 
		// for this field, separated by "<br/>" and with the blocking 
		// rules on top.
		// The blocking notifications are with css class 'acBlock'
		// The none blocking notifications are with css class 'acInfo'
		// for easy manipulation of the UI
		// This field can be used to output the errors in each field
		// to the client.  

		$this->FailedRulesHTML=$this->ParseFailedRulesHTML();
	} 
  
	function NodeXPath($lXMLDoc,$node,$path)
	{
		$xpath = '';

		do
		{
			$position = 1 + $lXMLDoc->query('preceding-sibling::*[name()="' . $node->nodeName . '"]', $node)->length;
			$xpath    = '/' . $node->nodeName . '[' . $position . ']' . $xpath;
			$node     = $node->parentNode;
		}
		while (!$node instanceof DOMDocument);
		return $lXMLDoc->query($xpath."/".$path);
	}  
}
?>