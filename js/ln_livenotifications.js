/*-- ln_livenotifications JS Script
--------------------------------*/
function setCookie(c_name, value, exdays) {
    var exdate = new Date();
    exdate.setDate(exdate.getDate() + exdays);
    var c_value = escape(value) + ((exdays == null) ? "" : "; expires=" + exdate.toUTCString());
    document.cookie = c_name + "=" + c_value;
}

function getCookie(c_name) {
    var i, x, y, ARRcookies = document.cookie.split(";");
    for (i = 0; i < ARRcookies.length; i++) {
        x = ARRcookies[i].substr(0, ARRcookies[i].indexOf("="));
        y = ARRcookies[i].substr(ARRcookies[i].indexOf("=") + 1);
        x = x.replace(/^\s+|\s+$/g, "");
        if (x == c_name) {
            return unescape(y);
        }
    }
}

jQuery(document).ready(function(){
	ln_timer = window.setInterval(ln_checknotifications, update_interval);
	jQuery('body').prepend('<div id="ln_push"></div>');
	jQuery("#toplinks").addClass("addtopmy");
	var ul=jQuery("#userName span").html().length;
	var widt=ul*6.5+330+"px";
	jQuery(".welcomelink").css('width',widt);
	jQuery('#ln_livenotifications a.ln_botsec').click(function(){
		jQuery(this).slideToggle(100);
		jQuery('#ln_push').css('height','35px');
		jQuery('#ln_livenotifications').css('height','61px');
		jQuery('#ln_livenotifications .ln_topsec').slideToggle(200);
		jQuery("#toplinks").addClass("addtop");
		jQuery("#toplinks").removeClass("addtopmy");
		jQuery(".welcomelink").addClass("addtop1");
		jQuery(".pointmain").addClass("addtop2");
		jQuery("#userName1").addClass("addtop3");
		jQuery("#userName").addClass("addtop6");
		jQuery("#user-dropdown").addClass("addtop4");
		jQuery("#ln_livenotifications .socialdropdown2").addClass("addtop5");
		setCookie("xbarvalid","show",365);
		jQuery('#xbarvalid').val(getCookie("xbarvalid"));
		
		
	});

	jQuery('#ln_livenotifications a.ln_close').click(function(){
		
		jQuery('#ln_livenotifications .ln_topsec').slideToggle(100);
		jQuery('#ln_livenotifications a.ln_botsec').show();
		jQuery('#ln_push').css('height','0px');
		jQuery('#ln_livenotifications').css('height','0px');
		 jQuery("#toplinks").removeClass("addtop");
		 jQuery(".welcomelink").removeClass("addtop1");
		jQuery(".pointmain").removeClass("addtop2");
		jQuery("#userName1").removeClass("addtop3");
		jQuery("#userName").removeClass("addtop6");
		jQuery("#user-dropdown").removeClass("addtop4");
		jQuery("#ln_livenotifications .socialdropdown2").removeClass("addtop5");
		 setCookie("xbarvalid","hide",365);
		 jQuery('#xbarvalid').val(getCookie("xbarvalid"));
		
	});
	
	jQuery('#xbarvalid').val(getCookie("xbarvalid"));
	
	if(jQuery("#xbarvalid").val() == "hide"){
	 		
			jQuery('#ln_livenotifications .ln_topsec').css('display','none');
			jQuery('#ln_push').css('height','0px !important');
			jQuery('#ln_livenotifications').css('height','0px !important');
			jQuery('#ln_livenotifications .ln_botsec').show();
		
	 	}
	if(jQuery("#xbarvalid").val() == "show"){
			jQuery('#ln_push').css('height','35px !important');
			jQuery('#ln_livenotifications').css('height','61px');
			jQuery('#ln_livenotifications .ln_botsec').hide();
			jQuery('#ln_livenotifications .ln_topsec').show();
	 		
	 	}
		
	jQuery("#livenotifications a.popupctrl").css("background-image","none !important");
	jQuery("#livenotifications_pm a.popupctrl").css("background-image","none !important");
	jQuery("#livenotifications_friend a.popupctrl").css("background-image","none !important");
	jQuery("#livenotifications_moderation a.popupctrl").css("background-image","none !important");


	jQuery('html').click(function() {
		if(jQuery("#ln_livenotifications .ln_signin_dropdown").css("display") == "block"){
	 		jQuery("#ln_livenotifications .ln_signin_dropdown").slideUp("fast");
	 	}
		if(jQuery("#ln_livenotifications .ln_register_dropdown").css("display") == "block"){
	 		jQuery("#ln_livenotifications .ln_register_dropdown").slideUp("fast");
	 	}
				
		if(jQuery("#livenotifications_list").css("display") == "block"){
	 		jQuery("#livenotifications_list").attr("style", "display:none");
	 		jQuery("#livenotifications a").removeClass("selected");
	 	}
	 	if(jQuery("#livenotifications_list_pm").css("display") == "block"){
	 		jQuery("#livenotifications_list_pm").attr("style", "display:none");
	 		jQuery("#livenotifications_pm a").removeClass("selected");
	 	}
	 	if(jQuery("#livenotifications_list_friend").css("display") == "block"){
	 		jQuery("#livenotifications_list_friend").attr("style", "display:none");
	 		jQuery("#livenotifications_friend a").removeClass("selected");
	 	}
	 	if(jQuery("#livenotifications_list_moderation").css("display") == "block"){
	 		jQuery("#livenotifications_list_moderation").attr("style", "display:none");
	 		jQuery("#livenotifications_moderation a").removeClass("selected");
	 	}
	 	
	 	
	 });
	jQuery('#avatar_editor_wrapper').click(function() {
	 	jQuery('#avatar_editor').attr("style", "display:none");
	 	jQuery('#avatar_editor_wrapper').attr("style", "display:none");
	});
	
	jQuery('#upload_default_avatar_image_button').click(function() {
		formfield = jQuery('#upload_default_avatar_image').attr('name');
		tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
		return false;
	});
	jQuery('#upload_logo_image_button').click(function() {
		formfield = jQuery('#upload_logo_image').attr('name');
		tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
		return false;
	});
	
	jQuery('#upload_rew_image').click(function() {
		formfield = jQuery('#rew_image').attr('name');
		tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
		return false;
	});
	
	jQuery('#upload_logo_image_button1').click(function() {
		formfield = jQuery('#eventologourl').attr('name');
		tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
		return false;
	});
	jQuery('#ln_avatar_image').click(function() {
		formfield = "change_avatar";
		tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
		return false;
	});
	
	/*window.send_to_editor = function(html) {
		imgurl = jQuery('img',html).attr('src');
		if(formfield == "ln_options[logo_url]")
			jQuery('#upload_logo_image').val(imgurl);
		else if(formfield == "ln_options[ln_default_avatar]")
			jQuery('#upload_default_avatar_image').val(imgurl);
		else if(formfield == "change_avatar"){
			save_avatar(imgurl);
		}
			}*/
	tb_remove();
	ln_checknotifications();
	
	 //private message
		

	jQuery('#send-form').submit(function() {
		jQuery('<input type="hidden" name="recipient" value="' + jQuery('.as-values').val() + '" />').appendTo(jQuery(this));
	});
});
/*
jQuery(window).resize(function(){
		
			var width=window.outerWidth;
			var output;
			if(width<=590){
				output=jQuery('#cutdiv').html();
				if(output!=''){
				document.getElementById('cutdiv').innerHTML="";
				jQuery('#pastediv').html(output);	
				jQuery('#mysearch').css('top','65px');
				jQuery('#mysearch1').css('top','65px');
			}
			}
			if(width>=590)
			{
			output=jQuery('#pastediv').html();
				if(output!=''){
				document.getElementById('pastediv').innerHTML="";
				jQuery('#cutdiv').html(output);	
				jQuery('#mysearch').css('top','36px');
				jQuery('#mysearch1').css('top','36px');
			}
				}
	
	
	});
/*jQuery(window).resize(function(){
		
			var width=window.outerWidth;
			if(width>=645){
				jQuery(".ln_topsec").css('height','36px');
				jQuery(".toplinks").css('display','block');
				jQuery(".socialdropdown").css('margin-top','-25px');
				jQuery(".socialdropdown").css('display','block');
				jQuery(".socialdropdown1").css('display','block');
				
				}
			if(width<645)
			{
					if(jQuery('.socialButtonMenu').hasClass('active'))
					{
					jQuery('.socialButtonMenu').removeClass('active');
						jQuery(".socialdropdown").css('display','none');
						jQuery(".toplinks").css('display','none');
						jQuery(".ln_topsec").css('height','36px');
						
						
					}
					else{jQuery(".socialdropdown").css('display','none');
					jQuery(".socialdropdown").css('margin-top','12px');
					jQuery(".toplinks").css('display','none');
					
					
					}	
					
					if(jQuery('.socialButtonMenu1').hasClass('active'))
					{
						jQuery('.socialButtonMenu1').removeClass('active');
						jQuery(".socialdropdown1").css('display','none');
						jQuery(".toplinks").css('display','none');
						jQuery(".ln_topsec").css('height','36px');
						}
					else{
			jQuery(".socialdropdown1").css('display','none');
					jQuery(".socialdropdown1").css('margin-top','12px');
					jQuery(".toplinks").css('display','none');
					
					
					}	
				
				}
				
				if(width>=450){
				jQuery("#searchWeb").css('display','none');	
					}
		
		});
	jQuery(".socialButtonMenu").click(function(){

		if(jQuery(".socialButtonMenu").hasClass('active'))
		{
			
				if(jQuery(window).outerWidth()<=590)
				{ 	
			jQuery(".toplinks").css('display','none');
				jQuery(".ln_topsec").css('height','36px');	
				jQuery(".socialdropdown").css('display','none');
				jQuery(".socialdropdown").css('margin-top','-7px');
				jQuery(".socialButtonMenu").removeClass('active');
				}
				
				if(jQuery(window).outerWidth()>=590 || jQuery(window).outerWidth()<=645){
		
			jQuery(".ln_topsec").css('height','36px');
			jQuery(".socialdropdown").css('display','none');
			jQuery(".socialdropdown").css('margin-top','-7px');
			jQuery(".socialButtonMenu").removeClass('active');					}
				
		}else{
			if(jQuery(window).outerWidth()<=590){
			jQuery(".toplinks").css('display','block');
			jQuery(".ln_topsec").css('height','80px');
			jQuery(".socialdropdown").css('display','block');
			jQuery(".socialdropdown").css('margin-top','12px');
			jQuery(".socialButtonMenu").addClass('active');}
			
			if(jQuery(window).outerWidth()>=590 || jQuery(window).outerWidth()<=645){
			
			jQuery(".ln_topsec").css('height','80px');
			jQuery(".socialdropdown").css('display','block');
			jQuery(".socialdropdown").css('margin-top','12px');
			jQuery(".socialButtonMenu").addClass('active');}
					}
						
	});

	jQuery(".socialButtonMenu1").click(function(){

		if(jQuery(".socialButtonMenu1").hasClass('active'))
		{
			
				if(jQuery(window).outerWidth()<=590)
				{ 	
				jQuery(".ln_topsec").css('height','36px');	
				jQuery(".socialdropdown1").css('display','none');
				jQuery(".socialdropdown1").css('margin-top','-7px');
				jQuery(".socialButtonMenu1").removeClass('active');
				
				}
				
				if(jQuery(window).outerWidth()>=590 || jQuery(window).outerWidth()<=645){
			
			jQuery(".ln_topsec").css('height','36px');
			jQuery(".socialdropdown1").css('display','none');
			jQuery(".socialdropdown1").css('margin-top','-7px');
			jQuery(".socialButtonMenu1").removeClass('active');
					}
				
		}else{
			if(jQuery(window).outerWidth()<=590){
			jQuery(".ln_topsec").css('height','80px');
			jQuery(".socialdropdown1").css('display','block');
			jQuery(".socialdropdown1").css('margin-top','12px');
			jQuery(".socialButtonMenu1").addClass('active');}
			
			if(jQuery(window).outerWidth()>=590 || jQuery(window).outerWidth()<=645){
			
			jQuery(".ln_topsec").css('height','80px');
			jQuery(".socialdropdown1").css('display','block');
			jQuery(".socialdropdown1").css('margin-top','12px');
			jQuery(".socialButtonMenu1").addClass('active');}
					}
						
	});*/

	
