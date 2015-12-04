(function () {
    'use strict';

    Keeper.createTableFromData = function (params) {
        var data = params.data;
        var buttons = params.buttons || [];
        var fields = params.fields;
        var parent = params.putIn;

        var tableContents = [];
        var dataRows = [];
        var headers = [];

        fields.forEach(function (field) {
            headers.push(createElement({
                elem: 'th',
                textContent: field.title
            }));
        });

        buttons.forEach(function (button) {
            headers.push(createElement({
                elem: 'th',
                textContent: button.title
            }))
        });

        tableContents.push(createElement({
            elem: 'thead',
            inside: [
                createElement({
                    elem: 'tr',
                    inside: headers
                })
            ]
        }));

        data.forEach(function (row) {
            var rowData = [];
            fields.forEach(function (field) {
                rowData.push(createElement({
                    elem: 'td',
                    textContent: row[field.key]
                }));
            });
            buttons.forEach(function (button) {
                rowData.push(createElement({
                    elem: 'td',
                    inside: [
                        createElement({
                            elem: 'button',
                            className: 'btn btn-default ' + (button.style ? ' btn-' + button.style : ''),
                            textContent: button.text,
                            onclick: function (e) {
                                button.onclick(row)
                            }
                        })
                    ]
                }))
            });
            dataRows.push(createElement({elem: 'tr', inside: rowData}));
        });

        tableContents.push(createElement({
            elem: 'tbody',
            inside: dataRows
        }));


        return createElement({
            elem: 'table',
            className: 'table table-hover table-condensed',
            inside: tableContents,
            putIn: parent
        });
    };

})();