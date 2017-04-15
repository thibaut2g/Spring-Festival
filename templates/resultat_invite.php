<?php
  var_dump($ListGuests);die();
  echo $ListGuests->getGuestAsTr();
?>

<script>
  jQuery(function() {
    $('.guestCount').each(function(event) {
      $(this).html(<?php echo '"'.$ListGuests->countSqlReturnedGuests.'/'.$ListGuests->countGuests.'"' ?>);
    });
    $('.pagination').each(function(event) {
      // Special for js : replace the \n with \\n...
      var pagination = '<?php echo $ListGuests->getPagination(1); ?>';
      $(this).html(pagination);
    });
    $('input[name="page"]').val(<?php echo $ListGuests->page; ?>);

    $(".page").click(function(event){
      pageHiddenInput.val($(this).attr('id').replace('p',''));
      refreshGuestList();
      return false;
    });
  });
</script>