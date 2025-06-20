jQuery(document).ready(function($) {
    $(document).on('click', '.chatgpt-submit', function() {
        const container = $(this).closest('.chatgpt-container');
        const responseArea = container.find('.chatgpt-response');
        const promptInput = container.find('.chatgpt-prompt');
        const loadingIndicator = container.find('.chatgpt-loading');
        const maxTokens = container.find('.chatgpt-max-tokens').val();
        const temperature = container.find('.chatgpt-temperature').val();
        const model = container.find('.chatgpt-model').val();
        
        const prompt = promptInput.val().trim();
        
        if (!prompt) {
            responseArea.html('<p class="chatgpt-error">Please enter a question</p>');
            return;
        }
        
        responseArea.html('');
        loadingIndicator.show();
        $(this).prop('disabled', true);
        
        $.ajax({
            url: chatgpt_vars.ajax_url,
            type: 'POST',
            data: {
                action: 'chatgpt_request',
                nonce: chatgpt_vars.nonce,
                prompt: prompt,
                max_tokens: maxTokens,
                temperature: temperature,
                model: model
            },
            success: function(response) {
                if (response.success) {
                    responseArea.html('<div class="chatgpt-message">' + response.data.response + '</div>');
                    promptInput.val('');
                } else {
                    responseArea.html('<p class="chatgpt-error">Error: ' + response.data + '</p>');
                }
            },
            error: function(xhr, status, error) {
                responseArea.html('<p class="chatgpt-error">Request failed: ' + error + '</p>');
            },
            complete: function() {
                loadingIndicator.hide();
                container.find('.chatgpt-submit').prop('disabled', false);
            }
        });
    });
    
    // Handle Enter key press
    $(document).on('keypress', '.chatgpt-prompt', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            $(this).closest('.chatgpt-container').find('.chatgpt-submit').click();
        }
    });
});