/**       add by nikhil start *****/
 	
	jQuery("#signIn").click(function(){
		if(document.getElementById('menuOrder').style.display=="block")
		{	
			jQuery('#menuOrder').css('display','none');
			jQuery('#SearchTop').css('background','none');
			jQuery('#userName span').css('background','none');
			jQuery('#userName span').css('border-radius','none');
			jQuery('#userName span').css('padding','none');
			jQuery('#userName span').css('margin-top','none');
			jQuery('#twiter').css('background','none');
			jQuery('#twiter').css('border-radius','none');
		}
		if(document.getElementById('mysearch1').style.display=='block')
		{
		document.getElementById('mysearch1').style.display='none';
		}
		if(document.getElementById('mysearch').style.display=='block')
		{
		document.getElementById('mysearch').style.display='none';
		}
		
		 if(jQuery('.SignUpBox').hasClass('active'))
			{
					
			 	jQuery('.SignUpBox').removeClass("active");
				
		 		jQuery(".SignUpBox").slideUp('fast','linear');
				//jQuery(".SignUpBox").css('display','block');sssssssss
	  		}else{
				jQuery('.SignUpBox').addClass("active");
		 		jQuery(".SignUpBox").slideDown('fast','linear');
				
				}
		 });
		 
	/*jQuery("#signIn").mouseover(function(){
		if(document.getElementById('mysearch1').style.display=='block')
		{
		document.getElementById('mysearch1').style.display='none';
		}
		if(document.getElementById('mysearch').style.display=='block')
		{
		document.getElementById('mysearch').style.display='none';
		}
		
		 
				jQuery('.SignUpBox').addClass("active");
		 		jQuery(".SignUpBox").slideDown('fast');
				
				
		 });	 
	
*/
		 	 
		 
	jQuery(".signIn").click(function(){
		if(document.getElementById('mysearch1').style.display=='block')
		{
		document.getElementById('mysearch1').style.display='none';
		}
		if(document.getElementById('mysearch').style.display=='block')
		{
		document.getElementById('mysearch').style.display='none';
		}
		
		 if(jQuery('.SignUpBox').hasClass('active'))
			{
				jQuery('.SignUpBox').removeClass("active");
				
		 		jQuery(".SignUpBox").slideUp('fast');
				//jQuery(".SignUpBox").css('display','block');sssssssss
	  		}else{
				jQuery('.SignUpBox').addClass("active");
		 		jQuery(".SignUpBox").slideDown('fast');
				
				}
		 });	 
		 /*************end script********/
function searchOpen(){

	if(document.getElementById('searchWeb').style.display=='none')
	{
		document.getElementById('searchWeb').style.display='block';
		}
		else{
			document.getElementById('searchWeb').style.display='none';
			}
	}
function add_more(a)
{
	jQuery('#upload_logo_image_button'+a).click(function() {
		formfield = jQuery('#upload_logo_image').attr('name');
		tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
		return false;
	});
}
function ln_stop_propagation(e){
	if(e != null  ){
		
		if ('bubbles' in e) {   // all browsers except IE before version 9
            if (e.bubbles) {
                e.stopPropagation ();
            }
            
        }
        else {  // Internet Explorer before version 9
                // always cancel bubbling
            e.cancelBubble = true;
        }
	}
}
function ln_show_signin(e){
	ln_stop_propagation(e);
	if(jQuery("#ln_livenotifications .ln_signin_dropdown").css("display") == "block"){
		jQuery('#ln_livenotifications .ln_signin_dropdown').slideUp('fast');
	}
	else{
		jQuery('#ln_livenotifications .ln_signin_dropdown').slideDown('slow');
	}
}
function ln_show_register(e){
	ln_stop_propagation(e);
	if(jQuery("#ln_livenotifications .ln_register_dropdown").css("display") == "block"){
		jQuery('#ln_livenotifications .ln_register_dropdown').slideUp('fast');
	}
	else{
		jQuery('#ln_livenotifications .ln_register_dropdown').slideDown('slow');
	}
}

var xmlHttp = null;
var ln_transferids = '';

