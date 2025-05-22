jQuery(document).ready(function($) {
    var audioPlayers = $(".ld-audio-player");
    var completed = new Set();
    var total = audioPlayers.length;
    var nextTopicUrl = typeof wdmLdAudioCompletion !== 'undefined' ? wdmLdAudioCompletion.nextTopicUrl : '';
    var ajaxUrl = typeof wdmLdAudioCompletion !== 'undefined' ? wdmLdAudioCompletion.ajaxUrl : '';
    var topicId = typeof wdmLdAudioCompletion !== 'undefined' ? wdmLdAudioCompletion.topicId : '';
    var nonce = typeof wdmLdAudioCompletion !== 'undefined' ? wdmLdAudioCompletion.nonce : '';

    // Hide the mark complete button
    $(".learndash_mark_complete_button, #learndash_mark_complete_button").hide();

    audioPlayers.each(function(index) {
        var audioPlayer = this;
        $(audioPlayer).data("audio-index", index);
        audioPlayer.addEventListener("ended", function() {
            completed.add(index);
            if (completed.size === total) {
                $.ajax({
                    url: ajaxUrl,
                    type: "POST",
                    data: {
                        action: "wdm_ld_mark_topic_complete",
                        topic_id: topicId,
                        nonce: nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            if (nextTopicUrl) {
                                window.location.href = nextTopicUrl;
                            }
                        }
                    },
                    error: function() {
                        console.error("AJAX request failed.");
                    }
                });
            }
        });
    });
}); 