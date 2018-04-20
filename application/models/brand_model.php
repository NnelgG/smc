<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

class Brand_model extends CI_Model{
	public static $tbl_brands = 'brands';
	public static $tbl_categories = 'categories';

    /**
     * Used to get the brand listing count
     * @param string $searchText : This is optional search text
     * @return number $count : This is row count
     */
    function brandListingCount($searchText = ''){
        $this->db->select('BaseTbl.brandId, BaseTbl.brandName, BaseTbl.about, Category.category');
        $this->db->from('brands as BaseTbl');
        $this->db->join('categories as Category', 'Category.categoryId = BaseTbl.categoryId','left');
        
        if(!empty($searchText)) {
            $likeCriteria = "(BaseTbl.brandName  LIKE '%".$searchText."%'
                            OR  BaseTbl.about  LIKE '%".$searchText."%'
                            OR  Category.category  LIKE '%".$searchText."%')";
            $this->db->where($likeCriteria);
        }

        $query = $this->db->get();
        
        return count($query->result());
    }
    
    /**
     * Used to get the brand listing count
     * @param string $searchText : This is optional search text
     * @param number $page : This is pagination offset
     * @param number $segment : This is pagination limit
     * @return array $result : This is result
     */
    function brandListing($searchText = '', $page, $segment){
        $this->db->select('BaseTbl.brandId, BaseTbl.brandName, BaseTbl.about, BaseTbl.last_crawled_at, BaseTbl.thumbnail_url, Category.category');
        $this->db->from('brands as BaseTbl');
        $this->db->join('categories as Category', 'Category.categoryId = BaseTbl.categoryId','left');
        
        if(!empty($searchText)) {
            $likeCriteria = "(BaseTbl.brandName  LIKE '%".$searchText."%'
                            OR  BaseTbl.about  LIKE '%".$searchText."%'
                            OR  Category.category  LIKE '%".$searchText."%')";
            $this->db->where($likeCriteria);
        }

        $this->db->limit($page, $segment);
        
        $query = $this->db->get();
        
        $result = $query->result();        
        
        return $result;
    }

    /**
     * Used to get the brand categories information
     * @return array $result : This is result of the query
     */
    function getBrandCategories(){
        $this->db->select('categoryId, category');
        $this->db->from(self::$tbl_categories);

        $query = $this->db->get();
        
        return $query->result();
    }

    /**
     * Used to check whether brand name is already exist or not
     * @param {string} $brandName : This is brand name
     * @param {number} $brandId : This is brand id
     * @return {mixed} $result : This is searched result
     */
    function checkBrandExists($brandName, $brandId = 0){
        $this->db->select("brandName");
        $this->db->from(self::$tbl_brands);
        $this->db->where("brandName", $brandName);

        if($brandId != 0){
            $this->db->where("brandId !=", $brandId);
        }

        $query = $this->db->get();

        return $query->result();
    }
    
    /**
     * Used to add new brand to system
     * @return number $insert_id : This is last inserted id
     */
    function addNewBrand($brandInfo){
        $this->db->trans_start();
        $this->db->insert(self::$tbl_brands, $brandInfo);
        
        $insert_id = $this->db->insert_id();
        
        $this->db->trans_complete();
        
        return $insert_id;
    }
    
    /**
     * Used to get brand information by id
     * @param number $brandId : This is brand id
     * @return array $result : This is brand information
     */
    function getBrandInfo($brandId){
        $this->db->select('brandId, brandName, categoryId, about');
        $this->db->from(self::$tbl_brands);
        $this->db->where('brandId', $brandId);
        
        $query = $this->db->get();
        
        return $query->result();
    }
    
    /**
     * Used to update the brand information
     * @param array $brandInfo : This is brand updated information
     * @param number $brandId : This is brand id
     */
    function editBrand($brandInfo, $brandId){
        $this->db->where('brandId', $brandId);
        $this->db->update(self::$tbl_brands, $brandInfo);
        
        return TRUE;
    }
        
    /**
     * Used to delete the brand information
     * @param number $brandId : This is brand id
     * @return boolean $result : TRUE / FALSE
     */
    function deleteBrand($brandId){
        $this->db->delete(self::$tbl_brands, array('brandId' => $brandId));
        
        return $this->db->affected_rows();
    }

}

  