function ln_checknotifications() {
	opentype = "";
	if(document.getElementById("livenotifications_list") != null && document.getElementById("livenotifications_list").style.display == "block"){
		opentype = "comment";
	}
	else if(document.getElementById("livenotifications_list_pm") != null && document.getElementById("livenotifications_list_pm").style.display == "block"){
		opentype = "pm";
	}
	else if(document.getElementById("livenotifications_list_friend") != null && document.getElementById("livenotifications_list_friend").style.display == "block"){
		opentype = "friend";
	}
	else if(document.getElementById("livenotifications_list_moderation") != null && document.getElementById("livenotifications_list_moderation").style.display == "block"){
		opentype = "moderation";
	}
	if(opentype != "") return;

	jQuery.ajax({
        type: 'POST',
        url: base_url + '/wp-admin/admin-ajax.php',
        data: {
        	action:'ln_ajax_process',
            do: 'ln_getcount',
            numonly: 0
        },
        success: function(data, textStatus, XMLHttpRequest){
        	ln_onsuccess_num(data);
        },
        error: function(XMLHttpRequest, textStatus, errorThrown){
//            alert(errorThrown);
        }
    });


	
}
function adminnotificarion()
{
	jQuery(window).load(function(){
	jQuery('#livenotifications_list').prepend('<li id="created_div"></li>');
	jQuery(".menu li#created_div").hide();
});
}
function ln_checknotifications_more(type, count,e) {
	
	if(e != null  ){
		
		if ('bubbles' in e) {   // all browsers except IE before version 9
            if (e.bubbles) {
                e.stopPropagation ();
            }
            
        }
        else {  // Internet Explorer before version 9
                // always cancel bubbling
            e.cancelBubble = true;
        }
	}

	jQuery.ajax({
        type: 'POST',
        url: base_url + '/wp-admin/admin-ajax.php',
        data: {
        	action:'ln_ajax_process',
            do: 'ln_getcount',
            numonly: 0,
            type: type,
            count: count
        },
        success: function(data, textStatus, XMLHttpRequest){
        	ln_onsuccess_num(data);
        },
        error: function(XMLHttpRequest, textStatus, errorThrown){
//            alert(errorThrown);
        }
    });
}
function ln_onsuccess_num(raw) {
	if(raw == "logout" && ln_timer != "") {
		window.clearInterval(ln_timer);
		return;
	}
	var num = 0;
	var num_pm = 0;
	var num_friend = 0;
	var num_moderation = 0;
	if (raw.indexOf("|") > -1) {
		var num_array = raw.split("|");
		num = num_array['0'];
		num_pm = num_array['1'];
		num_friend = num_array['2'];
		num_moderation = num_array['3'];
		document.getElementById("livenotifications_list").innerHTML = num_array['4'];
		document.getElementById("livenotifications_list_pm").innerHTML = num_array['5'];
		document.getElementById("livenotifications_list_friend").innerHTML = num_array['6'];
		document.getElementById("livenotifications_list_moderation").innerHTML = num_array['7'];
					
		document.getElementById("livenotifications_num").innerHTML = num;
		document.getElementById("livenotifications_num").style.visibility = num > 0 ? "visible" : "hidden";
		document.getElementById("livenotifications_num_pm").innerHTML = num_pm;
		document.getElementById("livenotifications_num_pm").style.visibility = num_pm > 0 ? "visible" : "hidden";
		document.getElementById("livenotifications_num_friend").innerHTML = num_friend;
		document.getElementById("livenotifications_num_friend").style.visibility = num_friend > 0 ? "visible" : "hidden";
		document.getElementById("livenotifications_num_moderation").innerHTML = num_moderation;
		document.getElementById("livenotifications_num_moderation").style.visibility = num_moderation > 0 ? "visible" : "hidden";
		
		if(jQuery(".ln_scrollpane").length > 0) jQuery(".ln_scrollpane").mCustomScrollbar();
	} 
	else{
		document.getElementById("livenotifications_list").innerHTML = '<li class="livenotifications_loading"></li>';
		document.getElementById("livenotifications_list_pm").innerHTML = '<li class="livenotifications_loading"></li>';
		document.getElementById("livenotifications_list_friend").innerHTML = '<li class="livenotifications_loading"></li>';
		document.getElementById("livenotifications_list_moderation").innerHTML = '<li class="livenotifications_loading"></li>';
	}


}
function ln_onsuccess_num(raw) {
	
	if(raw == "logout" && ln_timer != "") {
		window.clearInterval(ln_timer);
		return;
	}
	var pluginURL=document.getElementById("pluginURL").value;
	var num = 0;
	var num_pm = 0;
	var num_friend = 0;
	var num_moderation = 0;
	if (raw.indexOf("|") > -1) {
		var num_array = raw.split("|");
		num = num_array['0'];
		num_pm = num_array['1'];
		num_friend = num_array['2'];
		num_moderation = num_array['3'];
		
		document.getElementById("livenotifications_list").innerHTML = num_array['4'];
		document.getElementById("livenotifications_list_pm").innerHTML = num_array['5'];
		document.getElementById("livenotifications_list_friend").innerHTML = num_array['6'];
		document.getElementById("livenotifications_list_moderation").innerHTML = num_array['7'];
					
		document.getElementById("livenotifications_num").innerHTML = num;
	if(num>0 && document.getElementById("login_check").value==1 &&  document.getElementById("login_valid").value=='enable')
	{
			
		document.getElementById("notification_box").style.display="block";							

			document.getElementById("close1").style.display="block";

	
	}
	
	
		document.getElementById("livenotifications_num").style.visibility = num > 0 ? "visible" : "hidden";
		document.getElementById("livenotifications_num_pm").innerHTML = num_pm;
		document.getElementById("livenotifications_num_pm").style.visibility = num_pm > 0 ? "visible" : "hidden";
		document.getElementById("livenotifications_num_friend").innerHTML = num_friend;
		document.getElementById("livenotifications_num_friend").style.visibility = num_friend > 0 ? "visible" : "hidden";
		document.getElementById("livenotifications_num_moderation").innerHTML = num_moderation;
		document.getElementById("livenotifications_num_moderation").style.visibility = num_moderation > 0 ? "visible" : "hidden";
		if(document.getElementById("livenotifications_num").style.visibility=="visible")
		{	
			jQuery("#livenotifications a img").attr('src',pluginURL+"/images/world1.png");
		}else{jQuery("#livenotifications a img").attr('src',pluginURL+"/images/world.png");}
		
		
		if(document.getElementById("livenotifications_num_pm").style.visibility=="visible")
		{	
			jQuery("#livenotifications_pm a img").attr('src',pluginURL+"/images/message_notification1.png");
		}else{jQuery("#livenotifications_pm a img").attr('src',pluginURL+"/images/message_notification.png");}
		
		if(document.getElementById("livenotifications_num_friend").style.visibility=="visible")
		{	
			jQuery("#livenotifications_friend a img").attr('src',pluginURL+"/images/friend_notification1.png");
		}else{jQuery("#livenotifications_friend a img").attr('src',pluginURL+"/images/friend_notification.png");}
		
		if(document.getElementById("livenotifications_num_moderation").style.visibility=="visible")
		{	
			jQuery("#livenotifications_moderation a img").attr('src',pluginURL+"/images/moderation_notification1.png");
		}else{jQuery("#livenotifications_moderation a img").attr('src',pluginURL+"/images/moderation_notification.png");}
		
		if(jQuery(".ln_scrollpane").length > 0) jQuery(".ln_scrollpane").mCustomScrollbar();
	} 
	else{
		document.getElementById("livenotifications_list").innerHTML = '<li class="livenotifications_loading"></li>';
		document.getElementById("livenotifications_list_pm").innerHTML = '<li class="livenotifications_loading"></li>';
		document.getElementById("livenotifications_list_friend").innerHTML = '<li class="livenotifications_loading"></li>';
		document.getElementById("livenotifications_list_moderation").innerHTML = '<li class="livenotifications_loading"></li>';
	}


}

function ln_fetchnotifications(type,e) {
	  jQuery('#SearchTop').css('background','none');
	jQuery('#userName span').css('background','none');
	jQuery('#userName span').css('padding','16px 0px');
	jQuery('#userName span').css('margin-top','-9px');
	jQuery('#twiter').css('background','none');
	
	
	if(e != null  ){
		
		if ('bubbles' in e) {   // all browsers except IE before version 9
                    if (e.bubbles) {
                        e.stopPropagation ();
                    }
                    
                }
                else {  // Internet Explorer before version 9
                        // always cancel bubbling
                    e.cancelBubble = true;
                }
	}
	
	if(document.getElementById("livenotifications_list").style.display == "block"){
		document.getElementById("livenotifications_list").style.display = "none";
		if(type == 'comment'){
			jQuery("#livenotifications a").removeClass("selected");
			return;
		}
	}
	if(document.getElementById("livenotifications_list_pm").style.display == "block"){
		document.getElementById("livenotifications_list_pm").style.display = "none";
		if(type == 'pm'){
			jQuery("#livenotifications_pm a").removeClass("selected");
			return;
		}
	}
	if(document.getElementById("livenotifications_list_friend").style.display == "block"){
		document.getElementById("livenotifications_list_friend").style.display = "none";
		if(type == 'friend'){
			jQuery("#livenotifications_friend a").removeClass("selected");
			return;
		}
	}
	
	if(document.getElementById('menuOrder').style.display=="block")
		{
			jQuery('#menuOrder').css('display','none');
		}

	if(document.getElementById('mysearch').style.display=="block")
		{
			jQuery('#mysearch').css('display','none');
		}
		
if(document.getElementById('mysearch1').style.display=="block")
		{
			document.getElementById('mysearch1').style.display="none";
		}
	if(document.getElementById("livenotifications_list_moderation").style.display == "block"){
		document.getElementById("livenotifications_list_moderation").style.display = "none";
		if(type == 'moderation'){
			jQuery("#livenotifications_moderation a").removeClass("selected");
			return;
		}
	}
	if(jQuery("#user-dropdown").length > 0){
		if(document.getElementById("user-dropdown").style.display == "block"){
			document.getElementById("user-dropdown").style.display = "none";
		}
	}
	if(type == "comment"){
		document.getElementById("livenotifications_list").style.display = "block";
		jQuery("#livenotifications a").addClass("selected");
		jQuery("#livenotifications_pm a").removeClass("selected");
		jQuery("#livenotifications_friend a").removeClass("selected");
		jQuery("#livenotifications_moderation a").removeClass("selected");
	}
	else if(type == "pm"){
		document.getElementById("livenotifications_list_pm").style.display = "block";
		jQuery("#livenotifications_pm a").addClass("selected");
		jQuery("#livenotifications a").removeClass("selected");
		jQuery("#livenotifications_friend a").removeClass("selected");
		jQuery("#livenotifications_moderation a").removeClass("selected");
	}
	else if(type == "friend"){
		document.getElementById("livenotifications_list_friend").style.display = "block";
		jQuery("#livenotifications_friend a").addClass("selected");
		jQuery("#livenotifications_pm a").removeClass("selected");
		jQuery("#livenotifications a").removeClass("selected");
		jQuery("#livenotifications_moderation a").removeClass("selected");
	}
	else if(type == "moderation"){
		document.getElementById("livenotifications_list_moderation").style.display = "block";
		jQuery("#livenotifications_moderation a").addClass("selected");
		jQuery("#livenotifications_pm a").removeClass("selected");
		jQuery("#livenotifications a").removeClass("selected");
		jQuery("#livenotifications_friend a").removeClass("selected");
	}

	jQuery.ajax({
        type: 'POST',
        url: base_url + '/wp-admin/admin-ajax.php',
        data: {
        	action:'ln_ajax_process',
            do: 'ln_getcount',
            numonly: 1,
            type: type
        },
        success: function(data, textStatus, XMLHttpRequest){
        	if (data.indexOf("|") > -1) {
    			var num = data.substring(0,data.indexOf("|"));
    			var type = data.substring(data.indexOf("|")+1);
    			if(type == "comment"){
    				document.getElementById("livenotifications_num").innerHTML = num;
    				document.getElementById("livenotifications_num").style.visibility = "hidden";
    			}
    			else if(type == "pm"){
    				document.getElementById("livenotifications_num_pm").innerHTML = num;
    				document.getElementById("livenotifications_num_pm").style.visibility = "hidden";
    			}
    			else if(type == "friend"){
    				document.getElementById("livenotifications_num_friend").innerHTML = num;
    				document.getElementById("livenotifications_num_friend").style.visibility = "hidden";
    			}
    			else if(type == "moderation"){
    				document.getElementById("livenotifications_num_moderation").innerHTML = num;
    				document.getElementById("livenotifications_num_moderation").style.visibility = "hidden";
    			}
    		}
        },
        error: function(XMLHttpRequest, textStatus, errorThrown){
//            alert(errorThrown);
        }
    });

}

