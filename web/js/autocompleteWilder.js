$( document ).ready(function() {
    $("#form_input").keyup(function(){
        var input = $(this).val();
        if ( input.length >= 2 ) {
            $.ajax({
                type: "POST",
                url: "/ajax-wilder/" + input,
                dataType: 'json',
                timeout: 3000,
                success: function(response){
                    var wilders = JSON.parse(response.data);

                    html = '';
                    wilders.forEach(function(wilder) {
                        html += '<li>' + wilder.firstname + ' ' + wilder.lastname + '</li>';
                    });
                    $('#trombi_search_autocomplete').html(html);
                    $('#trombi_search_autocomplete li').on('click', function() {
                        $('#form_input').val($(this).text());
                        $('#trombi_search_autocomplete').html('');
                    });
                },
                error: function() {
                    $('#trombi_search_autocomplete').text('Ajax call error');
                }
            });
        } else {
            $('#trombi_search_autocomplete').html('');
        }
    });
});

