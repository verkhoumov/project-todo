<!DOCTYPE html>

<html lang="ru">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<meta http-equiv="x-ua-compatible" content="ie=edge">

		<title>{{{title}}}</title>
		<meta name="description" content="{{description}}">
	
		<!-- Styles -->
		<link rel="stylesheet" href="/resources/css/common.css{{#version}}?v={{version}}{{/version}}">

		<!-- Optimization for IE < 9 -->
		<!--[if lt IE 9]><script src="//cdn.jsdelivr.net/g/html5shiv@3.7.3,respond@1.4.2"></script><![endif]-->
	</head>

	<body>
		<header>{{&header}}</header>
		<main>{{&content}}</main>
		<footer>{{&footer}}</footer>

		<!-- Шаблоны и модальные окна -->
		{{&templates}}
		<!-- Custom data -->
		{{#json}}<script>var CustomData = {{{json}}};</script>{{/json}}
		<!-- Scripts -->
		<script src="/resources/js/common.js{{#version}}?v={{version}}{{/version}}"></script>
	</body>
</html>