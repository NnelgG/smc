<?php

$brandId = '';
$brandName = '';
$categoryId = '';
$about = '';

if(!empty($brandInfo)){
    foreach ($brandInfo as $row){
        $brandId = $row->brandId;
        $brandName = $row->brandName;
        $categoryId = $row->categoryId;
        $about = $row->about;
    }
}

?>

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        <i class="fa fa-users"></i> Brand Management
        <small>Add / Edit Brand</small>
      </h1>
    </section>
    
    <section class="content">
        <div class="row">
            <!-- left column -->
            <div class="col-md-8">
                <!-- general form elements -->
                <div class="box box-primary">
                    <div class="box-header">
                        <h3 class="box-title">Enter Brand Details</h3>
                    </div><!-- /.box-header -->
                    <!-- form start -->
                    
                    <!--Form : Edit Brand-->
                    <form role="form" action="<?php echo base_url() ?>brand/editBrand" method="post" id="editBrand" role="form">
                        <div class="box-body">
                            <div class="row">
                                <div class="col-md-6">                                
                                    <div class="form-group">
                                        <label for="brandName">Brand Name</label>
                                        <input type="text" class="form-control" id="brandName" placeholder="Brand Name" name="brandName" value="<?php echo $brandName; ?>" maxlength="64">
                                        <input type="hidden" value="<?php echo $brandId; ?>" name="brandId" id="brandId" />    
                                    </div>
                                </div>
                                <div class="col-md-6">                                
                                    <div class="form-group">
                                        <label for="about">About</label>
                                        <input type="text" class="form-control" id="about" placeholder="About the brand..." name="about" value="<?php echo $about; ?>" maxlength="264">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="categoryId">Category</label>
                                        <select class="form-control" id="categoryId" name="categoryId">
                                            <option value="0">Select Category</option>
                                            <?php
                                            if(!empty($categories)){
                                                foreach ($categories as $row){
                                                    ?>
                                                    <option value="<?php echo $row->categoryId; ?>" <?php if($row->categoryId == $categoryId) {echo "selected=selected";} ?>><?php echo $row->category ?></option>
                                                    <?php
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>    
                            </div>
                        </div><!-- /.box-body -->
    
                        <div class="box-footer">
                            <input type="submit" class="btn btn-primary" value="Submit" />
                            <input type="reset" class="btn btn-default" value="Reset" />
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-md-4">
                <?php
                    $this->load->helper('form');
                    $error = $this->session->flashdata('error');
                    if($error)
                    {
                ?>
                <div class="alert alert-danger alert-dismissable">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <?php echo $this->session->flashdata('error'); ?>                    
                </div>
                <?php } ?>
                <?php  
                    $success = $this->session->flashdata('success');
                    if($success)
                    {
                ?>
                <div class="alert alert-success alert-dismissable">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <?php echo $this->session->flashdata('success'); ?>
                </div>
                <?php } ?>
                
                <div class="row">
                    <div class="col-md-12">
                        <?php echo validation_errors('<div class="alert alert-danger alert-dismissable">', ' <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button></div>'); ?>
                    </div>
                </div>
            </div>
        </div>    
    </section>
</div>

<script src="<?php echo base_url(); ?>assets/js/editBrand.js" type="text/javascript"></script>