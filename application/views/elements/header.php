<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta http-equiv="X-UA-Compatible" content="chrome=1">
        <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" name="viewport" />
       
        <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
        <meta name="google-signin-client_id" content="928066407020-57mu8pom065chbuuog9iqm4ggldcheqi.apps.googleusercontent.com">
        <meta name="keywords" content="WOW Tasks, Jobs, Job Work">
        <meta name="description" content="WOW Tasks">
        <link rel="icon" href="<?php echo asset_url('images/favicon4.png')?>">
        <title>Job Work - Find Jobs</title>
        <link rel="stylesheet" type="text/css" href="<?php echo asset_url('css/aos.css')?>">
       
        <!-- Bootstrap core CSS -->   
        <link rel="stylesheet" type="text/css" href="<?php echo asset_url('css/bootstrap.css?v='.time())?>">
       
        <!-- Custom styles -->
        <!--    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Comfortaa:300,400,700"> --> 
        <link rel="stylesheet" type="text/css" href="<?php echo asset_url('css/family.css'); ?>"/> 
        <link rel="stylesheet" type="text/css" href="<?php echo asset_url('css/bootstrap-slider.css?v='.time())?>"/>
        <link rel="stylesheet" type="text/css" href="<?php echo asset_url('css/bootstrap-select.css?v='.time())?>">
        <link rel="stylesheet" type="text/css" href="<?php echo asset_url('css/style.css?v='.time())?>">
        <link rel="stylesheet" type="text/css" href="<?php echo asset_url('css/set1.css')?>"/>    
        <link rel="stylesheet" type="text/css" href="<?php echo asset_url('css/custom.css?v='.time())?>"/>
        <link rel="stylesheet" type="text/css" href="<?php echo asset_url('css/media.css?v='.time())?>"/>
        <link href="https://cdn.rawgit.com/michalsnik/aos/2.1.1/dist/aos.css" rel="stylesheet">
        
        <!--    fancy box -->   
		<link rel="stylesheet" type="text/css" href="css/jquery.fancybox-1.3.4.css" media="screen" />
        <link rel="stylesheet" href="css/style-1.css" />
        <link rel="stylesheet" type="text/css" href="css/lightbox.css" media="screen" />
         
        <!-- fontawesome styles -->
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.13/css/all.css" integrity="sha384-DNOHZ68U8hZfKXOrtjWvjxusGo9WQnrNx2sqG0tfsghAvtVlRW3tvkXWZh58N9jp" crossorigin="anonymous">
		<link rel="stylesheet" type="text/css" href="<?php echo asset_url('css/all.css') ?>" />
        <link rel="stylesheet" type="text/css" href="<?php echo asset_url('css/font-awesome.css') ?>" />
		<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css" />  
		<link rel="stylesheet" type="text/css" href="https://use.fontawesome.com/releases/v5.0.13/css/all.css"> 
        
        <noscript>
        	<link rel="stylesheet" type="text/css" href="<?php echo asset_url('css/noJS.css')?>"/>
    	</noscript>
        
        <script src="<?php echo asset_url('js/jquery.min.js')?>"></script>
        
        <!--    datepicker-->
    	<link rel="stylesheet" type="text/css" href="<?php echo asset_url('css/jquery-ui.css')?>">
        <?php $this->load->view('elements/js-variables');?>    
    </head>
<body class="mb-0 safariOnly">
	<span id="auto_increment_pagination" class="d-none">1</span>
	<span id="auto_reaload_job_id" class="d-none">0</span>
	<span id="auto_reaload_selected_tab" class="d-none">Task Info</span>
	<span id="is_auto_reaload_job_details" class="d-none">0</span>
	<span id="selectFilterFormID" class="d-none"></span>
	