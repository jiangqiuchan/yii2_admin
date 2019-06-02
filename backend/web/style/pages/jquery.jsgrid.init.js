/**
* Theme: Ubold Admin Template
* Author: Coderthemes
* JsGrid page
*/


/**
 * JsGrid Controller
 */



var JsDBSource = {
    loadData: function (filter) {
        console.log(filter);
        var d = $.Deferred();
        $.ajax({
            type: "GET",
            url: "data/jsgrid.json",
            data: filter,
            success: function(response) {
                //static filter on frontend side, you should actually filter data on backend side
                /*var filtered_data = $.grep(response, function (client) {
                    return (!filter.Name || client.Name.indexOf(filter.Name) > -1)
                        && (!filter.Age || client.Age === filter.Age)
                        && (!filter.Address || client.Address.indexOf(filter.Address) > -1)
                        && (!filter.Country || client.Country === filter.Country)
                });*/
                var filtered_data = $.grep(response, function (client) {
                    return (!filter.Name || client.Name.indexOf(filter.Name) > -1)
                        && (!filter.ID || client.ID.indexOf(filter.ID) > -1)
                        && (!filter.Email || client.Email.indexOf(filter.Email) > -1)
                        && (!filter.PAssword || client.PAssword.indexOf(filter.PAssword) > -1)
                        && (!filter.State || client.State === filter.State)
                });
                d.resolve(filtered_data);
            }
        });
        return d.promise();
    },

    insertItem: function (item) {
        return $.ajax({
            type: "POST",
            url: "data/jsgrid.json",
            data: item
        });
    },

    updateItem: function (item) {
        return $.ajax({
            type: "PUT",
            url: "data/jsgrid.json",
            data: item
        });
    },

    deleteItem: function (item) {
        return $.ajax({
            type: "DELETE",
            url: "data/jsgrid.json",
            data: item
        });
    },

/*    countries: [
        { Name: "", Id: 0 },
        { Name: "United States", Id: 1 },
        { Name: "Canada", Id: 2 },
        { Name: "United Kingdom", Id: 3 },
        { Name: "France", Id: 4 },
        { Name: "Brazil", Id: 5 },
        { Name: "China", Id: 6 },
        { Name: "Russia", Id: 7 }
    ]*/
    countries: [
        { Name: "", Id: 0 },
        { Name: "启用", Id: 1 },
        { Name: "禁用", Id: 2 }
    ]
};



!function($) {
    "use strict";

    var GridApp = function() {
        this.$body = $("body")
    };
    GridApp.prototype.createGrid = function ($element, options) {
        //default options
        var defaults = {
            height: "450",
            width: "100%",
            filtering: true,
            editing: true,
            inserting: true,
            sorting: true,
            paging: true,
            autoload: true,
            pageSize: 10,
            pageButtonCount: 5,
            deleteConfirm: "你确定删除改行?"
        };

        $element.jsGrid($.extend(defaults, options));
    },
    GridApp.prototype.init = function () {
        var $this = this;
        // 用户列表
        var options = {
            fields: [
                {name: "ID", type: "hidden", width: 50},
                {name: "Name", type: "text", width: 100},
                {name: "Email", type: "text", width: 150},
                {name: "PAssword", type: "text", width: 200},
                {name: "State", type: "select", items: JsDBSource.countries, valueField: "Id", textField: "Name"},
                {type: "control"}
            ],
            controller: JsDBSource,
        };
        $this.createGrid($("#jsGrid"), options);

        var options1 = {
            fields: [
                {name: "ID", type: "text", width: 50},
                {name: "State", type: "select", items: JsDBSource.countries, valueField: "Id", textField: "Name"},
                {name: "Name", type: "text", width: 100},
                {type: "control"}
            ],
            controller: JsDBSource,
        };
        $this.createGrid($("#jsGrid1"), options1);

    },
    //init ChatApp
    $.GridApp = new GridApp, $.GridApp.Constructor = GridApp

}(window.jQuery),

//initializing main application module
function($) {
    "use strict";
    $.GridApp.init();
}(window.jQuery);
