;$(function () {
    App.define('modules.common');

    var Common = function () {

        this.$document = $(document);

        this.oneClickClass = '.js-one-click';
        this.noButtonClick = 'noButtonClick';


        this.bindEvents();
    };

    //-----------------------------------------------------
    // Event handlers
    //-----------------------------------------------------

    Common.prototype.tryGetFileLink = function () {
        var instance = this;

    };

    Common.prototype.bindEvents = function () {
        var instance = this;

        instance.$document.on('click', instance.oneClickClass, function (e) {

            instance.oneClickClass.each(function () {

                var errorCount = $('.has-error').length;

                if (errorCount == 0) {

                    $(this).addClass('noButtonClick');
                }
            });
        });
    };

    App.modules.common = new Common();
});