;$(function () {
    App.define('modules.common');

    var Common = function () {

        this.$document = $(document);

        this.$table = $('.js-table');

        this.statisticsPlatesUrl = '/api/statistics.get-by-plates';

        this.fromDate = '2016-09-01';
        this.toDate   = '2016-09-15';

        this.tableTemplate = 'table/table.html';

        this.bindEvents();
    };

    //-----------------------------------------------------
    // Event handlers
    //-----------------------------------------------------

    Common.prototype.renderTable = function () {
        var instance = this;

        App.components.api.fetch(instance.statisticsPlatesUrl, {
            'data' : {
                'from' : instance.fromDate,
                'to'   : instance.toDate
            },
            'successCallback' : function (response) {

                App.components.templates.render(instance.tableTemplate, {
                    'data': {
                        items : response.data.plates,
                        total : response.data.total
                    },
                    'callback': function (html) {

                        instance.$table.append(html);
                    }
                });
            }
        });
    };

    Common.prototype.bindEvents = function () {
        var instance = this;

        instance.renderTable();
    };

    App.modules.common = new Common();
});