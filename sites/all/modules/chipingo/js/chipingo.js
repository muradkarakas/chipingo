

(function($, Drupal) {
  
    Drupal.ajax.prototype.commands.showMessage = function(ajax, response, status) {
      opts = {};
      opts.classes = ['gray', 'success'];
      $("#freeow").freeow("Success", response.message, opts);
    };
    
})(jQuery, Drupal);