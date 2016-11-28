(function($, _) {
  var Confirm, tmpl;
  _.templateSettings = {
    evaluate: /\{\{([\s\S]+?)\}\}/g,
    interpolate: /\{\{=([\s\S]+?)\}\}/g,
    escape: /\{\{-([\s\S]+?)\}\}/g
  };
  tmpl = "<div class='popup-box'>			<h2 class='urb-stitle'>{{= title}}</h2>			<p class='control mb20'>{{= answer}}</p>			<div class='control'>				<a  class='btn-gray btn-standar' title='No' href='javascript:;'>No</a>     			<a title='Sí' class='btn-urb btn-standar' href='javascript:;'>Sí</a>			</div>		</div>";
  Confirm = function(options) {
    var opt;
    opt = {
      "title": "Título",
      "answer": "Preguntas",
      "callback": null
    };
    this.settings = $.extend(opt, options);
    this.tmpl = this.render();
    this.arquitect = {};
    this.rspta = null;
    this.init();
  };
  Confirm.prototype.init = function() {
    var _this;
    _this = this;
    this.construct();
    this.events();
    $.fancybox(this.arquitect.content, {
      beforeClose: function() {
        if (_this.rspta == null) {
          _this.rspta = false;
        }
        _this.settings.callback && _this.settings.callback(_this.rspta);
      }
    });
  };
  Confirm.prototype.construct = function() {
    var content;
    content = $(this.tmpl);
    this.arquitect = {
      "content": content,
      "yes": content.find(".btn-urb"),
      "no": content.find(".btn-gray")
    };
  };
  Confirm.prototype.events = function() {
    var arquitect, _this;
    _this = this;
    arquitect = _this.arquitect;
    arquitect.yes.on("click", function(e) {
      e.preventDefault();
      _this.rspta = true;
      $.fancybox.close();
    });
    arquitect.no.on("click", function(e) {
      e.preventDefault();
      _this.rspta = false;
      return $.fancybox.close();
    });
  };
  Confirm.prototype.render = function() {
    var template, _this;
    _this = this;
    template = _.template(tmpl);
    return template(_this.settings);
  };
  $.extend({
    confirm: function(json) {
      new Confirm(json);
    }
  });
})(jQuery, _);