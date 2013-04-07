<?php
 
/*
 * - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - 
 * The contents of this file are subject to the Common Public Attribution License Version 1.0
 * you may not use this file except in compliance with the License.
 * Copyright (C) <2013> <amar@amkosys.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * Official site: http://www.amkosys.com
 * Author: Amar Vora<amar@amkosys.com>
 * - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
 */
 
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Stream extends CI_Controller {
    protected $template_variables = array();
	public $data = array();
	
	public function __construct() {
	  parent::__construct();
        if(!$this->ion_auth->logged_in()){
            redirect("/",302);
        }
        $this->load->model('model_stream');
	    $this->load->library('wall_lib');
	    $this->load->library('youtube_video_info');
        $this->template_variables['section'] ='stream';
        $this->template_variables['logged_in'] =$this->ion_auth->logged_in();
	}
	
	/**
	 * Default Home page Action
	 */
	public function index()
	{
		$data['render_comment_box'] = $this->wall_lib->render_main_comment_box();
        $data['stream_data'] = $this->model_stream->get_stream();
        $this->load->helper('date');

        //var_dump($data['stream_data']);
        $this->template_variables['function'] ='index';
        $this->template_variables['title'] ='Members Stream';
        $this->load->view('includes/meta', $this->template_variables);
        $this->load->view('includes/header', $this->template_variables);
		$this->load->view('members/members_stream',$data);
        $this->load->view('includes/footer', $this->template_variables);
	}	
	
	/**
	 * Action For New Comments and Youtube Video sharing
	 */
	public function new_share() {
		
		if($this->input->post('form') && $this->input->post('form') == 'main_comment_req' && $this->input->post('comment')) {
			
			$user_comment = $this->input->post('comment');
			$user_name = $this->session->userdata('username');
			$user_id = $this->session->userdata('user_id');
			$user_img = '/_assets/img/user/'.$user_id.'.jpg';

            if(!file_exists ( BASEPATH . '/_assets/img/user/'.$user_id.'.jpg' )) {
                $user_img = '/_assets/img/wall/user_image_1.jpg';
            }

            preg_match_all('/(^|\s)(@\w+)/', $user_comment, $result);
            $parent_id = false;
            $stream_type= 'outbox';
            if($this->input->post('permission')) {
                $permission=$this->input->post('permission');
            } else {
                $permission="public";
            }

            if ($result) {
                //get the userid out of the username.
                $mentions = array ();
                $i = 0;
                foreach($result as $mention_data) {
                    $username_clean = str_replace("@","",$mention_data);
                    if ($this->model_stream->get_username_from_id($username_clean)) {
                        $mentions[] = $this->model_stream->get_username_from_id($username_clean);
                    }
                }
                if(isset($mentions)){
                    $permission="private";
                }
            }
            //var_dump($username_clean);
            //var_dump($permission);
            if($this->input->post('reply_id')) {
                $parent_id = $this->input->post('reply_id');
            }
            //var_dump($parent_id);
			// Check if Youtube video link found, then fetch full video info //

            $s_images = false;
            $s_type = false;
            $s_title = false;
            $s_link = false;
            $s_desc = false;
            $s_attachmentID = false;
            preg_match_all('/\b(?:(?:https?|ftp|file):\/\/|www\.|ftp\.)[-A-Z0-9+&@#\/%=~_|$?!:,.]*[A-Z0-9+&@#\/%=~_|$]/i', $user_comment, $link, PREG_PATTERN_ORDER);
            //var_dump($link);
            $arrVid = $this->youtube_video_info->is_youtube_url($user_comment);
            if (!empty($arrVid)) {
                $s_type = "youtube";
                $s_link = $arrVid['url'];
                $s_title = $arrVid['title'];
                $s_desc = $arrVid['description'] ? $arrVid['description'] : "you tube video;";
                $s_attachmentID = new mongoid();
            }
            if (!empty($link[0])) {
                $link_Info = $this->wall_lib->get_link($link[0]);
                $s_type = "link";
                $s_title = $link_Info['title'];
                $s_link = $link_Info['url'];
                $s_desc = $link_Info['description'];
                $arrLink = $link_Info;
            }
            $shared_items = array(
                'stream_share_URL' => $s_link,
                'stream_share_type' => $s_type, //link, you tube
                'stream_share_title' => $s_title,
                'stream_share_desc' => $s_desc,
            );

            $data_array = array (
                'stream_current_user_id' => $user_id, //user id of receiver if this is a private message
                'stream_message' => $user_comment,
                'stream_type' => $stream_type, // inbox or outbox
                'stream_permission' => $permission, //Public, Followers, Friends, Group, Private (Only Me)
                'stream_parent_id' => $parent_id,
                'stream_shared' => $shared_items
              );

             $dbSave = $this->model_stream->do_comment_add($data_array);
            //var_dump($dbSave);

			// If Video Link Found then render the video //
			if(!empty($arrVid)) {
				echo str_replace("[REPLY_COUNT]","",$this->wall_lib->show_new_video_share($user_comment,$dbSave,$arrVid,$user_name,$user_id,$user_img,$s_attachmentID));
            } elseif(!empty($link[0])) {
                echo str_replace("[REPLY_COUNT]","",$this->wall_lib->show_new_link_share($user_comment,$dbSave,$arrLink,$user_name,$user_id,$user_img));
            } else {
				echo str_replace("[REPLY_COUNT]","",$this->wall_lib->show_new_share($user_comment,$dbSave,$user_name,$user_id,$user_img));
            }
        }
		exit;
	}

    public function reply_share() {
        $user_id = $this->session->userdata('user_id');
        $username = $this->session->userdata('username');
        $stream_id = str_replace("reply_comment_","",$this->input->post('stream_id'));
        $message = $this->input->post('reply_comment');
        if($this->input->post('form') && $this->input->post('form') == 'reply_comment_req' && $this->input->post('reply_comment') && $this->input->post('stream_id')) {

            $dbSave = $this->model_stream->do_comment_on_stream($stream_id, $message);

            if($dbSave) {
                //echo $stream_id;
                $replyImg  = '/_assets/img/user/' . $user_id . '.jpg';
                    if (!file_exists(BASEPATH . $replyImg)) {
                        $replyImg = '/_assets/img/wall/user_3.jpg';
                    }

                $comment_return ='<div class="comment_post_user" id="reply_'.$stream_id.'">
                                                <div class="comment_post_image">
                                                    <img src="'.$replyImg.'" alt=""/>
                                                </div>
                                                <div class="comment_post_data">
                                                    <h5><a href="/profile/'.$username.'">'.$username.'</a></h5>
                                                    <p>'.$message.'</p>
                                                </div>
                                            </div>';
                echo $comment_return;

            } else {
                echo "Sorry system could not save your comment. Try again later.";
            }
        }
        //echo $this->input->post('stream_id');
        //echo $this->input->post('form') ;
        //echo $this->input->post('reply_comment') ;
        //redirect('/stream',302);
    }





}