function ln_friend_actions(act,userid_subj,e) {
	if(e != null  ){
		
		if ('bubbles' in e) {   // all browsers except IE before version 9
                    if (e.bubbles) {
                        e.stopPropagation ();
                    }
                    
                }
                else {  // Internet Explorer before version 9
                        // always cancel bubbling
                    e.cancelBubble = true;
                }
	}
	//document.getElementById("livenotifications_list_friend").innerHTML = "<li class=\"livenotifications_loading\">&nbsp;</li>";
	var action = "reject";
	if(act) action = "accept";
    
	jQuery.ajax({
        type: 'POST',
        url: base_url + '/wp-admin/admin-ajax.php',
        data: {
        	action:'ln_ajax_process',
            do: 'ln_getcount',
            numonly: 1,
            type: 'friend',
            act: action,
            userid_subj: userid_subj
        },
        success: function(data, textStatus, XMLHttpRequest){
        	ln_onsuccess_friend(data);
        },
        error: function(XMLHttpRequest, textStatus, errorThrown){
//            alert(errorThrown);
        }
    });
	
}
function ln_onsuccess_friend(raw) {
	if (raw.indexOf("|") > -1) {
		var num = raw.substring(0,raw.indexOf("|"));
		var tmp = raw.substring(raw.indexOf("|")+1);
		var ids = tmp.substring(0,tmp.indexOf("|"));
		var txt = tmp.substring(tmp.indexOf("|")+1);
		ln_transferids = escape(ids);
		document.getElementById("livenotifications_num_friend").innerHTML = num;
		document.getElementById("livenotifications_list_friend").innerHTML = txt;
	} else {
		var num = raw;
		document.getElementById("livenotifications_num_friend").innerHTML = num;
	}
	document.getElementById("livenotifications_num_friend").style.visibility = "hidden";
}
function ln_pm_delete_action(pm_id,e) {
	if(e != null  ){
		
		if ('bubbles' in e) {   // all browsers except IE before version 9
                    if (e.bubbles) {
                        e.stopPropagation ();
                    }
                    
                }
                else {  // Internet Explorer before version 9
                        // always cancel bubbling
                    e.cancelBubble = true;
                }
	}
	if(!confirm("Are you sure you want to delete this private message?")) return;
	 jQuery.ajax({
   type: 'POST',
        url: base_url + '/wp-admin/admin-ajax.php',
        data: {
        	action:'ln_ajax_process',
            do: 'ln_getcount',
            numonly: 1,
            type: 'pm',
            act: 'pm_delete',
            pm_id: pm_id
        },
        success: function(data, textStatus, XMLHttpRequest){
        	ln_onsuccess_pm_delete(data);
        },
        error: function(XMLHttpRequest, textStatus, errorThrown){
//            alert(errorThrown);
        }
    });
}
function ln_onsuccess_pm_delete(raw) {
	
	if (raw.indexOf("|") > -1) {
		var num = raw.substring(0,raw.indexOf("|"));
		var tmp = raw.substring(raw.indexOf("|")+1);
		var ids = tmp.substring(0,tmp.indexOf("|"));
		var txt = tmp.substring(tmp.indexOf("|")+1);
		
		ln_transferids = escape(ids);
		document.getElementById("livenotifications_num_pm").innerHTML = num;
		document.getElementById("livenotifications_list_pm").innerHTML = txt;
	} else {
		var num = raw;
		document.getElementById("livenotifications_num_pm").innerHTML = num;
	}
	document.getElementById("livenotifications_num_pm").style.visibility = "hidden";
}
function ln_show_pm_other(pm_id,e){
	if(e != null  ){
		if ('bubbles' in e) { 
            if (e.bubbles) {
                e.stopPropagation ();
            }
        }
        else {
            e.cancelBubble = true;
        }
	}
	jQuery("#livenotifications_list_pm li.ln_title").attr("style","display:none;");
	jQuery("#livenotifications_list_pm li.livenotifications_link").attr("style","display:none;");
	jQuery("#livenotifications_list_pm li .lnpmbit.livenotificationbit").attr("style","display:none;");
	jQuery("#livenotifications_list_pm li.livenotifications_more").attr("style","display:none !important;");
	jQuery(".ln_scrollpane").attr("style","");
	
	jQuery("#ln_pm_inner_window_"+pm_id).slideDown();
}
function ln_back_to_messages(pm_id,scrollpane_height){
	jQuery("#livenotifications_list_pm li.ln_title").attr("style","display:block;");
	jQuery("#livenotifications_list_pm li.livenotifications_link").attr("style","display:block;");
	jQuery("#livenotifications_list_pm li .lnpmbit.livenotificationbit").attr("style","display:block;");
	jQuery("#livenotifications_list_pm li.livenotifications_more").attr("style","");
	if(scrollpane_height > 0) jQuery("#livenotifications_list_pm .ln_scrollpane").attr("style","height:" + scrollpane_height + "px;");
	
	jQuery("li.ln_pm_inner_window").attr("style","display:none;");
}
function ln_pm_innerwindow_click(e){
	if(e != null  ){
		if ('bubbles' in e) { 
            if (e.bubbles) {
                e.stopPropagation ();
            }
        }
        else {
            e.cancelBubble = true;
        }
	}
}

function ln_pm_reply_action(pm_id,scrollpane_height) {
	jQuery.ajax({
        type: 'POST',
        url: base_url + '/wp-admin/admin-ajax.php',
        data: {
        	action:'ln_ajax_process',
            a: 'pr',
            i: pm_id,
            t: jQuery("#reply_"+pm_id).val(),
            h: scrollpane_height
        },
        success: function(data, textStatus, XMLHttpRequest){
        	var arr = data.split(",");
    		ln_back_to_messages(arr['0'],arr['1']);
        },
        error: function(XMLHttpRequest, textStatus, errorThrown){
//            alert(errorThrown);
        }
    });
}
//jayesh intgration//
jQuery("#page").click(function() {
    jQuery('#SearchTop').css('background','none');
	jQuery('#userName span').css('background','none');
	jQuery('#userName span').css('padding','16px 0px');
	jQuery('#userName span').css('margin-top','-9px');
	jQuery('#twiter').css('background','none');
	jQuery('#user-dropdown').hide();	
});

jQuery('#close1').click(function(){
		jQuery("#notification_box").hide();
	});

setTimeout(function() {
    jQuery('#notification_box').hide();
}, 5000); 

jQuery("#emails").click(function(){
		 if(jQuery('#email_form').hasClass('active'))
			{
			
			 	jQuery('#email_form').removeClass("active");
		 		jQuery("#email_form").fadeOut();
	  		}else{
				jQuery('#email_form').addClass("active");
		 		jQuery("#email_form").fadeIn();
				}
		 });
jQuery("#mysearch1Menu #emails").click(function(){
if(jQuery('#mysearch1Menu #email_form').hasClass('active'))
{

	jQuery('#mysearch1Menu #email_form').removeClass("active");
	jQuery("#mysearch1Menu #email_form").fadeOut();
}else{
	jQuery('#mysearch1Menu #email_form').addClass("active");
	jQuery("#mysearch1Menu #email_form").fadeIn();
	}
});
		 
