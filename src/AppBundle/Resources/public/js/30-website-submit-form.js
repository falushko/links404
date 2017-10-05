$(function () {

    var inProgress = 'inProgress';
    var finished = 'finished';

    /**
     * Send website on analysis via AJAX
     */
    $("#website-submit-form").submit(function(e) {
        e.preventDefault();
        $( "#website-submit-form-container" ).hide();
        $( ".spinner" ).show();

        $.ajax({
            type: "POST",
            url: '/',
            data: $("#website-submit-form").serialize(),
            success: function(data) {},
            error: function (error) {}
        });

        setInterval(getProgress, 5000);
    });

    /**
     * Gets analysis progress
     */
    function getProgress() {
        $.ajax({
            type: "GET",
            url: '/progress',
            data: $("#website-submit-form").serialize(),
            success: function(data)
            {
                if (data['progress'] == finished) {
                    window.location.href = data['url'];
                } else if (data['progress'] == inProgress) {
                    $('.progress-bar-container .progress-bar').css('width', data['progressPercentage'] + '%');
                }
            }
        });
    }
});