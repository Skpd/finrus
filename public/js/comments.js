$(document).ready(function() {
    $('.comments-open').click(function() {
        var link = baseUrl + '/comments/list/conversation/' + $(this).data('id'), $button = $(this);

        $('<div></div>').dialog({
            modal: true,
            width: 600,
            title: 'Комментарии',
            open: function() {
                var $dialog = $(this);

                $.get(link, $.proxy(function(r) {
                    $(this).html(r).dialog('open').dialog('option', 'position', 'center');

                    $('#commentadd').click(function() {
                        var $form = $(this).parents('#comment-form');

                        if ($form.find('#text').val()) {
                            $(this).attr('disabled', 'disabled');

                            $.post($form.attr('action'), $form.serialize(), $.proxy(function() {
                                $form.find('#text').val('');
                                $form.parent().empty();

                                $dialog.dialog('close');

                                $button.click();
                            }, this));
                        } else {
                            $form.find('#text').effect("highlight", {}, 500).focus();
                        }
                    }).button();
                }, this));
            },
            close: function() {
                $(this).remove();
            }
        });
    });
});