//jayesh intgration//
function ln_clickuser(e){
	//jayesh intgration//
	/*jQuery('#SearchTop').css('background','none');
	jQuery('#userName span').css('background','#DDF0F9');
	jQuery('#userName span').css('border-radius','3px 3px 0px 0px');
	jQuery('#userName span').css('padding','16px 7px');
	jQuery('#userName span').css('margin-top','-9px');
	jQuery('#twiter').css('background','none');*/
	//jayesh intgration//
	if(e != null  ){
		if ('bubbles' in e) { 
            if (e.bubbles) {
                e.stopPropagation ();
            }
        }
        else {
            e.cancelBubble = true;
        }
	}
	jQuery('#userName span').css('background','none');
	jQuery('#userName span').css('border-radius','none');
	jQuery('#SearchTop').css('background','none');
	jQuery('#twiter').css('background','none');
	jQuery('#twiter').css('border-radius','none');
	jQuery('#userName span').css('margin-top','none');
	if(document.getElementById("livenotifications_list").style.display == "block"){
		document.getElementById("livenotifications_list").style.display = "none";
		jQuery("#livenotifications a").removeClass("selected");
	}
	if(document.getElementById("livenotifications_list_pm").style.display == "block"){
		document.getElementById("livenotifications_list_pm").style.display = "none";
		jQuery("#livenotifications_pm a").removeClass("selected");
	}
	if(document.getElementById("livenotifications_list_friend").style.display == "block"){
		document.getElementById("livenotifications_list_friend").style.display = "none";
		jQuery("#livenotifications_friend a").removeClass("selected");
	}
	if(document.getElementById("livenotifications_list_moderation").style.display == "block"){
		document.getElementById("livenotifications_list_moderation").style.display = "none";
		jQuery("#livenotifications_moderation a").removeClass("selected");
		
	}
	
	if(document.getElementById('menuOrder').style.display=="block")
		{
			jQuery('#menuOrder').css('display','none');
			
		}
		
	if(document.getElementById('mysearch').style.display=="block")
		{
			jQuery('#mysearch').css('display','none');
			jQuery('#userName span').css('background','none');
	jQuery('#userName span').css('border-radius','none');
	jQuery('#twiter').css('background','none');
	jQuery('#twiter').css('border-radius','none');
	jQuery('#userName span').css('margin-top','none');
			
		}
	if(document.getElementById('mysearch1').style.display=="block")
		{
			document.getElementById('mysearch1').style.display="none";
			
		}
		
	if(jQuery("#user-dropdown").length > 0){
		if(document.getElementById("user-dropdown").style.display == "block"){
			jQuery("#user-dropdown").slideUp('fast','linear');
			//document.getElementById("user-dropdown").style.display = "none";
			return;
			jQuery('#userName span').css('background','none');
	jQuery('#userName span').css('border-radius','none');
	jQuery('#twiter').css('background','none');
	jQuery('#twiter').css('border-radius','none');
	jQuery('#userName span').css('margin-top','none');
			
		}
		
		/*var left = "left:" + (jQuery(".welcomelink").width() - 280) + "px;";
		jQuery("#user-dropdown").attr("style",left);*/
	jQuery('#userName span').css('background','#none');
	jQuery('#userName span').css('border-radius','3px 3px 5px 10px');
	jQuery('#userName span').css('padding','16px 0px');
	jQuery('#userName span').css('margin-top','-9px');
		jQuery("#user-dropdown").slideDown('fast','linear');
		//document.getElementById("user-dropdown").style.display = "block";
	}
}
/*jQuery( "#userName" ).mouseover(function() {
	jQuery('#userName span').css('background','none');
	jQuery('#userName span').css('border-radius','none');
	jQuery('#SearchTop').css('background','none');
	jQuery('#twiter').css('background','none');
	jQuery('#twiter').css('border-radius','none');
	jQuery('#userName span').css('margin-top','none');
	if(document.getElementById("livenotifications_list").style.display == "block"){
		document.getElementById("livenotifications_list").style.display = "none";
		jQuery("#livenotifications a").removeClass("selected");
	}
	if(document.getElementById("livenotifications_list_pm").style.display == "block"){
		document.getElementById("livenotifications_list_pm").style.display = "none";
		jQuery("#livenotifications_pm a").removeClass("selected");
	}
	if(document.getElementById("livenotifications_list_friend").style.display == "block"){
		document.getElementById("livenotifications_list_friend").style.display = "none";
		jQuery("#livenotifications_friend a").removeClass("selected");
	}
	if(document.getElementById("livenotifications_list_moderation").style.display == "block"){
		document.getElementById("livenotifications_list_moderation").style.display = "none";
		jQuery("#livenotifications_moderation a").removeClass("selected");
		
	}
	
	if(document.getElementById('menuOrder').style.display=="block")
		{
			jQuery('#menuOrder').css('display','none');
			
		}
		
	if(document.getElementById('mysearch').style.display=="block")
		{
			jQuery('#mysearch').css('display','none');
			jQuery('#userName span').css('background','none');
	jQuery('#userName span').css('border-radius','none');
	jQuery('#twiter').css('background','none');
	jQuery('#twiter').css('border-radius','none');
	jQuery('#userName span').css('margin-top','none');
			
		}
	if(document.getElementById('mysearch1').style.display=="block")
		{
			document.getElementById('mysearch1').style.display="none";
			
		}
		
	if(jQuery("#user-dropdown").length > 0){
	
	jQuery('#userName span').css('background','#none');
	jQuery('#userName span').css('border-radius','3px 3px 0px 0px');
	jQuery('#userName span').css('padding','16px 0px');
	jQuery('#userName span').css('margin-top','-9px');
		document.getElementById("user-dropdown").style.display = "block";
	}
});
jQuery( "#userName1" ).mouseover(function() {
	jQuery('#userName span').css('background','none');
	jQuery('#userName span').css('border-radius','none');
	jQuery('#SearchTop').css('background','none');
	jQuery('#twiter').css('background','none');
	jQuery('#twiter').css('border-radius','none');
	jQuery('#userName span').css('margin-top','none');
	if(document.getElementById("livenotifications_list").style.display == "block"){
		document.getElementById("livenotifications_list").style.display = "none";
		jQuery("#livenotifications a").removeClass("selected");
	}
	if(document.getElementById("livenotifications_list_pm").style.display == "block"){
		document.getElementById("livenotifications_list_pm").style.display = "none";
		jQuery("#livenotifications_pm a").removeClass("selected");
	}
	if(document.getElementById("livenotifications_list_friend").style.display == "block"){
		document.getElementById("livenotifications_list_friend").style.display = "none";
		jQuery("#livenotifications_friend a").removeClass("selected");
	}
	if(document.getElementById("livenotifications_list_moderation").style.display == "block"){
		document.getElementById("livenotifications_list_moderation").style.display = "none";
		jQuery("#livenotifications_moderation a").removeClass("selected");
		
	}
	
	if(document.getElementById('menuOrder').style.display=="block")
		{
			jQuery('#menuOrder').css('display','none');
			
		}
		
	if(document.getElementById('mysearch').style.display=="block")
		{
			jQuery('#mysearch').css('display','none');
			jQuery('#userName span').css('background','none');
	jQuery('#userName span').css('border-radius','none');
	jQuery('#twiter').css('background','none');
	jQuery('#twiter').css('border-radius','none');
	jQuery('#userName span').css('margin-top','none');
			
		}
	if(document.getElementById('mysearch1').style.display=="block")
		{
			document.getElementById('mysearch1').style.display="none";
			
		}
		
	if(jQuery("#user-dropdown").length > 0){
	
	jQuery('#userName span').css('background','#none');
	jQuery('#userName span').css('border-radius','3px 3px 0px 0px');
	jQuery('#userName span').css('padding','16px 0px');
	jQuery('#userName span').css('margin-top','-9px');
		document.getElementById("user-dropdown").style.display = "block";
	}
});

jQuery( "#livenotifications" ).mouseover(function() {
	jQuery('#userName span').css('background','none');
	jQuery('#userName span').css('border-radius','none');
	jQuery('#SearchTop').css('background','none');
	jQuery('#twiter').css('background','none');
	jQuery('#twiter').css('border-radius','none');
	jQuery('#userName span').css('margin-top','none');
	if(document.getElementById("livenotifications_list_pm").style.display == "block"){
		document.getElementById("livenotifications_list_pm").style.display = "none";
		jQuery("#livenotifications_pm a").removeClass("selected");
	}
	if(document.getElementById("livenotifications_list_friend").style.display == "block"){
		document.getElementById("livenotifications_list_friend").style.display = "none";
		jQuery("#livenotifications_friend a").removeClass("selected");
	}
	if(document.getElementById("livenotifications_list_moderation").style.display == "block"){
		document.getElementById("livenotifications_list_moderation").style.display = "none";
		jQuery("#livenotifications_moderation a").removeClass("selected");
		
	}
	
	if(document.getElementById('menuOrder').style.display=="block")
		{
			jQuery('#menuOrder').css('display','none');
			
		}
		
	if(document.getElementById('mysearch').style.display=="block")
		{
			jQuery('#mysearch').css('display','none');
			jQuery('#userName span').css('background','none');
	jQuery('#userName span').css('border-radius','none');
	jQuery('#twiter').css('background','none');
	jQuery('#twiter').css('border-radius','none');
	jQuery('#userName span').css('margin-top','none');
			
		}
	if(document.getElementById('mysearch1').style.display=="block")
		{
			document.getElementById('mysearch1').style.display="none";
			
		}
		
	if(jQuery("#user-dropdown").length > 0){
	
	if(document.getElementById("user-dropdown").style.display == "block"){
			document.getElementById("user-dropdown").style.display = "none";
			return;
			jQuery('#userName span').css('background','none');
	jQuery('#userName span').css('border-radius','none');
	jQuery('#twiter').css('background','none');
	jQuery('#twiter').css('border-radius','none');
	jQuery('#userName span').css('margin-top','none');
			
		}
		
	}
		document.getElementById("livenotifications_list").style.display = "block";
		jQuery("#livenotifications a").addClass("selected");
	
});

jQuery( "#livenotifications_pm" ).mouseover(function() {
	jQuery('#userName span').css('background','none');
	jQuery('#userName span').css('border-radius','none');
	jQuery('#SearchTop').css('background','none');
	jQuery('#twiter').css('background','none');
	jQuery('#twiter').css('border-radius','none');
	jQuery('#userName span').css('margin-top','none');
	if(document.getElementById("livenotifications_list").style.display == "block"){
		document.getElementById("livenotifications_list").style.display = "none";
		jQuery("#livenotifications a").removeClass("selected");
	}
	
	if(document.getElementById("livenotifications_list_friend").style.display == "block"){
		document.getElementById("livenotifications_list_friend").style.display = "none";
		jQuery("#livenotifications_friend a").removeClass("selected");
	}
	if(document.getElementById("livenotifications_list_moderation").style.display == "block"){
		document.getElementById("livenotifications_list_moderation").style.display = "none";
		jQuery("#livenotifications_moderation a").removeClass("selected");
		
	}
	
	if(document.getElementById('menuOrder').style.display=="block")
		{
			jQuery('#menuOrder').css('display','none');
			
		}
		
	if(document.getElementById('mysearch').style.display=="block")
		{
			jQuery('#mysearch').css('display','none');
			jQuery('#userName span').css('background','none');
	jQuery('#userName span').css('border-radius','none');
	jQuery('#twiter').css('background','none');
	jQuery('#twiter').css('border-radius','none');
	jQuery('#userName span').css('margin-top','none');
			
		}
	if(document.getElementById('mysearch1').style.display=="block")
		{
			document.getElementById('mysearch1').style.display="none";
			
		}
		
	if(jQuery("#user-dropdown").length > 0){
	
	if(document.getElementById("user-dropdown").style.display == "block"){
			document.getElementById("user-dropdown").style.display = "none";
			return;
			jQuery('#userName span').css('background','none');
	jQuery('#userName span').css('border-radius','none');
	jQuery('#twiter').css('background','none');
	jQuery('#twiter').css('border-radius','none');
	jQuery('#userName span').css('margin-top','none');
			
		}
	
	}
	
		document.getElementById("livenotifications_list_pm").style.display = "block";
		jQuery("#livenotifications_pm a").removeClass("selected");
	
});

jQuery( "#livenotifications_friend" ).mouseover(function() {
	jQuery('#userName span').css('background','none');
	jQuery('#userName span').css('border-radius','none');
	jQuery('#SearchTop').css('background','none');
	jQuery('#twiter').css('background','none');
	jQuery('#twiter').css('border-radius','none');
	jQuery('#userName span').css('margin-top','none');
	if(document.getElementById("livenotifications_list").style.display == "block"){
		document.getElementById("livenotifications_list").style.display = "none";
		jQuery("#livenotifications a").removeClass("selected");
	}
	if(document.getElementById("livenotifications_list_pm").style.display == "block"){
		document.getElementById("livenotifications_list_pm").style.display = "none";
		jQuery("#livenotifications_pm a").removeClass("selected");
	}
	
	if(document.getElementById("livenotifications_list_moderation").style.display == "block"){
		document.getElementById("livenotifications_list_moderation").style.display = "none";
		jQuery("#livenotifications_moderation a").removeClass("selected");
		
	}
	
	if(document.getElementById('menuOrder').style.display=="block")
		{
			jQuery('#menuOrder').css('display','none');
			
		}
		
	if(document.getElementById('mysearch').style.display=="block")
		{
			jQuery('#mysearch').css('display','none');
			jQuery('#userName span').css('background','none');
	jQuery('#userName span').css('border-radius','none');
	jQuery('#twiter').css('background','none');
	jQuery('#twiter').css('border-radius','none');
	jQuery('#userName span').css('margin-top','none');
			
		}
	if(document.getElementById('mysearch1').style.display=="block")
		{
			document.getElementById('mysearch1').style.display="none";
			
		}
		
	if(jQuery("#user-dropdown").length > 0){
	
	if(document.getElementById("user-dropdown").style.display == "block"){
			document.getElementById("user-dropdown").style.display = "none";
			return;
			jQuery('#userName span').css('background','none');
	jQuery('#userName span').css('border-radius','none');
	jQuery('#twiter').css('background','none');
	jQuery('#twiter').css('border-radius','none');
	jQuery('#userName span').css('margin-top','none');
			
		}
	
	}
		document.getElementById("livenotifications_list_friend").style.display = "block";
		jQuery("#livenotifications_friend a").removeClass("selected");
	
});

jQuery( "#livenotifications_moderation" ).mouseover(function() {
	jQuery('#userName span').css('background','none');
	jQuery('#userName span').css('border-radius','none');
	jQuery('#SearchTop').css('background','none');
	jQuery('#twiter').css('background','none');
	jQuery('#twiter').css('border-radius','none');
	jQuery('#userName span').css('margin-top','none');
	if(document.getElementById("livenotifications_list").style.display == "block"){
		document.getElementById("livenotifications_list").style.display = "none";
		jQuery("#livenotifications a").removeClass("selected");
	}
	if(document.getElementById("livenotifications_list_pm").style.display == "block"){
		document.getElementById("livenotifications_list_pm").style.display = "none";
		jQuery("#livenotifications_pm a").removeClass("selected");
	}
	if(document.getElementById("livenotifications_list_friend").style.display == "block"){
		document.getElementById("livenotifications_list_friend").style.display = "none";
		jQuery("#livenotifications_friend a").removeClass("selected");
	}
	
	
	if(document.getElementById('menuOrder').style.display=="block")
		{
			jQuery('#menuOrder').css('display','none');
			
		}
		
	if(document.getElementById('mysearch').style.display=="block")
		{
			jQuery('#mysearch').css('display','none');
			jQuery('#userName span').css('background','none');
	jQuery('#userName span').css('border-radius','none');
	jQuery('#twiter').css('background','none');
	jQuery('#twiter').css('border-radius','none');
	jQuery('#userName span').css('margin-top','none');
			
		}
	if(document.getElementById('mysearch1').style.display=="block")
		{
			document.getElementById('mysearch1').style.display="none";
			
		}
		
	if(jQuery("#user-dropdown").length > 0){
	
	if(document.getElementById("user-dropdown").style.display == "block"){
			document.getElementById("user-dropdown").style.display = "none";
			return;
			jQuery('#userName span').css('background','none');
	jQuery('#userName span').css('border-radius','none');
	jQuery('#twiter').css('background','none');
	jQuery('#twiter').css('border-radius','none');
	jQuery('#userName span').css('margin-top','none');
			
		}
	
	}
	
		document.getElementById("livenotifications_list_moderation").style.display = "block";
		jQuery("#livenotifications_moderation a").removeClass("selected");
		
	
});
*/
function customeSearch()
{	if(document.getElementById('menuOrder').style.display=="block")
		{	
		jQuery('#menuOrder').css('display','none');
		jQuery('#SearchTop').css('background','none');
		jQuery('#userName span').css('background','none');
		jQuery('#userName span').css('border-radius','none');
		jQuery('#userName span').css('padding','none');
		jQuery('#userName span').css('margin-top','none');
		jQuery('#twiter').css('background','none');
		}
		if(document.getElementById("livenotifications_list").style.display == "block"){
		document.getElementById("livenotifications_list").style.display = "none";
		jQuery("#livenotifications a").removeClass("selected");
	}
	if(document.getElementById("livenotifications_list_pm").style.display == "block"){
		document.getElementById("livenotifications_list_pm").style.display = "none";
		jQuery("#livenotifications_pm a").removeClass("selected");
	}
	if(document.getElementById("livenotifications_list_friend").style.display == "block"){
		document.getElementById("livenotifications_list_friend").style.display = "none";
		jQuery("#livenotifications_friend a").removeClass("selected");
	}
	if(document.getElementById("livenotifications_list_moderation").style.display == "block"){
		document.getElementById("livenotifications_list_moderation").style.display = "none";
		jQuery("#livenotifications_moderation a").removeClass("selected");
		
	}
	if(document.getElementById('mysearch1').style.display=="block")
		{
			document.getElementById('mysearch1').style.display="none";
			jQuery('#mysearch1').slideUp('fast');
			
			jQuery('#SearchTop').css('background','none');
			jQuery('#twiter').css('background','none');
			jQuery('#userName span').css('background','none');
			jQuery('#userName span').css('border-radius','none');
			jQuery('#userName span').css('padding','none');
			jQuery('#userName span').css('margin-top','none');
		}
		
		if(jQuery("#user-dropdown").length > 0){
			if(document.getElementById("user-dropdown").style.display == "block"){
			document.getElementById("user-dropdown").style.display = "none";
			jQuery('#SearchTop').css('background','none');
			jQuery('#twiter').css('background','none');
			jQuery('#userName span').css('background','none');
			jQuery('#userName span').css('border-radius','none');
			jQuery('#userName span').css('padding','none');
			jQuery('#userName span').css('margin-top','none');
			
		}

		}
		if((document.getElementById('mysearch').style.display)=="block")
		{	
			document.getElementById('mysearch').style.display="none";
			jQuery('#mysearch').slideUp('fast');
			jQuery('#SearchTop').css('background','none');
			jQuery('#twiter').css('background','none');
			jQuery('#userName span').css('background','none');
			jQuery('#userName span').css('border-radius','none');
			jQuery('#userName span').css('padding','none');
			jQuery('#userName span').css('margin-top','none');
			}
			else{
				
				document.getElementById('mysearch').style.display="block";
				jQuery('#mysearch').slideDown('fast');
				//jQuery('#SearchTop').css('background','#DDF0F9');
				}
}
function customeSearchmenu()
{	/*jayesh intgration	
	jQuery('#SearchTop').css('border-radius','3px 3px 0px 0px');
	jQuery('#SearchTop').css('background','#DDF0F9');
	jQuery('#userName span').css('background','none');
	jQuery('#userName span').css('padding','none');
	jQuery('#twiter').css('background','none');*/
	//jayesh intgration//
	
	if(document.getElementById('menuOrder').style.display=="block")
		{	
			jQuery('#menuOrder').css('display','none');
			jQuery('#SearchTop').css('background','none');
			jQuery('#userName span').css('background','none');
	jQuery('#userName span').css('border-radius','none');
	jQuery('#userName span').css('padding','none');
	jQuery('#userName span').css('margin-top','none');
			jQuery('#twiter').css('background','none');
		}
		
	if(document.getElementById('mysearch1Menu').style.display=="block")
		{
			document.getElementById('mysearch1Menu').style.display="none";
			jQuery('#mysearch1Menu').slideUp('fast');
			
			jQuery('#SearchTop').css('background','none');
			jQuery('#twiter').css('background','none');
			jQuery('#userName span').css('background','none');
			jQuery('#userName span').css('border-radius','none');
			jQuery('#userName span').css('padding','none');
			jQuery('#userName span').css('margin-top','none');
		}
		
		if(jQuery("#user-dropdown").length > 0){
			if(document.getElementById("user-dropdown").style.display == "block"){
			document.getElementById("user-dropdown").style.display = "none";
			jQuery('#SearchTop').css('background','none');
			jQuery('#twiter').css('background','none');
			jQuery('#userName span').css('background','none');
			jQuery('#userName span').css('border-radius','none');
			jQuery('#userName span').css('padding','none');
			jQuery('#userName span').css('margin-top','none');
			
		}

		}
		if((document.getElementById('mysearchMenu').style.display)=="block")
		{	
			document.getElementById('mysearchMenu').style.display="none";
			jQuery('#mysearchMenu').slideUp('fast');
			jQuery('#SearchTop').css('background','none');
			jQuery('#twiter').css('background','none');
			jQuery('#userName span').css('background','none');
			jQuery('#userName span').css('border-radius','none');
			jQuery('#userName span').css('padding','none');
			jQuery('#userName span').css('margin-top','none');
			}
			else{
				
				document.getElementById('mysearchMenu').style.display="block";
				jQuery('#mysearchMenu').slideDown('fast');
				jQuery('#mysearchMenu').css('margin-top','25px');
				//jQuery('#SearchTop').css('background','#DDF0F9');
				}
}
function customeSearch4()
{	
	if(document.getElementById('menuOrder').style.display=="block")
		{	
			jQuery('#menuOrder').css('display','none');
			jQuery('#SearchTop').css('background','none');
			jQuery('#userName span').css('background','none');
	jQuery('#userName span').css('border-radius','none');
	jQuery('#userName span').css('padding','none');
	jQuery('#userName span').css('margin-top','none');
			jQuery('#twiter').css('background','none');
		}
	if(document.getElementById('mysearch1').style.display=="block")
		{
			document.getElementById('mysearch1').style.display="none";
			jQuery('#mysearch1').slideUp('fast');
			
			jQuery('#SearchTop').css('background','none');
			jQuery('#twiter').css('background','none');
			jQuery('#userName span').css('background','none');
			jQuery('#userName span').css('border-radius','none');
			jQuery('#userName span').css('padding','none');
			jQuery('#userName span').css('margin-top','none');
		}
		
		if(jQuery('.SignUpBox').hasClass('active'))
			{
					
			 	jQuery('.SignUpBox').removeClass("active");
				
		 		jQuery(".SignUpBox").slideUp('fast');
				//jQuery(".SignUpBox").css('display','block');sssssssss
	  		}

		
		if((document.getElementById('mysearch').style.display)=="block")
		{	
			document.getElementById('mysearch').style.display="none";
			jQuery('#mysearch').slideUp('fast');
			jQuery('#SearchTop').css('background','none');
			jQuery('#twiter').css('background','none');
			jQuery('#userName span').css('background','none');
			jQuery('#userName span').css('border-radius','none');
			jQuery('#userName span').css('padding','none');
			jQuery('#userName span').css('margin-top','none');
			}
			else{
				
				document.getElementById('mysearch').style.display="block";
				jQuery('#mysearch').slideDown('fast');
				//jQuery('#SearchTop').css('background','#DDF0F9');
				}
}
/*jQuery( "#SearchTop" ).mouseover(function() {	
	if(document.getElementById('menuOrder').style.display=="block")
		{	
			jQuery('#menuOrder').css('display','none');
			jQuery('#SearchTop').css('background','none');
			jQuery('#userName span').css('background','none');
	jQuery('#userName span').css('border-radius','none');
	jQuery('#userName span').css('padding','none');
	jQuery('#userName span').css('margin-top','none');
			jQuery('#twiter').css('background','none');
		}
		
	if(document.getElementById('mysearch1').style.display=="block")
		{
			document.getElementById('mysearch1').style.display="none";
			jQuery('#mysearch1').slideUp('fast');
			
			jQuery('#SearchTop').css('background','none');
			jQuery('#twiter').css('background','none');
			jQuery('#userName span').css('background','none');
			jQuery('#userName span').css('border-radius','none');
			jQuery('#userName span').css('padding','none');
			jQuery('#userName span').css('margin-top','none');
		}
		if(document.getElementById("livenotifications_list").style.display == "block"){
		document.getElementById("livenotifications_list").style.display = "none";
		jQuery("#livenotifications a").removeClass("selected");
	}
	if(document.getElementById("livenotifications_list_pm").style.display == "block"){
		document.getElementById("livenotifications_list_pm").style.display = "none";
		jQuery("#livenotifications_pm a").removeClass("selected");
	}
	if(document.getElementById("livenotifications_list_friend").style.display == "block"){
		document.getElementById("livenotifications_list_friend").style.display = "none";
		jQuery("#livenotifications_friend a").removeClass("selected");
	}
	if(document.getElementById("livenotifications_list_moderation").style.display == "block"){
		document.getElementById("livenotifications_list_moderation").style.display = "none";
		jQuery("#livenotifications_moderation a").removeClass("selected");
		
	}
		if(jQuery('.SignUpBox').hasClass('active'))
			{
					
			 	jQuery('.SignUpBox').removeClass("active");
				
		 		jQuery(".SignUpBox").slideUp('fast');
				//jQuery(".SignUpBox").css('display','block');sssssssss
	  		}
	if(document.getElementById('user-dropdown').style.display=="block")
	
			{
					
			 	jQuery('#user-dropdown').css('display','none');
	  		}

		
	
				
				document.getElementById('mysearch').style.display="block";
				jQuery('#mysearch').slideDown('fast');
				//jQuery('#SearchTop').css('background','#DDF0F9');
				
});*/


