/**
 * Created with JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 20.08.13
 * Time: 12:37
 * To change this template use File | Settings | File Templates.
 */

$(function(){
    $('form.custodian-form').live('submit', function(event) {
        var form = $(this);
        var btn = form.find('input[type="submit"]');
        var targetId = form.parent().attr('id');

        btn.button('loading');

        form.ajaxSubmit({target: '#' + targetId, complete: function(){ btn.button('reset') } });
        event.preventDefault();
    });

    $('.custodian-messages-list a').live('click', function(event) {
        var elem = $(this);
        var parent = elem.parent();

        parent.append('<img class="ajax-loader" src="/img/ajax-loader.gif" style="margin-left: 5px;">');

        $.ajax({
            url: elem.attr('href'),
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    var dialog = $('#modal_dialog');

                    dialog.find('.modal-header h3').html('Edit Message');
                    dialog.find('.modal-body').html(response.content);

                    $('#editor').html(dialog.find('#custodian_message_message').val());

                    dialog.modal('show');

                    initBootstrapWysiwyg();
                }
                if (response.status === 'error') {
                    alert(response.message);
                }
            },
            complete: function() {
                parent.find('.ajax-loader').remove();
            }
        });

        event.preventDefault();
    });

    $('#custodian_message_form').live('submit', function(event) {
        var form = $(this);
        var dialog = $('#modal_dialog');
        var btn =  dialog.find('.save-modal-form-btn');

        form.find('#custodian_message_message').val($('#editor').html());

        btn.button('loading');

        var options = {
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    dialog.modal('hide');
                }
                if (response.status === 'error') {
                    form.parent().html(response.content);
                }
            },
            complete: function() {
                btn.button('reset');
            }
        };

        form.ajaxSubmit(options);
        event.preventDefault();
    });
});

function initBootstrapWysiwyg() {
    var fonts = ['Serif', 'Sans', 'Arial', 'Arial Black', 'Courier',
            'Courier New', 'Comic Sans MS', 'Helvetica', 'Impact', 'Lucida Grande', 'Lucida Sans', 'Tahoma', 'Times',
            'Times New Roman', 'Verdana'],
        fontTarget = $('[title=Font]').siblings('.dropdown-menu');

    $.each(fonts, function (idx, fontName) {
        fontTarget.append($('<li><a data-edit="fontName ' + fontName +'" style="font-family:\''+ fontName +'\'">'+fontName + '</a></li>'));
    });
    $('a[title]').tooltip({container:'body'});
    $('.dropdown-menu input').click(function() {return false;})
        .change(function () {$(this).parent('.dropdown-menu').siblings('.dropdown-toggle').dropdown('toggle');})
        .keydown('esc', function () {this.value='';$(this).change();});

    $('[data-role=magic-overlay]').each(function () {
        var overlay = $(this), target = $(overlay.data('target'));
        overlay.css('opacity', 0).css('position', 'absolute').offset(target.offset()).width(target.outerWidth()).height(target.outerHeight());
    });

    $('#editor').wysiwyg();
}