<script language="javascript" type="text/javascript">
/* <![CDATA[ */
function verify_cat() {
  var fieldvalue=document.new_category.category.value;

  if (fieldvalue == '' || fieldvalue.length < 2) {
    alert("<?php echo OSF_WARN_CAT_EMPTY; ?>");
    document.new_category.category.focus();
    return false;
  } else {
    return true;
  }
}
/* ]]> */
</script>