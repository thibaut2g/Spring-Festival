<?php

  echo $ListInvites->getGuestAsTr();
   
?>

<script>
  jQuery(function() {
    $('.inviteCount').each(function(event) {
      $(this).html(<?php echo '"'.$ListInvites->countSqlReturnedInvites.'/'.$ListInvites->countInvites.'"' ?>);
    });
    $('.pagination').each(function(event) {
      // Special for js : replace the \n with \\n...
      var pagination = '<?php echo $ListInvites->getPagination(1); ?>';
      $(this).html(pagination);
    });
    $('input[name="page"]').val(<?php echo $ListInvites->page; ?>);

    $(".page").click(function(event){
      pageHiddenInput.val($(this).attr('id').replace('p',''));
      refreshGuestList();
      return false;
    });
  });
</script>