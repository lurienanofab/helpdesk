function client(){
    this.ready = function(){
        //add the lnf logo
        $('div#header').prepend($('<div style="float: left; padding-left: 30px;"><a href="/"><img style="height: 59px;" src="/helpdesk/api/lnf/images/lnf-logo.png" border="0" /></a></div>'));
        //add login link
        $('ul#nav a.ticket_status').closest('li').before($('<li><a class="log_in" href="scp">Login</a></li>'));
        //fix the "show support" comment
        var link = $('a#powered_by')
        var container = link.closest('div');
        container.html('').append(link);
    };
}

(function($){
	$.lnf = {'client': new client()};
	$(document).ready(function(){$.lnf.client.ready();});
}(jQuery))