$(function () {
    $("#website-submit-form").submit(function(e) {
        e.preventDefault();

        $( "#website-submit-form-container" ).hide();
        $( ".spinner" ).show();

        $.ajax({
            type: "POST",
            url: '/',
            data: $("#website-submit-form").serialize(),
            success: function(data)
            {
                window.location.replace(data);
            }
        });
    });
});