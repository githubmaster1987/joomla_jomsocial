require([
    'core',
    'utils/crop',
    'utils/dropdown',
    'utils/hovercard',
    'utils/loadlib',
    'utils/popup',
    'utils/tab',
    'utils/tagging',
    'utils/validation',
    'utils/video',
    'utils/wysiwyg',
    'functions/tagging',
    'views/comment',
    'views/customize',
    'views/misc',
    'views/stream',
    'views/streams',
    'views/toolbar',
    'api'
], function() {

    joms.onStart(function() {
        joms.view.comment.start();
        joms.view.page.initialize();
        joms.view.stream.start();
        joms.view.streams.start();
        joms.view.toolbar.start();
        joms.view.customize.start();
        joms.view.misc.start();

        joms.util.dropdown.start();
        joms.util.tab.start();
        joms.util.validation.start();
        joms.util.wysiwyg.start();
        joms.util.hovercard.initialize();

        // Fetch all friends in context (group, event, or default) if user is logged-in.
        if ( +window.joms_my_id ) {
            joms.fn.tagging.fetchFriendsInContext();
        }

    });

    joms.jQuery(function() {
        joms.start();
    });
});