function eventodropdown()
{	
	if(jQuery("#ln_livenotifications .ln_signin_dropdown").css("display") == "block"){
	 		jQuery("#ln_livenotifications .ln_signin_dropdown").slideUp();
	 	}
		if(jQuery("#ln_livenotifications .ln_register_dropdown").css("display") == "block"){
	 		jQuery("#ln_livenotifications .ln_register_dropdown").slideUp();
	 	}
		if(jQuery("#livenotifications_list").css("display") == "block"){
	 		jQuery("#livenotifications_list").attr("style", "display:none");
	 		jQuery("#livenotifications a").removeClass("selected");
	 	}
	 	if(jQuery("#livenotifications_list_pm").css("display") == "block"){
	 		jQuery("#livenotifications_list_pm").attr("style", "display:none");
	 		jQuery("#livenotifications_pm a").removeClass("selected");
	 	}
	 	if(jQuery("#livenotifications_list_friend").css("display") == "block"){
	 		jQuery("#livenotifications_list_friend").attr("style", "display:none");
	 		jQuery("#livenotifications_friend a").removeClass("selected");
	 	}
	 	if(jQuery("#livenotifications_list_moderation").css("display") == "block"){
	 		jQuery("#livenotifications_list_moderation").attr("style", "display:none");
	 		jQuery("#livenotifications_moderation a").removeClass("selected");
	 	}
	 	if(jQuery("#user-dropdown").length > 0){
			if(document.getElementById("user-dropdown").style.display == "block"){
			document.getElementById("user-dropdown").style.display = "none";
			}
		}
	if(document.getElementById('mysearch').style.display=="block")
		{
			document.getElementById('mysearch').style.display="none";
		}
	
	if(document.getElementById('mysearch1').style.display=="block")
		{
			document.getElementById('mysearch1').style.display="none";
		}
	if(jQuery("#lv_socialLinks .SignUpBox").css("display") == "block"){
	 		jQuery("#lv_socialLinks .SignUpBox").slideUp();
	 	}
	if(document.getElementById('menuOrder').style.display=="block")
	{
		jQuery('#menuOrder').css('display','none');
	}
	else
	{
		jQuery('#menuOrder').css('display','block');
	}
	
}
/*jQuery( "#eventodropdown" ).mouseover(function() {
	if(jQuery("#ln_livenotifications .ln_signin_dropdown").css("display") == "block"){
	 		jQuery("#ln_livenotifications .ln_signin_dropdown").slideUp();
	 	}
		if(jQuery("#ln_livenotifications .ln_register_dropdown").css("display") == "block"){
	 		jQuery("#ln_livenotifications .ln_register_dropdown").slideUp();
	 	}
		if(jQuery("#livenotifications_list").css("display") == "block"){
	 		jQuery("#livenotifications_list").attr("style", "display:none");
	 		jQuery("#livenotifications a").removeClass("selected");
	 	}
	 	if(jQuery("#livenotifications_list_pm").css("display") == "block"){
	 		jQuery("#livenotifications_list_pm").attr("style", "display:none");
	 		jQuery("#livenotifications_pm a").removeClass("selected");
	 	}
	 	if(jQuery("#livenotifications_list_friend").css("display") == "block"){
	 		jQuery("#livenotifications_list_friend").attr("style", "display:none");
	 		jQuery("#livenotifications_friend a").removeClass("selected");
	 	}
	 	if(jQuery("#livenotifications_list_moderation").css("display") == "block"){
	 		jQuery("#livenotifications_list_moderation").attr("style", "display:none");
	 		jQuery("#livenotifications_moderation a").removeClass("selected");
	 	}
	 	if(jQuery("#user-dropdown").length > 0){
			if(document.getElementById("user-dropdown").style.display == "block"){
			document.getElementById("user-dropdown").style.display = "none";
			}
		}
	if(document.getElementById('mysearch').style.display=="block")
		{
			document.getElementById('mysearch').style.display="none";
		}
	
	if(document.getElementById('mysearch1').style.display=="block")
		{
			document.getElementById('mysearch1').style.display="none";
		}
	if(jQuery("#lv_socialLinks .SignUpBox").css("display") == "block"){
	 		jQuery("#lv_socialLinks .SignUpBox").slideUp();
	 	}
	if(document.getElementById('menuOrder').style.display=="none")
	{
		jQuery('#menuOrder').css('display','block');
	}
	
	
});*/

