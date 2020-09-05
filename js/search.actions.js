jQuery(function ($) {
  $('input[type=radio][name=search-action]').change(function () {
    var action = $(this).data('action');
    var form = $(this).closest('form');
    form.attr('action', action);
  });
});
