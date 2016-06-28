function autoComplete(ele) {
    var url = ele.attr('data-ac-url');
    var field = ele.attr('data-ac-field');
    
    url = url.replace('{field}', field);
    url = url.replace('{value}', '');
    
    ele.attr('autocomplete', 'off');

    ele.typeahead({
        minLength: 3,
        source: function (query, process) {
            return $.get( url + ele.val(), function (data) {
                return process(data);
            }, 'json');
        }
    });
}

$(document).ready(function () {
    $("input[data-ac-url]").each(function () {
        autoComplete($(this));
    });
});



