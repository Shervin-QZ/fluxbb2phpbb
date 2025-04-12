$(document).ready(function() {
    $('.fluxbb_to_phpbb_ajax_form').each(function() {
        var $form = $(this);

        // Disable all submit buttons with name 'action_online' before sending the form
        function disableActionButtons() {
            $('.action_online').prop('disabled', 'disabled');
        }

        // Enable all submit buttons with name 'action_online'
        function enableActionButtons() {
            $('.action_online').prop('disabled', false);
        }

        function submitFormWithOffset() {
            disableActionButtons(); // Disable buttons before sending request
            $form.find('.proccess-pm').html('please wait ...');
            $.ajax({
                type: $form.attr('method'),
                url: $form.attr('action'),
                data: $form.serialize(), // Send form data
                dataType: 'json', // Expect JSON response
                success: function(response) {
                    // Check if success is true in the response
                    if (response.success === true) {
                        // Check if 'continue' is present and true in the response
                        if (response.continue === true) {
                            // Resubmit form with a delay
                            setTimeout(function() {
                                submitFormWithOffset();
                            }, 3000); // 3-second delay
                        } else {
                            enableActionButtons(); // Enable buttons if 'continue' is false
                            $form.find('.proccess-pm').html('done');
                            phpbb.alert(response.title, response.message);
                        }
                        $form.find('.proccess-count').html(response.count);
                    } else {
                        enableActionButtons(); // Enable buttons if success is not true
                        phpbb.alert(response.MESSAGE_TITLE, response.MESSAGE_TEXT);
                    }
                },
                error: function() {
                    console.error("Error in the AJAX request.");
                    phpbb.alert('Erorr', "Error in the AJAX request.");
                    enableActionButtons(); // Enable buttons on AJAX error
                }
            });
        }

        // Form submission event 
        $form.submit(function(e) {
            e.preventDefault(); // Prevent default form submission
            submitFormWithOffset(); // 
        });
    });
});
