<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

class Login extends CI_Controller {
    //default constructor
    public function __construct(){
        parent::__construct();
        $this->load->model('login_model');  //req to load
    }

    public function index(){
        $this->isLoggedIn();
    }
    
    //check if user is logged in
    function isLoggedIn(){
        $isLoggedIn = $this->session->userdata('isLoggedIn');
        
        if(!isset($isLoggedIn) || $isLoggedIn != TRUE){
            $this->load->view('login');
        }else{
            redirect('/dashboard');
        }
    }
    
    //Authenticate user
    public function loginMe(){
        $this->load->library('form_validation');    //req to load
        
        //form validation set rules
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email|max_length[128]|xss_clean|trim');
        $this->form_validation->set_rules('password', 'Password', 'required|max_length[32]|');
        
        //is input valid?
        if($this->form_validation->run() == FALSE){
            $this->index();
        }else{
            $email = $this->input->post('email');   
            $password = $this->input->post('password');
            
            $result = $this->login_model->loginMe($email, $password);   //user data

            if(count($result) > 0){
                foreach ($result as $res){
                    $sessionArray = array('userId'=>$res->userId,                    
                                            'role'=>$res->roleId,
                                            'roleText'=>$res->role,
                                            'name'=>$res->name,
                                            'isLoggedIn' => TRUE
                                    );
                                    
                    $this->session->set_userdata($sessionArray);    //put user data to session
                    
                    redirect('/dashboard');
                }
            }else{
                $this->session->set_flashdata('error', 'Email or password mismatch');
                
                redirect('/login');
            }
        }
    }

    public function forgotPassword(){
        $this->load->view('forgotPassword');
    }
    
    //form : forgot password
    //generate reset password request link
    function resetPasswordUser(){
        $status = '';
        
        $this->load->library('form_validation');    //req to load
        
        //form validation set rules
        $this->form_validation->set_rules('login_email','Email','trim|required|valid_email|xss_clean');
                
        //is input valid?
        if($this->form_validation->run() == FALSE){
            $this->forgotPassword();
        }else {
            $email = $this->input->post('login_email');
            
            //is email exist?
            if($this->login_model->checkEmailExist($email)){
                $encoded_email = urlencode($email);
                
                $this->load->helper('string');  //req to load

                $data['email'] = $email;
                $data['activation_id'] = random_string('alnum',15);
                $data['createdDtm'] = date('Y-m-d H:i:s');
                $data['agent'] = getBrowserAgent();
                $data['client_ip'] = $this->input->ip_address();
                
                $save = $this->login_model->resetPasswordUser($data);                
                
                //is reset password details saved?
                if($save){
                    //get activation code
                    $data1['reset_link'] = base_url() . "resetPasswordConfirmUser/" . $data['activation_id'] . "/" . $encoded_email;
                    
                    //get user info
                    $userInfo = $this->login_model->getCustomerInfoByEmail($email);

                    //is user info not empty?
                    if(!empty($userInfo)){
                        $data1["name"] = $userInfo[0]->name;
                        $data1["email"] = $userInfo[0]->email;
                        $data1["message"] = "Reset Your Password";
                    }

                    $sendStatus = resetPasswordEmail($data1);   //send email--inh from cias_helper

                    //is email sent?
                    if($sendStatus){
                        $status = "send";
                        setFlashData($status, "Reset password link sent successfully, please check mails.");
                    } else {
                        $status = "notsend";
                        setFlashData($status, "Email has been failed, try again.");
                    }
                }else{
                    $status = 'unable';
                    setFlashData($status, "It seems an error while sending your details, try again.");
                }
            }else{
                $status = 'invalid';
                setFlashData($status, "This email is not registered with us.");
            }

            redirect('/forgotPassword');
        }
    }

    //reset the password confirm user
    function resetPasswordConfirmUser($activation_id, $email){
        //get email and activation code from URL values at index 3-4
        $email = urldecode($email);
        
        //check activation details
        $is_correct = $this->login_model->checkActivationDetails($email, $activation_id);
        
        $data['email'] = $email;
        $data['activation_code'] = $activation_id;
        
        //is activation details correct?
        if ($is_correct == 1){
            $this->load->view('newPassword', $data);
        }else{
            redirect('/login');
        }
    }
    
    //form : new password
    //create new password
    function createPasswordUser(){
        $status = '';
        $message = '';
        
        $email = $this->input->post("email");
        $activation_id = $this->input->post("activation_code");
        
        $this->load->library('form_validation');    //req to load
        
        //form validation set rules
        $this->form_validation->set_rules('password','Password','required|max_length[20]');
        $this->form_validation->set_rules('cpassword','Confirm Password','trim|required|matches[password]|max_length[20]');
        
        //is input valid?
        if($this->form_validation->run() == FALSE){
            $this->resetPasswordConfirmUser($activation_id, urlencode($email));
        }else{
            $password = $this->input->post('password');
            $cpassword = $this->input->post('cpassword');
            
            //check activation details
            $is_correct = $this->login_model->checkActivationDetails($email, $activation_id);
            
            //is activation details correct?
            if($is_correct == 1){                
                $this->login_model->createPasswordUser($email, $password);
                
                $status = 'success';
                $message = 'Password changed successfully';
            }else{
                $status = 'error';
                $message = 'Password changed failed';
            }
            
            setFlashData($status, $message);

            redirect("/login");
        }
    }


}

?>