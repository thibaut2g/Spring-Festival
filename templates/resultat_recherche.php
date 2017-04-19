<?php

  echo $Listguests->getGuestAsTr();
   
?>

<script>
  jQuery(function() {
    $('.guestCount').each(function(event) {
      $(this).html(<?php echo '"'.$Listguests->countSqlReturnedguests.'/'.$Listguests->countguests.'"' ?>);
    });
    $('.pagination').each(function(event) {
      // Special for js : replace the \n with \\n...
      var pagination = '<?php echo $Listguests->getPagination(1); ?>';
      $(this).html(pagination);
    });
    $('input[name="page"]').val(<?php echo $Listguests->page; ?>);

    $(".page").click(function(event){
      pageHiddenInput.val($(this).attr('id').replace('p',''));
      refreshGuestList();
      return false;
    });
  });
</script>