function staff(){
	var self = this;
	self.ready = function(){
		//add the lnf logo
		$('div#header').prepend($('<div style="float: left; padding-top: 0px;"><a href="/"><img style="height: 72px;" src="/helpdesk/api/lnf/images/lnf-logo.png" border="0" /></a></div>'));
	
		//modify the logout link
		$('div#header p#info a[href="logout.php"]').attr('href', '#').on('click', function(e){
			e.preventDefault();
			lnf.loginRedirect(true);
		});
		
		//make sure we are still logged into LNF and if not go to login page
		lnf.isOnLoginPage({
			'yes': function(body){ lnf.loginRedirect(); },
			'no': function(){ lnf.loginCheck(); }
		});
		
		var showTicketCounts = true;
        
		if (showTicketCounts){
			var target = $("div#system_notice");
			if (target.length == 0){
				target = $("<div/>", {"id": "system_notice", "style": "display: none;"});
				$("div#container").before(target);
			}
			
			//target.append($("<div/>", {"class": "loader"}).html($("<img/>", {"src": "//ssel-apps.eecs.umich.edu/common/images/ajax-loader-2.gif"})));
					
			$.ajax({
				"url": "/helpdesk/api/data.php?action=get-ticket-counts&f=json",
				"success": function(data){
					$(".loader", target).hide();
					
					var showNotice = false;
					
					if (data.staleTicketCount > 0){
						target.append($("<div/>", {"style": "color: red; font-weight: bold;"}).html("You have " + data.staleTicketCount + " tickets with no feedback in more than 5 business days."));
						showNotice = true;
					}
					
					if (data.overdueTicketCount > 0){
						target.append($("<div/>", {"style": "color: red; font-weight: bold;"}).html("You have " + data.overdueTicketCount + " overdue tickets."));
						showNotice = true;
					}
					
					if (showNotice)
						target.show();
				},
				"error": function(err){
					//$(".loader", target).hide();
					target.append($("<div/>", {"style": "color: red; font-weight: bold;"}).html(err));
				}
			})
		}
		
		//fill the tool select on the ticket list page
		$('.tool-select-container').load(
			lnf.root+'ajax.php',
			{'command': 'get-tools'},
			function(responseText, textStatus, XMLHttpRequest){
				var selectedTool = $('.selected-tool').val();
				if (selectedTool != '')
					$('.tool-select').find('option[value="'+selectedTool+'"]').prop('selected', true);
				lnf.tableSearch([{
					'if': function(th){
						return th.find('a').html() == 'Resource'
					},
					'then': function(table, index){
						//replace tool numbers in the table with tool names from the select
						$('tr', table).each(function(){
							var cell = $(this).find('td').eq(index);
							var val = cell.find('span').html();
							if (typeof val != 'undefined' && val != '&nbsp;'){
								var option = $('.tool-select').find('option[value="'+val+'"]');
								if (option.length > 0)
									cell.html(option.html());
							}				
						});
					}
				}]);
			}
		);
		
		//emails look a little nicer with a line break between the name and the bracketed email address
		var replaceEmailName = function(cell){
			var link = cell.find('a');
			if (link.length > 0){
				var replaced = link.html().replace('&lt;', '<br />&lt;');
				link.html(replaced);
			}
		}
		
		lnf.tableSearch([
			{
				'if': function(th){
						return th.html() == 'Primary Outgoing Email' || th.html() == 'Email Address';
				},
				'then': function(table, index){
					//do the email line breaks
					$('tr', table).each(function(){
						var cell = $(this).find('td').eq(index);
						replaceEmailName(cell);
					});
				}
			},
			{
				'if': function(th){
					return th.html() == 'Dept';
				},
				'then': function(table, index){
					//create a control to show/hide dept access via groups
					$('form').append($('<div style="margin-top: 5px;"/>')
						.append('Department access via group membership: ')
						.append($('<a/>').attr('href', '#').html('show').on('click', function(event){
							event.preventDefault();
							var text = $(this).text();
							if (text == 'show'){
								$('.dept-name-staff').css({'font-weight': 'bold'});
								$('.group-depts').show();
								$(this).text('hide');
							}
							else{
								$('.dept-name-staff').css({'font-weight': 'normal'});
								$('.group-depts').hide();
								$(this).text('show');
							}	
						})));
					//replace the cell text with the full dept list for each user
					$('tr', table).each(function(){
						var staffId = $(this).attr('id');
						var cell = $(this).find('td').eq(index);
						cell.load('/helpdesk/api/data.php?action=dept-membership&staff_id='+staffId+'&f=html');
					});
				}
			}
			]);
	}
	
	this.logout = function(){
		lnf.ajax({'command': 'logout'}, function(data){
			console.log(data);
		});
	}

	this.session = function(){
		lnf.ajax({'command': 'session'}, function(data){
			console.log(data);
		});
	}
	
	this.server = function(){
		lnf.ajax({'command': 'server'}, function(data){
			console.log(data);
		});
	}
}

(function($){
	$.lnf = {'staff': new staff()};
	$(document).ready(function(){$.lnf.staff.ready();})
}(jQuery))