function customeSearch1()
{	//jayesh intgration//	
	jQuery('#SearchTop').css('background','none');
	jQuery('#userName span').css('background','none');
	jQuery('#userName span').css('padding','none');
	jQuery('#twiter').css('background','none');
	jQuery('#twiter').css('border-radius','none');
	
		if((document.getElementById('mysearch').style.display)=="block")
		{
			document.getElementById('mysearch').style.display="none";
			
			}
			if((document.getElementById('user-dropdown').style.display)=="block")
		{
			document.getElementById('user-dropdown').style.display="none";
			
			}
		
		if(document.getElementById('mysearch1').style.display=="block")
		{
			
			jQuery('#mysearch1').slideUp('fast');
			}
		else{
			
			jQuery('#mysearch1').slideDown('fast');
			}
				
}
function customeSearchsocailmenu()
{	//jayesh intgration//	
	jQuery('#SearchTop').css('background','none');
	jQuery('#userName span').css('background','none');
	jQuery('#userName span').css('padding','none');
	jQuery('#twiter').css('background','none');
	jQuery('#twiter').css('border-radius','none');
	
		if((document.getElementById('mysearchMenu').style.display)=="block")
		{
			document.getElementById('mysearchMenu').style.display="none";
			
			}
			if((document.getElementById('user-dropdown').style.display)=="block")
		{
			document.getElementById('user-dropdown').style.display="none";
			
			}
		
		if(document.getElementById('mysearch1Menu').style.display=="block")
		{
			
			jQuery('#mysearch1Menu').slideUp('fast');
			}
		else{
			
			jQuery('#mysearch1Menu').slideDown('fast');
			}
				
}
/*jQuery( "#afterloginsocial" ).mouseover(function() {	
//jayesh intgration//	
	jQuery('#SearchTop').css('background','none');
	jQuery('#userName span').css('background','none');
	jQuery('#userName span').css('padding','none');
	jQuery('#twiter').css('background','none');
	jQuery('#twiter').css('border-radius','none');
	if(jQuery("#ln_livenotifications .ln_signin_dropdown").css("display") == "block"){
	 		jQuery("#ln_livenotifications .ln_signin_dropdown").slideUp();
	 	}
		if(jQuery("#ln_livenotifications .ln_register_dropdown").css("display") == "block"){
	 		jQuery("#ln_livenotifications .ln_register_dropdown").slideUp();
	 	}
		if(jQuery("#livenotifications_list").css("display") == "block"){
	 		jQuery("#livenotifications_list").attr("style", "display:none");
	 		jQuery("#livenotifications a").removeClass("selected");
	 	}
	 	if(jQuery("#livenotifications_list_pm").css("display") == "block"){
	 		jQuery("#livenotifications_list_pm").attr("style", "display:none");
	 		jQuery("#livenotifications_pm a").removeClass("selected");
	 	}
	 	if(jQuery("#livenotifications_list_friend").css("display") == "block"){
	 		jQuery("#livenotifications_list_friend").attr("style", "display:none");
	 		jQuery("#livenotifications_friend a").removeClass("selected");
	 	}
	 	if(jQuery("#livenotifications_list_moderation").css("display") == "block"){
	 		jQuery("#livenotifications_list_moderation").attr("style", "display:none");
	 		jQuery("#livenotifications_moderation a").removeClass("selected");
	 	}
		if((document.getElementById('mysearch').style.display)=="block")
		{
			document.getElementById('mysearch').style.display="none";
			
			}
			if((document.getElementById('user-dropdown').style.display)=="block")
			{
			document.getElementById('user-dropdown').style.display="none";
			
			}
			jQuery('#mysearch1').slideDown('fast');
				
});*/


