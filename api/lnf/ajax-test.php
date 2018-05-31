<!DOCTYPE html>
<html>
<head>
<style type="text/css">
body{font-family: 'arial'; font-size: 10pt;}

.usercheck{
	margin-top: 10px;
}

.usercheck > table{
	border-collapse: separate;
	border-spacing: 2px;
}

.usercheck > table td,
.usercheck table th{
	padding: 5px;
}

.usercheck > table th{
	background-color: #77cc99;
	border: solid 1px #66aa88;
	text-align: left;
}

.usercheck > table td{
	border: solid 1px #dddddd;
	width: 300px;
}
</style>
<script type="text/javascript" src="//code.jquery.com/jquery-2.0.1.min.js"></script>
<script type="text/javascript">
$(document).ready(function(){

	var createUser = function(data){
		var table = $('<table/>');
		table.append(
			$('<tr/>')
				.append($('<th/>').html('IsAuthenticated'))
				.append($('<td/>').html(data.IsAuthenticated?"yes":"no"))
		).append(
			$('<tr/>')
				.append($('<th/>').html('UserName'))
				.append($('<td/>').html(data.UserName))
		).append(
			$('<tr/>')
				.append($('<th/>').html('LName'))
				.append($('<td/>').html(data.LName))
		).append(
			$('<tr/>')
				.append($('<th/>').html('FName'))
				.append($('<td/>').html(data.FName))
		).append(
			$('<tr/>')
				.append($('<th/>').html('Email'))
				.append($('<td/>').html(data.Email))
		).append(
			$('<tr/>')
				.append($('<th/>').html('Roles'))
				.append($('<td/>').html(data.Roles.length > 0 ? '<div>&diams; '+data.Roles.join('</div><div>&diams; ')+'</div>' : ''))
		);
		$('.usercheck').html(table);
	}

	$('.container').load('ajax.php', {'command': 'get-tools'});
	
	$.ajax({
		url: 'ajax.php',
		type: 'POST',
		data: {'command': 'user-check'},
		dataType: 'json',
		success: function(data){
			createUser(data);
		},
		error: function(err){
			$('.usercheck').html('<div style="color: #ff0000;">'+err+'</div>');
		}
	});
});
</script>
</head>
<body>
<div class="container"></div>
<div class="usercheck"></div>
</body>
</html>
