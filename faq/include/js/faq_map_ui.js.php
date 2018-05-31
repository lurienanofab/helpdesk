<?php
/* Id: faq_xml_smap.js.php
 * Copyright (c) 2009-2010 osfaq.oz-devworx.com.au - All Rights Reserved.
 */
?>
<script language="javascript" type="text/javascript">
/* <![CDATA[ */
function show_hide_div(divid, state) {
    document.getElementById(divid).style.display = ((state == true) ? 'inline' : 'none');
}
function show_hide_help(divid, imgid) {
    if (document.getElementById(divid).style.display=='inline') {
        document.getElementById(imgid).src = '../faq/img/seo/help_white.png';
        show_hide_div(divid, false);
    } else {
        document.getElementById(imgid).src = '../faq/img/seo/help_green.png';
        show_hide_div(divid, true);
    }
}
function basic_jump_menu(objId,targ,url){
	  var selObj = null;
	  with (document) {
    	  if (getElementById) selObj = getElementById(objId);
    	  if (selObj) eval(targ+".location='"+url+"'");
	  }
}
function on_output_change(objId,targ) {
	  show_hide_div('osf_working', true);
    if (document.getElementById(objId).value == 'test') {
    	basic_jump_menu(objId, targ, '<?php echo DIR_WS_ADMIN . FILE_FAQ_ADMIN . '?map=true&smap=test'; ?>');
    } else if (document.getElementById(objId).value == 'other') {
    	basic_jump_menu(objId, targ, '<?php echo DIR_WS_ADMIN . FILE_FAQ_ADMIN . '?map=true&smap=other'; ?>');
    } else if (document.getElementById(objId).value == 'local') {
    	basic_jump_menu(objId, targ, '<?php echo DIR_WS_ADMIN . FILE_FAQ_ADMIN . '?map=true&smap=local'; ?>');
    }
}
function selectText(elementId, container) {
    if (document.getElementById(container).style.display = 'inline') {
        document.getElementById(elementId).focus();
        document.getElementById(elementId).select();
    }
}
/* ]]> */
</script>