function customeSearch2()
{	
if(document.getElementById('menuOrder').style.display=="block")
		{	
			jQuery('#menuOrder').css('display','none');
			jQuery('#SearchTop').css('background','none');
			jQuery('#userName span').css('background','none');
			jQuery('#userName span').css('border-radius','none');
			jQuery('#userName span').css('padding','none');
			jQuery('#userName span').css('margin-top','none');
			jQuery('#twiter').css('background','none');
			jQuery('#twiter').css('border-radius','none');
		}
	
		
		if((document.getElementById('mysearch').style.display)=="block")
		{
			document.getElementById('mysearch').style.display="none";
			
			}
			if(jQuery('.SignUpBox').hasClass('active'))
			{
					
			 	jQuery('.SignUpBox').removeClass("active");
				
		 		jQuery(".SignUpBox").slideUp('fast');
				//jQuery(".SignUpBox").css('display','block');sssssssss
	  		}
		
		if(document.getElementById('mysearch1').style.display=="block")
		{
			
			jQuery('#mysearch1').css('display','none');
		}
		else{
			
			document.getElementById('mysearch1').style.display="block";
			}
				
}
/*jQuery( "#beforeloginsocial" ).mouseover(function() {	
	jQuery('#SearchTop').css('background','none');
	jQuery('#userName span').css('background','none');
	jQuery('#userName span').css('padding','none');
	jQuery('#twiter').css('background','none');
	jQuery('#twiter').css('border-radius','none');
	
		
		if((document.getElementById('mysearch').style.display)=="block")
		{
			document.getElementById('mysearch').style.display="none";
			
			}
			if((document.getElementById('menuOrder').style.display)=="block")
		{
			document.getElementById('menuOrder').style.display="none";
			
			}
			if(jQuery('.SignUpBox').hasClass('active'))
			{
					
			 	jQuery('.SignUpBox').removeClass("active");
				
		 		jQuery(".SignUpBox").slideUp('fast');
				//jQuery(".SignUpBox").css('display','block');sssssssss
	  		}
		
		if(document.getElementById('mysearch1').style.display=="none")
		{
			
			jQuery('#mysearch1').slideDown('fast');
		}
		
				
});
jQuery( "#loginsocial" ).mouseover(function() {	
	jQuery('#SearchTop').css('background','none');
	jQuery('#userName span').css('background','none');
	jQuery('#userName span').css('padding','none');
	jQuery('#twiter').css('background','none');
	jQuery('#twiter').css('border-radius','none');
	
		
		if((document.getElementById('mysearch').style.display)=="block")
		{
			document.getElementById('mysearch').style.display="none";
			
			}
			if(jQuery('.SignUpBox').hasClass('active'))
			{
					
			 	jQuery('.SignUpBox').removeClass("active");
				
		 		jQuery(".SignUpBox").slideUp('fast');
				//jQuery(".SignUpBox").css('display','block');sssssssss
	  		}
		
		if(document.getElementById('mysearch1').style.display=="block")
		{
			
			jQuery('#mysearch1').slideUp('fast');
		}
		else{
			
			//document.getElementById('mysearch1').style.display="block";
			jQuery('#mysearch1').slideDown('fast');
			}
				
});*/
function customeSearchcloase()
{
	document.getElementById('mysearch').style.display="none";			
}
function ln_create_userpane(){
	
	jQuery.ajax({
        type: 'POST',
        url: base_url + '/wp-admin/admin-ajax.php',
        data: {
        	action:'ln_ajax_process',
            do: 'ln_userdropdown'
        },
        success: function(data, textStatus, XMLHttpRequest){
        	document.getElementById("user-dropdown").innerHTML = data;
        },
        error: function(XMLHttpRequest, textStatus, errorThrown){
//            alert(errorThrown);
        }
    });
	
}

function ln_avatar_click(){
	formfield = "change_avatar";
	tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
}
function ln_onsuccess_avatar_form() {
    if ( xmlHttp.readyState == 4 && xmlHttp.status == 200 ) 
    {
		var raw = xmlHttp.responseText;
		document.getElementById("avatar_editor").innerHTML = raw;
		document.getElementById("avatar_editor").style.display = "block";
	}
}
function ln_show_settings(e){
	if(e != null  ){
		if ('bubbles' in e) { 
            if (e.bubbles) {
                e.stopPropagation ();
            }
        }
        else {
            e.cancelBubble = true;
        }
	}
	jQuery("#livenotifications_list li.ln_title").css("display","none");
	jQuery("#livenotifications_list li.livenotifications_more").css("display","none");
	jQuery("#livenotifications_list li.livenotifications_link").css("display","none");
	jQuery("#livenotifications_list li .livenotificationbit").css("display","none");
	jQuery(".ln_scrollpane").attr("style","");
	
	jQuery("#ln_settings_window").slideDown();
}
function ln_back_to_notification(scrollpane_height){
	jQuery("#livenotifications_list li.ln_title").css("display","block");
	jQuery("#livenotifications_list li.livenotifications_more").attr("style","");
	jQuery("#livenotifications_list li.livenotifications_link").css("display","block");
	jQuery("#livenotifications_list li .livenotificationbit").css("display","block");
	if(scrollpane_height > 0) jQuery("#livenotifications_list .ln_scrollpane").css("height", scrollpane_height + "px");

	jQuery("#ln_settings_window").attr("style","display:none;");
}

function ln_options_save_action(userid) {
	var comment=0,reply=0,award=0,friend=0,moderation=0,taguser=0,pm=0;
	if(jQuery("#ln_enable_comment").length > 0) comment = jQuery('#ln_enable_comment').is(':checked');
	if(jQuery("#ln_enable_reply").length > 0) reply = jQuery("#ln_enable_reply").is(':checked');
	if(jQuery("#ln_enable_award").length > 0) award = jQuery("#ln_enable_award").is(':checked');
	if(jQuery("#ln_enable_friend").length > 0) friend = jQuery("#ln_enable_friend").is(':checked');
	if(jQuery("#ln_enable_moderation").length > 0) moderation = jQuery("#ln_enable_moderation").is(':checked');
	if(jQuery("#ln_enable_taguser").length > 0) taguser = jQuery("#ln_enable_taguser").is(':checked');
	if(jQuery("#ln_enable_pm").length > 0) pm = jQuery("#ln_enable_pm").is(':checked');
	
	
	options = comment+","+reply+","+award+","+friend+","+moderation+","+taguser+","+pm;

    
	jQuery.ajax({
        type: 'POST',
        url: base_url + '/wp-admin/admin-ajax.php',
        data: {
        	action:'ln_ajax_process',
            do: 'ln_save_option',
            options: options
        },
        success: function(data, textStatus, XMLHttpRequest){
        	ln_back_to_notification();
        },
        error: function(XMLHttpRequest, textStatus, errorThrown){
//            alert(errorThrown);
        }
    });
}

function ln_transfer_overview(url) {
	//self.location.href = url + "&lntransf=" + ln_transferids + "#livenotifications";
	self.location.href = url ;
}
