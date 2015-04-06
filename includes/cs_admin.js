jQuery(function() {

  init_form_dirty_notification();

});

function init_form_dirty_notification() {
  var $form = jQuery('form.caseswap-form');
  var form_dirty = false;

  var make_dirty = function(e) {
    form_dirty = true;
    $form.off('change', make_dirty);
    console.log('Form is dirty');
  };

  $form.on('change', 'input, textarea, checkbox, select', make_dirty);

  $form.on('submit', function(e) {
    form_dirty = false;
  });

  window.onbeforeunload = function(e) {
    if ( form_dirty === true ) {
      return 'You have not yet saved your changes.';
    }
  };
}