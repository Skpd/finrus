$(document).ready(function(){
    $('.default-button').button();

    $('ul.navigation a').button();

    $('.default-table .sortable').bind('click', function() {
        var
            field     = $(this).attr('field'),
            direction = $('#direction').val() == 'asc' ? 'desc' : 'asc'
        ;

        $('#direction').val(direction);
        $('#sort').val(field);

        $('form').submit();
    });

    if ($('#direction').length > 0 && $('#sort').length > 0) {
        if ($('#direction').val() == 'asc') {
            $('.sortable[field="' + $('#sort').val() + '"]').addClass('ui-state-active').append('<span class="ui-icon ui-icon-arrow-1-n"></span>');
        } else {
            $('.sortable[field="' + $('#sort').val() + '"]').addClass('ui-state-active').append('<span class="ui-icon ui-icon-arrow-1-s"></span>');
        }
    }

    $('.balloon-image-link').click(function() {
        $('<div><img src="' + $(this).attr('href') + '"></div>').dialog({
            modal: true,
            title: 'Паспорт',
            resizable: false,
            buttons: {
                close: {
                    text: 'Закрыть',
                    click: function() {
                        $(this).dialog('close');
                    }
                }
            }
        });

        return false;
    });

    $('.client-link').click(function() {
        var $button = $(this);

        $('<div></div>').dialog({
            modal: true,
            resizable: false,
            draggable: false,
            width: 500,
            position: ['center', 'center'],
            open: function() {
                var $dialog = $(this);

                $.ajax({
                    dataType: 'json',
                    method: 'GET',
                    url: $button.attr('href'),
                    success: function(r) {
                        console.log(r, arguments);
                        $dialog.html(r.content)
                            .dialog('open')
                            .dialog('option', 'position', 'center')
                            .dialog('option', 'title', r.title);
                    }
                });
            },
            buttons: {
                'Закрыть': function() {
                    $(this).dialog('close');
                }
            },
            close: function() {
                $(this).remove();
            }
        });

        return false;
    });
});