<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
	<title>Page not Found</title>

	<link rel="stylesheet" type="text/css" href="<?= $this->assets('css/reset.css');?>" />
	<link rel="stylesheet" type="text/css" href="<?= $this->assets('css/errors.css');?>" />
	<link rel="stylesheet" type="text/css" href="<?= $this->assets('css/error404.css');?>" />
</head>

<body>
	<div id="main">
		<div id="content">
			<h1>Page not Found</h1>
			<p id="warning_msg">
				<img src="<?= $this->assets('img/warning_error_tpl.png');?>" alt="warning" class="embedWarning" />
				The <strong>page</strong> you requested <strong>was not found</strong> on this server. 
				Make sure <strong>the address</strong> you entered in your <strong>web browser</strong> is valid or try
				again later. If you are sure that the address is correct but this page is still displayed, 
				<strong>contact server administrator</strong> to get support.
			</p>
			

		</div>
	</div>
</body>

</html>