/**
 * Gestion des utilisateurs : recherche GIR
 */
$(function() {
    var field = $('#username');
    var url = field.attr('data-url');

    // Auto-complétion sur le champ de recherche "username"
    field
        .attr('autocomplete', 'off')
        .typeahead({
            minLength: 4,
            updater: function(item) {
                var matches = item.match(/^.*\(([A-Z0-9]+)\).*$/); // CUID recherché
                return matches[1];
            },
            source: function(query, process) {
                return $.get(url + '?term='  + query)
                    .done(function(data) {
                        $('#username').parent().removeClass('has-error');
                        $('#username').parent().find('span.form-control-feedback').remove();
                        return process(data);
                    }, 'json')
                    .error(function() {
                        $('#username').parent().addClass('has-error has-feedback');
                        $('#username').parent().append('<span class="glyphicon glyphicon-remove form-control-feedback" aria-hidden="true"></span>');
                    });
            }
    });

    // Informations complètes sur l'utilisateur
    $('#users-search-gir').on('click', function() {
        return $.get(url + '?term='  + field.val() + '&complete=1')
            .done(function(data) {
                $('#username').parent().removeClass('has-error');
                $('#username').parent().find('span.form-control-feedback').remove();

                var xData = data[0].split('|');
                var manager = xData[7].match(/^uid=([A-Z0-9]+),.*$/); // CUID du manager
                
                $('#preferred_language').val(xData[1]);
                $('#civility').val(xData[2]);
                $('#givenname').val(xData[3]);
                $('#surname').val(xData[4]);
                $('#mail').val(xData[5]);
                $('#entity').val(xData[6]);
                $('#manager_username').val(manager[1]);
            }, 'json')
            .error(function() {
                $('#username').parent().addClass('has-error has-feedback');
                $('#username').parent().append('<span class="glyphicon glyphicon-remove form-control-feedback" aria-hidden="true"></span>');
            });
    });
});