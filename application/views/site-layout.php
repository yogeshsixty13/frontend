<?php

$this->load->view('elements/header');
$data['where'] = @$where;
$this->load->view($pageName,$data);

$this->load->view('elements/footer',$data);

?>