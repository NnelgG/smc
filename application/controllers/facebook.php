<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

class Facebook extends BaseController {

    public function __construct(){
        parent::__construct();
        
        //req to load
        $this->load->model('facebook_model');

        $this->isLoggedIn();
    }

    public function dashboard(){
        $this->global['pageTitle'] = 'SMC : Facebook Platform Stats';
        
        $this->loadViews("facebook/index", $this->global, NULL, NULL);
    }

    public function crawl() {
        echo 'function :    index :    start<br>';
        
        require_once APPPATH . 'libraries/php-graph-sdk-5.x/src/Facebook/autoload.php';
        echo 'requires :    facebook api<br>';

        $fb = new Facebook\Facebook([
            /* store to mysql db w/ enc sha256 */
            'app_id' => '1710527335685473',
            'app_secret' => '02d6d5e05577db5e91a5b89f22db4de1',
            'default_graph_version' => 'v2.11',
            ]);
        
        try {
            $brand = 'suzuki';

            // Returns a `Facebook\FacebookResponse` object
            $response = $fb->get(
            "search?q=$brand&type=page&fields=id,name,category,about,emails,location,phone,website,birthday,posts.limit(10){id,from,created_time,message,comments.limit(10){id,created_time,message,like_count,reactions,comment_count,comments.limit(10){id,parent,created_time,message,like_count,reactions}}}&limit=10",
            "1710527335685473|02d6d5e05577db5e91a5b89f22db4de1"
            );
            //echo 'graph api request<br>';

            if(!empty($response)){  //not empty response?
                echo 'response not empty<br>';

                $response_graphEdge = $response->getGraphEdge();   //response to graphEdge convertion
                
                if(!empty($response_graphEdge)){   //not empty response graphEdge?
                    //init  //pages, posts, comments, child comments as an array
                    $array_pages = array(); $array_posts = array(); $array_comments = array(); $array_ch_comments = array();

                    //init  //pages, posts, comments, child comments array indexes
                    $i_page = 0; $i_post = 0; $i_comm = 0; $i_ch_comm = 0;
                    
                    $count_posts_saved = 0;

                    $i_next = 0;

                    do{
                        $count_response_graphEdge = count($response_graphEdge);
                        echo "<h3>FacebookResponse :    GraphEdge :   count :   $count_response_graphEdge</h3>";

                        //Each and every single PAGE will undergo to this process
                        foreach ($response_graphEdge as $graphNode) {
                            //isset field?  //sanitize string   //format
                            $graphNode_name_sanit = (isset($graphNode['name'])) ? filter_var($graphNode['name'], FILTER_SANITIZE_STRING) : '';
                            $graphNode_category_sanit = (isset($graphNode['category'])) ? filter_var($graphNode['category'], FILTER_SANITIZE_STRING) : '';
                            $graphNode_about_sanit = (isset($graphNode['about'])) ? filter_var($graphNode['about'], FILTER_SANITIZE_STRING) : '';
                            
                            //emails
                            $graphNode_emails_arr = (isset($graphNode['emails'])) ? $graphNode['emails'] : '';
                            $graphNode_emails_sanit = '';
                            if(!empty($graphNode_emails_arr)){
                                foreach ($graphNode_emails_arr as $row_email) {
                                    $graphNode_emails_sanit .= filter_var($row_email, FILTER_SANITIZE_STRING) . '|';
                                }
                            }
                            
                            //location
                            $graphNode_loc_sanit = '';
                            if(isset($graphNode['location'])){
                                $graphNode_loc_city_sanit = (isset($graphNode['location']['city'])) ? filter_var($graphNode['location']['city'], FILTER_SANITIZE_STRING) : 'n/a';
                                $graphNode_loc_country_sanit = (isset($graphNode['location']['country'])) ? filter_var($graphNode['location']['country'], FILTER_SANITIZE_STRING) : 'n/a';
                                $graphNode_loc_state_sanit = (isset($graphNode['location']['state'])) ? filter_var($graphNode['location']['state'], FILTER_SANITIZE_STRING) : 'n/a';
                                $graphNode_loc_street_sanit = (isset($graphNode['location']['street'])) ? filter_var($graphNode['location']['street'], FILTER_SANITIZE_STRING) : 'n/a';
                                $graphNode_loc_zip_sanit = (isset($graphNode['location']['zip'])) ? filter_var($graphNode['location']['zip'], FILTER_SANITIZE_STRING) : 'n/a';
                                $graphNode_loc_sanit = $graphNode_loc_city_sanit . '|' . $graphNode_loc_country_sanit . '|' . $graphNode_loc_state_sanit . '|' . $graphNode_loc_street_sanit . '|' . $graphNode_loc_zip_sanit;
                            }

                            $graphNode_phone_sanit = (isset($graphNode['phone'])) ? filter_var($graphNode['phone'], FILTER_SANITIZE_STRING) : '';
                            $graphNode_website_sanit = (isset($graphNode['website'])) ? filter_var($graphNode['website'], FILTER_SANITIZE_STRING) : '';
                            $graphNode_bday_formatted = (isset($graphNode['birthday'])) ? date_format($graphNode['birthday'], 'Y-m-d') : '';

                            echo '<h3>page i: ' . $i_page . '</h3>';
                            echo '<strong>page id: </strong>' . $graphNode['id'] . '<br>';
                            echo '<strong>page name: </strong>' . $graphNode_name_sanit . '<br>';
                            echo '<strong>category: </strong>' . $graphNode_category_sanit . '<br>';
                            echo '<strong>about: </strong>' . $graphNode_about_sanit . '<br>';
                            echo '<strong>emails: </strong>' . $graphNode_emails_sanit . '<br>';
                            echo '<strong>location: </strong>' . $graphNode_loc_sanit . '<br>';
                            echo '<strong>phone: </strong>' . $graphNode_phone_sanit . '<br>';
                            echo '<strong>website: </strong>' . $graphNode_website_sanit . '<br>';
                            echo '<strong>birthday: </strong>' . $graphNode_bday_formatted . '<br>';

                            echo '<strong>posts: </strong><br>';
                            
                            //array of posts    //isset field?
                            $graphNode_posts = (isset($graphNode['posts'])) ? $graphNode['posts'] : '';

                            if(!empty($graphNode_posts)){   //not empty posts?
                                //Each and every single POST will undergo to this process
                                foreach ($graphNode_posts as $graphNode_post) {
                                    $save_post = false; //init

                                    //isset field?  //sanitize string   //format
                                    $graphNode_post_created_time = $graphNode_post['created_time'];
                                    $graphNode_post_created_time_formatted = date_format($graphNode_post_created_time, 'Y-m-d h:m:s');
                                    
                                    $graphNode_post_from_arr = (isset($graphNode_post['from'])) ? $graphNode_post['from'] : '';
                                    $graphNode_post_from = $graphNode_post_from_arr['id'];


                                    $graphNode_post_message = (isset($graphNode_post['message'])) ? filter_var($graphNode_post['message'], FILTER_SANITIZE_STRING) : '';
                            
                                    echo '<h3>post i: ' . $i_post . '</h3>';
                                    echo '<strong>- - post id: </strong>' . $graphNode_post['id'] . '<br>';
                                    echo '- - post from: ' . $graphNode_post_from . '<br>';
                                    echo '- - post created time: ' . $graphNode_post_created_time_formatted . '<br>';
                                    echo '- - post message: ' . $graphNode_post_message . '<br>';

                                    if(stripos($graphNode_post_message, $brand)){   //post message contains brand?  //case-insensitive
                                        echo '<strong>- - GONNA SAVE THIS -- post message </strong><br>';
                                        $save_post = true;
                                    }

                                    //array of comments     //isset field?  //sanitize string
                                    $graphNode_post_comments = (isset($graphNode_post['comments'])) ? $graphNode_post['comments'] : '';

                                    if(!empty($graphNode_post_comments)){   //not empty post comments?
                                        
                                        echo '- - post comments: <br>';
                                        //Each and every single COMMENT will undergo to this process
                                        foreach ($graphNode_post_comments as $graphNode_post_comment) { 
                                            //isset field?  //sanitize string   //format field
                                            $graphNode_post_comment_created_time = $graphNode_post_comment['created_time'];
                                            $graphNode_post_comment_created_time_formatted = date_format($graphNode_post_comment_created_time, 'Y-m-d h:m:s');
                                            $graphNode_post_comment_message = (isset($graphNode_post_comment['message'])) ? filter_var($graphNode_post_comment['message'], FILTER_SANITIZE_STRING) : '';

                                            echo '- - - - comment id: ' . $graphNode_post_comment['id'] . '<br>';
                                            echo '- - - - comment created time: ' . $graphNode_post_comment_created_time_formatted . '<br>';
                                            echo '- - - - comment message: ' . $graphNode_post_comment_message . '<br>';

                                            if(stripos($graphNode_post_comment_message, $brand)){   //post comment message contains brand?  //case-insensitive
                                                echo '<strong>- - GONNA SAVE THIS -- post comment message </strong><br>';
                                                $save_post = true;
                                            }                               

                                            //child comments     //isset field?  //sanitize string
                                            $graphNode_post_ch_comments = (isset($graphNode_post_comment['comments'])) ? $graphNode_post_comment['comments'] : '';
                                            
                                            if(!empty($graphNode_post_ch_comments)){   //not empty comment comments?
                                                
                                                echo '- - - - comment comments: <br>';

                                                foreach ($graphNode_post_ch_comments as $graphNode_post_ch_comment) { 
                                                    //isset field?  //sanitize string   //format field created time
                                                    $graphNode_post_ch_comment_created_time = $graphNode_post_ch_comment['created_time'];
                                                    $graphNode_post_ch_comment_created_time_formatted = date_format($graphNode_post_ch_comment_created_time, 'Y-m-d h:m:s');
                                                    $graphNode_post_ch_comment_message = (isset($graphNode_post_ch_comment['message'])) ? filter_var($graphNode_post_ch_comment['message'], FILTER_SANITIZE_STRING) : '';

                                                    echo '- - - - - - - - child comment id: ' . $graphNode_post_ch_comment['id'] . '<br>';
                                                    echo '- - - - - - - - child comment parent: ' . $graphNode_post_ch_comment['parent'] . '<br>';
                                                    echo '- - - - - - - - child comment created time: ' . $graphNode_post_ch_comment_created_time_formatted . '<br>';
                                                    echo '- - - - - - - - child comment message: ' . $graphNode_post_ch_comment_message . '<br>';

                                                    if(stripos($graphNode_post_ch_comment_message, $brand)){   //child comment message contains brand?  //case-insensitive
                                                        echo '<strong>- - GONNA SAVE THIS -- child comment message </strong><br>';
                                                        $save_post = true;
                                                    }

                                                    //child comment info--for later use--batch insert
                                                    $array_ch_comments[$i_ch_comm]['comment_id'] = $graphNode_post_comment['id'];
                                                    $array_ch_comments[$i_ch_comm]['ch_comment_id'] = $graphNode_post_ch_comment['id'];
                                                    $array_ch_comments[$i_ch_comm]['created_time'] = $graphNode_post_ch_comment_created_time_formatted;
                                                    $array_ch_comments[$i_ch_comm]['message'] = $graphNode_post_ch_comment_message;

                                                    $i_ch_comm++;
                                                }   //end : single child comment
                                            }

                                        //comment info--for later use--batch insert
                                        $array_comments[$i_comm]['post_id'] = $graphNode_post['id'];
                                        $array_comments[$i_comm]['comment_id'] = $graphNode_post_comment['id'];
                                        $array_comments[$i_comm]['created_time'] = $graphNode_post_comment_created_time_formatted;
                                        $array_comments[$i_comm]['message'] = $graphNode_post_comment_message;

                                        $i_comm++;
                                        }   //end : single comment
                                    }

                                    if($save_post) {
                                        $count_posts_saved++;

                                        //$graphNode_post_comments_mres = filter_var($graphNode_post_comments, FILTER_SANITIZE_STRING);
                                        
                                        //post info--for later use--batch insert
                                        $array_posts[$i_post]['post_id'] = $graphNode_post['id'];
                                        $array_posts[$i_post]['post_from'] = $graphNode_post_from;
                                        $array_posts[$i_post]['created_time'] = $graphNode_post_created_time_formatted;
                                        $array_posts[$i_post]['message'] = $graphNode_post_message;
                                        //$array_posts[$i_post]['comments'] = $graphNode_post_comments_mres;

                                        echo '<h3> + + + + + + + + + + + + + + + + + + + + GONNA SAVE THIS POST! </h3>';
                                    } else {
                                        echo '<h3> + + + + + + + + + + + + + + + + + + + + NOT GONNA SAVE THIS POST! </h3>';
                                    }   

                                    $i_post++;     
                                }   //end : single post
                            } 

                            //page info--for later use--batch insert
                            $array_pages[$i_page]['page_id'] = $graphNode['id'];
                            $array_pages[$i_page]['name'] = $graphNode_name_sanit;
                            $array_pages[$i_page]['category'] = $graphNode_category_sanit;
                            $array_pages[$i_page]['about'] = $graphNode_about_sanit;
                            $array_pages[$i_page]['emails'] = $graphNode_emails_sanit;
                            $array_pages[$i_page]['location'] = $graphNode_loc_sanit;
                            $array_pages[$i_page]['phone'] = $graphNode_phone_sanit;
                            $array_pages[$i_page]['website'] = $graphNode_website_sanit;
                            $array_pages[$i_page]['birthday'] = $graphNode_bday_formatted;
                            
                            $i_page++;

                            echo "<h3> + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + +  page i: $i_page ends | saved post: $count_posts_saved </h3>";
                        }   //end : single page

                        //batch insert
                        $this->facebook_model->batchInsertFbPages($array_pages);
                        $this->facebook_model->batchInsertFbPosts($array_posts);
                        $this->facebook_model->batchInsertFbComments($array_comments);
                        $this->facebook_model->batchInsertFbChildComments($array_ch_comments);
                        
                        $response_graphEdge = $fb->next($response_graphEdge); //get next page
                        
                        $i_next++;

                    }while((!empty($response_graphEdge)) && ($i_next < 2)); //temp

                    //echo '<h3>Array Pages - Hierarchical</h3>';

                    //echo '<pre>';
                    //var_dump ($array_pages);
                    //echo '</pre>';
                    
                    //echo '<h3>Array Posts - Hierarchical</h3>';

                    //echo '<pre>';
                    //print_r ($array_posts);
                    //echo '</pre>';
                }
            }

        } catch(Facebook\Exceptions\FacebookResponseException $e) {
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }

        echo 'function :    index : end';
    }

}

//some changes happen
//another changes happen while on branch facebook
