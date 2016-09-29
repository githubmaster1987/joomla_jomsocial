define([ 'core' ], function() {

function Dummy() {}

Dummy.prototype.addLocation = function() {
    var text, attachment;

    text = 'Hello...';
    attachment = {
        element: 'profile',
        target: 451,
        type: 'message',
        privacy: 10,
        location: [ 'Plaza Tunjungan', -7.2664271, 112.7424973 ]
    };

    joms.ajax({
        func: 'system,ajaxStreamAdd',
        data: [ text, JSON.stringify( attachment ) ]
    });
};

// Export as `joms.view.dummy`.
joms.util || (joms.util = {});
joms.util.dummy = new Dummy();

});
