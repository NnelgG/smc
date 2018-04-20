<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

/**
 * Class to control all brand related operations.
 */
class Brand extends BaseController {

    public function __construct(){
        parent::__construct();

        $this->load->model('brand_model');   //req to load
        
        $this->isLoggedIn();   
    }

    /**
     * Used to load the first screen of the user
     */
    public function index() {
        //req to load
        $this->load->model('brand_model');
        $this->load->library('pagination');

        $searchText = $this->input->post('searchText');
        $data['searchText'] = $searchText;
        
        $count = $this->brand_model->brandListingCount($searchText);

        $returns = $this->paginationCompress ( "brandListing/", $count, 5 );
        
        $data['brandRecords'] = $this->brand_model->brandListing($searchText, $returns["page"], $returns["segment"]);
        
        $this->global['pageTitle'] = 'SMC : Brand Listing';
        
        $this->loadViews("brand/index", $this->global, $data, NULL);
    }

    /**
     * Used to load the add new form
     */
    function addNew() {
        //is admin?
        if($this->isAdmin() == TRUE){
            $this->loadThis();
        }else{
            $this->load->model('brand_model');  //req to load

            $data['categories'] = $this->brand_model->getBrandCategories();
            
            $this->global['pageTitle'] = 'SMC : Add New Brand';

            $this->loadViews("brand/addNewBrand", $this->global, $data, NULL);
        }
    }

    /**
     * Used to check whether brand already exist or not
     */
    function checkBrandExists(){
        $brandId = $this->input->post("brandId");
        $brandName = $this->input->post("brandName");

        if(empty($brandId)){
            $result = $this->brand_model->checkBrandExists($brandName);
        } else {
            $result = $this->brand_model->checkBrandExists($brandName, $brandId);
        }

        if(empty($result)){ echo("true"); }
        else { echo("false"); }
    }
    
    /**
     * Used to add new brand to the system
     */
    function addNewBrand() {
        //is admin?
        if($this->isAdmin() == TRUE){
            $this->loadThis();
        }else{
            //req to load
            $this->load->library('form_validation');
            $this->load->model('brand_model');

            //form validation : set rules
            $this->form_validation->set_rules('brandName','Brand Name','trim|required|max_length[64]|xss_clean');
            $this->form_validation->set_rules('about','About','trim|required|max_length[256]|xss_clean');
            $this->form_validation->set_rules('categoryId','Category','trim|required|numeric');
            
            if($this->form_validation->run() == FALSE){
                $this->addNew();
            }else{
                $brandName = ucwords(strtolower($this->input->post('brandName')));
                $about = ucwords(strtolower($this->input->post('about')));
                $categoryId = $this->input->post('categoryId');

                $brandInfo = array('brandName'=>$brandName, 'about'=>$about, 'categoryId'=>$categoryId, 'createdBy'=>$this->vendorId, 'createdDtm'=>date('Y-m-d H:i:s'));

                $result = $this->brand_model->addNewBrand($brandInfo);
                
                if($result > 0){
                    $this->session->set_flashdata('success', 'New Brand created successfully');
                }else{
                    $this->session->set_flashdata('error', 'Brand creation failed');
                }
                
                redirect('brand/addNewBrand');
            }
        }
    }

    
    /**
     * Used to load brand edit information
     * @param number $brandId : Optional : This is brand id
     */
    function editOld($brandId = NULL){
        //is admin?
        if($this->isAdmin() == TRUE){
            $this->loadThis();
        }else{
            if($brandId == null){
                redirect('brand');
            }
            
            //req data
            $data['categories'] = $this->brand_model->getBrandCategories();
            $data['brandInfo'] = $this->brand_model->getBrandInfo($brandId);
            
            $this->global['pageTitle'] = 'SMC : Edit Brand';
            
            $this->loadViews("brand/editOld", $this->global, $data, NULL);
        }
    }
    
    
    /**
     * Used to edit the brand information
     */
    function editBrand(){
        //is admin?
        if($this->isAdmin() == TRUE){
            $this->loadThis();
        }else{
            $this->load->library('form_validation');    //req to load

            $brandId = $this->input->post('brandId');
            
            //form validation : set rules
            $this->form_validation->set_rules('brandName','Brand Name','trim|required|max_length[64]|xss_clean');
            $this->form_validation->set_rules('about','About','trim|required|max_length[264]|xss_clean');
            $this->form_validation->set_rules('categoryId','Category','trim|required|numeric');
            
            if($this->form_validation->run() == FALSE){
                $this->editOld($brandId);
            }else{
                $brandName = ucwords(strtolower($this->input->post('brandName')));
                $about = ucwords(strtolower($this->input->post('about')));
                $categoryId = $this->input->post('categoryId');
                
                $brandInfo = array('brandName'=>$brandName, 'categoryId'=>$categoryId, 'about'=>$about, 'updatedBy'=>$this->vendorId, 'updatedDtm'=>date('Y-m-d H:i:s'));
                
                $result = $this->brand_model->editBrand($brandInfo, $brandId);
                
                if($result == true){
                    $this->session->set_flashdata('success', 'Brand updated successfully');
                }else{
                    $this->session->set_flashdata('error', 'Brand updation failed');
                }
                
                redirect('brand');
            }
        }
    }


    /**
     * Used to delete the brand using brandId
     * @return boolean $result : TRUE / FALSE
     */
    function deleteBrand(){
        //is admin?
        if($this->isAdmin() == TRUE){
            echo(json_encode(array('status'=>'access')));
        }else{
            $brandId = $this->input->post('brandId');

            $result = $this->brand_model->deleteBrand($brandId);
            
            if ($result > 0) { echo(json_encode(array('status'=>TRUE))); }
            else { echo(json_encode(array('status'=>FALSE))); }
        }
    }

    function pageNotFound(){
        $this->global['pageTitle'] = 'SMC : 404 - Page Not Found';
        
        $this->loadViews("404", $this->global, NULL, NULL);
    }

}

?>