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


$(document).ready(function() {

    /**
     * Export Dostavista, select 
     */
    $('#exportdostavistaordersform-userid').change(function(){

        var comment = $('.js-comment[data-id="' + $(this).val() + '"]').text();
        var address = $('.js-address[data-id="' + $(this).val() + '"]').text();
        var content = $('.js-content[data-id="' + $(this).val() + '"]').text();

        $('#exportdostavistaordersform-comment').val(comment);
        $('#exportdostavistaordersform-address').val(address);
        $('#exportdostavistaordersform-products').val(content);
    });
});