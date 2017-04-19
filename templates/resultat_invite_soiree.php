<?php 
  echo $Listeguests->getGuestAsTr();
 ?>

<script>
  jQuery(function() {
    $('.guestCount').each(function(event) {
      $(this).html(<?php echo '"'.$ListGuests->countSqlReturnedGuests.'/'.$ListGuests->countGuests.'"' ?>);
    });

    $('.span_bracelet_id').click(function(event) {
      var thisSpan    = $(this);
      var guestId     = thisSpan.attr('data-guestid');
      var bracelet_id = thisSpan.attr('data-braceletid');

      thisSpan.hide();
      thisSpan.parent().append('<div class="control-group input-append">\
    <input class="span1 input_bracelet_id" id="input_bracelet_id_for'+guestId+'" type="text" value="'+bracelet_id+'" maxlength="4">\
    <button class="btn submit_bracelet_id" id="submit_bracelet_id_for'+guestId+'" type="button" title="Valider"><i class="icon-ok"></i></button>\
    <button class="btn cancel" type="button" onclick="jQuery(this).parent().prev().fadeIn();jQuery(this).parent().remove();return false;" title="Anuler"><i class="icon-remove"></i></button>\
  </div>');

      $('.submit_bracelet_id').click(function(event) {
        var thisButton      = $(this);
        var span            = thisButton.parent().prev();
        var spanBracelet_id = span.attr('data-braceletid');
        var input           = thisButton.prev();
        var guestId         = span.attr('data-guestid');
        var bracelet_id     = input.val();

        if (spanBracelet_id != bracelet_id) {
          input.append('<small class="loader" style="margin-left:10px;"><img src="img/icons/spinner.gif" alt="loader"></small>');
          checkXhr('guestId'+guestId);
          xhr['guestId'+guestId] = jQuery.ajax({
            type : "POST",
            url : "admin_guest_check.php",
            data : 'bracelet_id='+bracelet_id+'&id='+guestId+'&saveit=true',
            success: function(server_response){
              if (server_response == '0') {
                thisButton.parent().remove();
                span.html(bracelet_id).fadeIn();
              }else{
                thisButton.parent().addClass('error');
              };
              $('.loader').fadeOut(500,function(event){$(this).remove();});
            }
          });
        } else{
          thisButton.parent().remove();
          span.fadeIn();
        };
      });
    });

    arrivalMarkUpCheck();
  });
</script>