<!DOCTYPE html>
<html>
<head>
	<title>Account Recovery</title>
</head>
<body>
	<div style="padding:10px; border:1px solid #cccccc; text-align:center; font-size:20px;">



	<img src="{{ $logo ?? '' ?? '' }}" alt="logo" />


	<h1>Hi  {{ $firstName }} , </h1>
	<p>We have just received a request to reset the password of your account on <b>2023-04-03 08:31:35 PM.</b></p>

	<p>Requested From-  IP Address: <b>192.210.85.220</b> using <b>Firefox</b> on <b>Windows 10</b></p>

	<p style="margin-top:20px;">Copy your account recovery code below:</p>
	<h2 style="color:#5B2C6F">{{ $emailCode }}</h2>

	<p style="margin-top:20px; color:red">If you're not the one who made this request and you're not aware of it, disregard this message.</p>

	<p style="margin-top:20px; color:red">© zenithstake.com 2023</p>

</div>
</body>
</html>