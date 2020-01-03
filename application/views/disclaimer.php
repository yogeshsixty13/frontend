<?php
$data['menu'] = "";
$this->load->view('elements/header-menu', $data);
?>
<section class="mT150 mT130-xs">
	<div class="container mb-5">
		<div class="row">
			<div class="col-md-1 col-sm-12"></div>
			<div class="col-md-10 col-sm-12 support mt-5 text-center">
				<span><?php if($listArr->status == "true"){ echo $listArr->data; } ?></span>
			</div>
		</div>
	</div>
</section>