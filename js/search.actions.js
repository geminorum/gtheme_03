jQuery(function ($) {
  $('input[type=radio][name=search-action]').on('change', function () {
    const action = $(this).data('action');
    const form = $(this).closest('form');
    form.attr('action', action);
  });
});
