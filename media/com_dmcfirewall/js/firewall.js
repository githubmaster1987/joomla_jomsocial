/**
 *	@Package DMC Firewall
 *	@Copyright Dean Marshall Consultancy Ltd
 *	Email software@deanmarshall.co.uk
 *	web: http://www.deanmarshall.co.uk/
 *	web: http://www.webdevelopmentconsultancy.com/
 */
window.addEvent( 'domready' ,  function() {
	$('changelog').addEvent('click', showChangelog);
});

function showChangelog() {
	var firewallChangelogElement = $('firewall-changelog').clone();
	
    SqueezeBox.fromElement(
        firewallChangelogElement, {
            handler: 'adopt',
            size: {
                x: 550,
                y: 500
            }
        }
    );
}