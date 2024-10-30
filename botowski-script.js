jQuery(document).ready(function($) {
    function createDialog(content, onAccept) {
        var dialog = $('<div>').text(content).dialog({
            title: 'Botowski Output',
            modal: true,
            buttons: {
                Accept: function() {
                    onAccept();
                    $(this).dialog('close');
                },
                Decline: function() {
                    $(this).dialog('close');
                }
            },
            close: function() {
                $(this).dialog('destroy').remove();
            }
        });

        return dialog;
    }

    function createSpinner() {
    var spinner = $('<div class="botowski-spinner"></div>');
    $('body').append(spinner);
    return spinner;
}

    var rewriteButton = $('<button type="button" class="button botowski-rewrite-btn" id="botowski_rewrite">Rewrite Short Description</button>');
    $('#wp-excerpt-media-buttons').append(rewriteButton);

    var rewriteTitleButton = $('<button type="button" class="button botowski-rewrite-title-btn" id="botowski_rewrite_title">Rewrite Product Title</button>');
    $('#titlewrap').append(rewriteTitleButton);
	
	var rewriteMainDescButton = $('<button type="button" class="button botowski-rewrite-main-desc-btn" id="botowski_rewrite_main_desc">Rewrite Main Description</button>');
    $('#wp-content-media-buttons').append(rewriteMainDescButton);



    $('#botowski_rewrite').on('click', function() {
        var title = $('#title').val();
        var product_id = $('input[name="post_ID"]').val();
        var shortDescription = '';

        if (title.length === 0) {
            alert('Please enter a title to rewrite.');
            return;
        }

        if (tinyMCE.activeEditor && !tinyMCE.activeEditor.isHidden()) {
            shortDescription = tinyMCE.activeEditor.getContent();
        } else {
            shortDescription = $('#excerpt').val();
        }

        var data = {
            action: 'botowski_rewrite',
            nonce: botowski_params.nonce,
            title: title,
            product_id: product_id,
            short_desc: shortDescription
        };

        var spinner = createSpinner();

         $.post(botowski_params.ajax_url, data, function(response) {
            spinner.remove();
            createDialog(response, function() {
                if (tinyMCE.activeEditor && !tinyMCE.activeEditor.isHidden()) {
                    var formattedResponse = $('<div>').text(response).html().replace(/\n/g, '<br>');
                    if (typeof tinyMCE !== 'undefined' && tinyMCE.get('excerpt')) {
                         tinyMCE.get('excerpt').setContent(formattedResponse);
                    } else {
                         $('#excerpt').val(formattedResponse);
}
                } else {
                    $('#excerpt').val(response);
                }
            });
        });
    });



    $('#botowski_rewrite_title').on('click', function() {
        var title = $('#title').val();
        var product_id = $('input[name="post_ID"]').val();

        if (title.length === 0) {
            alert('Please enter a title to rewrite.');
            return;
        }

        var data = {
            action: 'botowski_rewrite_title',
            nonce: botowski_params.nonce,
            title: title,
            product_id: product_id
        };

        var spinner = createSpinner();

        $.post(botowski_params.ajax_url, data, function(response) {
            spinner.remove();
            createDialog(response, function() {
                $('#title').val(response);
            });
        });
    });
	
	
	
	$('#botowski_rewrite_main_desc').on('click', function() {
        var title = $('#title').val();
        var product_id = $('input[name="post_ID"]').val();
        var mainDescription = '';

        if (title.length === 0) {
            alert('Please enter a title to rewrite.');
            return;
        }

        if (tinyMCE.activeEditor && !tinyMCE.activeEditor.isHidden()) {
            mainDescription = tinyMCE.activeEditor.getContent();
        } else {
            mainDescription = $('#content').val();
        }

        var data = {
            action: 'botowski_rewrite_main_desc',
            nonce: botowski_params.nonce,
            title: title,
            product_id: product_id,
            main_desc: mainDescription
        };

        var spinner = createSpinner();

        $.post(botowski_params.ajax_url, data, function(response) {
            spinner.remove();
            createDialog(response, function() {
                if (tinyMCE.activeEditor && !tinyMCE.activeEditor.isHidden()) {
                    var formattedResponse = $('<div>').text(response).html().replace(/\n/g, '<br>');
                    if (typeof tinyMCE !== 'undefined' && tinyMCE.get('content')) {
                        tinyMCE.get('content').setContent(formattedResponse);
                    } else {
                        $('#content').val(formattedResponse);
                    }
                } else {
                    $('#content').val(response);
                }
            });
        });
    });
});
