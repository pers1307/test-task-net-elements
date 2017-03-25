//-----------------------------------------------------
// Simple dynamic template loader
//-----------------------------------------------------

;$(function () {
    App.define('components.templates');

    var Templates = function () {
        this.cache = {};
    };

    Templates.prototype.render = function (path, options) {
        var instance = this;

        options = options || {};
        options.data = options.data || {};
        options.callback = options.callback || function (compiled) { };

        if(typeof instance.cache[path] === "undefined") {

            $.get('/templates/' + path, function(html){

                instance.cache[path] = _.template(html);

                options.callback(instance.cache[path](options.data));
            });
        } else {

            options.callback(instance.cache[path](options.data));
        }
    };

    App.components.templates = new Templates();
});