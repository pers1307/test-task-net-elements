;(function (App, document, window, $, undefined) {
    App.define('components.api');

    var Api = function () {

    };

    Api.prototype.fetch = function (url, options) {
        options = options || {};
        options.data = options.data || {};
        options.successCallback = options.successCallback || function (response) {};
        options.errorCallback = options.errorCallback || function (response) {};
        $.ajax({
            url: url,
            type: 'POST',
            data: options.data,
            dataType: 'json',
            success: function (response) {
                response.data = response.data || {};
                response.status = response.status || {};
                response.status.code = response.status.code || 0;
                if (response.status.code == 200) {
                    options.successCallback(response);
                } else {
                    options.errorCallback(response);
                }
            },
            error: function (response) {
                options.errorCallback(response);
            }
        });
    };

    App.components.api = new Api();
})(App, document, window, jQuery);