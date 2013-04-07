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
 
if (!defined('BASEPATH')) exit('No direct script access allowed');
	
/**
 * Wall Lib
 *
 * @author Amar Vora <amar@amkosys.com>
 * @copyright Copyright (c) 5 Jan 2013 
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @version 1.0
**/

class Wall_lib
{
    var $CI;
	var $wall_title = 'Stream';
	var $main_user_pic = '/_assets/img/wall/user_3.jpg';
	var $new_comment_div;
	var $new_video_div;
	
	/**
	 * __construct
	 *
	 * @access public
	 * @param void
	 * @return void
	**/
	
	public function __construct()
	{
        $CI =& get_instance();
        $CI->load->library('session');



		$this->new_link_div = '<div class="share_link">
									<div class="link_description">
										<span><a href="[LINK_URL]" target="_blank">[LINK_TITLE]</a></span>
										<p>[LINK_DESCRIPTION]</p>
									</div>
								</div>';

        $this->new_video_div = '<div class="share_video">
									<div class="video_shot">[VIDEO_IMAGES]</div>
									<div class="video_description">
										<span><a href="[VIDEO_URL]" target="_blank">[VIDEO_TITLE]</a></span>
										<p>[VIDEO_DESCRIPTION]</p>
									</div>
								</div>';



        $this->new_comment_div =
                        '<div class="user_detail_1 row" id="user_comment_[WALL_STREAM_ID]">
							<div class="user_photo_name">
								<div class="user_photo"><img src="[WALL_USER_IMG]" alt="" /></div>
								<div class="user_deta_right">
									<div class="user_name">
                                        <span>
                                            <a href="#">[WALL_USER_NAME]</a>
                                        </span>
                                        [WALL_USER_COMMENT]
                                        <label>[WALL_USER_MIN]</label>
									</div>
									<!-- EXTRA SHARE -->
									<ul>
									    <li><a href="#" class="expand_comment" id="comment_[WALL_STREAM_ID]">Expand<small>[REPLY_COUNT]</small></a></li>
										<li><a href="#" class="like_comment" id="like_[WALL_STREAM_ID]">Like</a></li>
									</ul>
								</div>
							</div><div class="comment_post hide" id="id_comment_[WALL_STREAM_ID]">
                                <div id="wrapper_id_reply_comment_[WALL_STREAM_ID]">[WALL_REPLIES]</div>
							    <input type="text" name="id_comment_[WALL_STREAM_ID]" id="input_reply_comment_[WALL_STREAM_ID]" class="comment_input" placeholder="Write a Comment..." value="" />
							    <div class="post_part">
						        <input type="button" name="reply" class="comment_reply_button post_button" id="reply_comment_[WALL_STREAM_ID]" value="Reply" />
							       <div style="text-align:center"><img src="/_assets/img/wall/loader.gif" id="box_loader_reply_comment_[WALL_STREAM_ID]" style="display:none;" /></div>
						        </div>
							</div>
						</div>';
	}
	
	public function render_main_comment_box($user_id = false) {
        $CI =& get_instance();
        $CI->load->library('session');
        $user_id = $CI->session->userdata('user_id');
            $main_pic = $this->main_user_pic;
        if(!file_exists ( BASEPATH . '/_assets/img/user/'.$user_id.'.jpg' )) {
            $main__pic = '/_assets/img/wall/user_3.jpg';
        }
        //todo make the below compliant with bootstrap
		$box = '<div class="comment_box row">
					<div class="span7 comment_box_title">
						<h3 style="margin-bottom:5px">'.$this->wall_title.'</h3>

					</div>
					
					<div class="comment_data_box" id="main_comment_share">
						<div class="user_image"><img src="'.$main_pic.'" /></div>
						<textarea class="comment_textarea" id="main_comment">What\'s on your mind?</textarea>
						<div class="post_part">
							<input type="button" name="share" class="post_button" value="Share" id="main_share_btn" />
							<div style="text-align:center"><img src="/_assets/img/wall/loader.gif" id="main_box_loader" style="display:none;" /></div>
						</div>
					</div>
					
					<div class="comment_data_box" id="main_link_share" style="display:none">
						<div class="user_image"><img src="'.$main_pic.'" /></div>
						
						<input type="text" name="main_link" id="main_link" size="50" class="link_input" />
						<div class="post_part">
							<input type="button" name="share" class="post_button" value="Share" id="link_share_btn" />
							<div style="text-align:center"><img src="/_assets/img/wall/loader.gif" id="link_box_loader" style="display:none;" /></div>
						</div>
					</div>

				</div>
				<div class="clear"></div>
				<input type="hidden" name="cur_image" id="cur_image" />
				<div id="hold_post" style="display:none;margin-bottom:10px;padding:10px;background-color:#F7F7F7;width:600px; height:150px; border:1px dashed #2588C7;"></div>
		';
		
		return $box;
	}
	
	public function show_new_share($user_comment,$stream_id,$user_name,$user_id,$user_img,$min='Just now') {
		
		$comment_div = $this->new_comment_div;
        $comment_div = preg_replace('@\[WALL_USER_ID\]@',$user_id,$comment_div);
        $comment_div = preg_replace('@\[WALL_STREAM_ID\]@',$stream_id,$comment_div);
		$comment_div = preg_replace('@\[WALL_USER_NAME\]@',$user_name,$comment_div);
		$comment_div = preg_replace('@\[WALL_USER_COMMENT\]@',$user_comment,$comment_div);
		$comment_div = preg_replace('@\[WALL_USER_IMG\]@',$user_img,$comment_div);
		$comment_div = preg_replace('@\[WALL_USER_MIN\]@',$min,$comment_div);

		return $comment_div;
	}
	
	public function show_new_video_share($user_comment,$stream_id,$arrVid,$user_name,$user_id,$user_img,$min='Just now') {

		$length = 300; //modify for desired width
//var_dump($arrVid);
		if (isset($arrVid['description']) || strlen($arrVid['description']) <= $length) {
			$arrVid['description'] = $arrVid['description']; //do nothing
		} else {
			$arrVid['description'] = preg_replace('/\s+?(\S+)?$/', '', substr($arrVid['description'], 0, $length));
			$arrVid['description'] .= '...';
		}

        $video_div = $this->new_video_div;
		$video_div = preg_replace('@\[VIDEO_SHOT\]@',$arrVid['video'],$video_div);
		$video_div = preg_replace('@\[VIDEO_TITLE\]@',$arrVid['title'],$video_div);
		$video_div = preg_replace('@\[VIDEO_URL\]@',$arrVid['url'],$video_div);
		$video_div = preg_replace('@\[VIDEO_DESCRIPTION\]@',$arrVid['description'],$video_div);

		$comment_div = $this->new_comment_div;
        $comment_div = preg_replace('@\[WALL_USER_ID\]@',$user_id,$comment_div);
		$comment_div = preg_replace('@\[WALL_STREAM_ID\]@',$stream_id,$comment_div);
		$comment_div = preg_replace('@\[WALL_USER_NAME\]@',$user_name,$comment_div);
		$comment_div = preg_replace('@\[WALL_USER_COMMENT\]@',$user_comment,$comment_div);
		$comment_div = preg_replace('@\[WALL_USER_IMG\]@',$user_img,$comment_div);
		$comment_div = preg_replace('@\[WALL_USER_MIN\]@',$min,$comment_div);
		$comment_div = preg_replace('@<\!\-\- EXTRA SHARE \-\->@',$video_div,$comment_div);

		return $comment_div;
	}
    public function show_new_link_share($user_comment,$stream_id,$arrLink,$user_name,$user_id,$user_img,$min='Just now') {

        $length = 300; //modify for desired width
        $title = $arrLink['title'];
        $url = $arrLink['url'];
        $description = $arrLink['description'];
        if (isset($arrLink['description']) || strlen($arrLink['description']) <= $length) {
            $arrLink['description'] = $arrLink['description']; //do nothing

        } else {
            $arrLink['description'] = preg_replace('/\s+?(\S+)?$/', '', substr($arrLink['description'], 0, $length));
            $arrLink['description'] .= '...';
        }

        $link_div = $this->new_link_div;
        $link_div = preg_replace('@\[LINK_TITLE\]@',$title,$link_div);
        $link_div = preg_replace('@\[LINK_URL\]@',$url,$link_div);
        $link_div = preg_replace('@\[LINK_DESCRIPTION\]@',$description,$link_div);

        $comment_div = $this->new_comment_div;
        $comment_div = preg_replace('@\[WALL_USER_ID\]@',$user_id,$comment_div);
        $comment_div = preg_replace('@\[WALL_STREAM_ID\]@',$stream_id,$comment_div);
        $comment_div = preg_replace('@\[WALL_USER_NAME\]@',$user_name,$comment_div);
        $comment_div = preg_replace('@\[WALL_USER_COMMENT\]@',$user_comment,$comment_div);
        $comment_div = preg_replace('@\[WALL_USER_IMG\]@',$user_img,$comment_div);
        $comment_div = preg_replace('@\[WALL_USER_MIN\]@',$min,$comment_div);
        $comment_div = preg_replace('@<\!\-\- EXTRA SHARE \-\->@',$link_div,$comment_div);

        return $comment_div;
    }
	public function checkURLValues($value)
	{
		$value = trim($value);
		if (get_magic_quotes_gpc())
		{
			$value = stripslashes($value);
		}
		
		$value = strtr($value, array_flip(get_html_translation_table(HTML_ENTITIES)));
		$value = strip_tags($value);
		$value = htmlspecialchars($value);
		
		return $value;
	}
	
	public function fetch_record_from_link($path)
	{
		$file = fopen($path, "r");
		if (!$file)
		{
			exit("Problem occured");
		}
		$data = '';
		while (!feof($file))
		{
			$data .= fgets($file, 1024);
		}
		return $data;
	}
    /**
     * Action For Link Extracting
     */
    public function get_link($url) {
        if(is_array($url)) {
            $url= $url[0];
        }
        //if there is an attachment id, it means to process and save the file to the shared Link library.

        $url = $this->checkURLValues($url);
        $url = preg_replace('@/$@','',$url);
        $CI =& get_instance();
        $CI->load->library('simple_html_dom');

        // Get HTML Source Code //
        //$raw =  $this->file_get_html($url);
        //echo "this is my url: ".$url."|";
        $contents = file_get_contents($url,false,null,-1);
        $raw =  $CI->simple_html_dom->load($contents);
        //var_dump($raw);
        //Get Title and Description //
        $title = $description = '';
        //echo count($raw->find('title'));
        foreach($raw->find('title') as $element) {
            $title = $element->plaintext;
            //echo "title: ".$title;
            break; //only need one title.
        }
        //var_dump($raw);
        $description = $raw->find("meta[name=description]",0);
        if(isset($description)) {
            //Avoid pesky notices. Check if there is an array before parsing.
            $description=$description->getAttribute('content');
        } else {
            $description=false;
        }

        $return_array = array(
            "title" => $title,
            "description" => $description,
            "url" => $url,
        );
        //var_dump($return_array);
        return $return_array;
    }
    function file_get_html($url, $use_include_path = false, $context=null, $offset = -1, $maxLen=-1, $lowercase = true, $forceTagsClosed=true, $target_charset = DEFAULT_TARGET_CHARSET, $stripRN=true, $defaultBRText=DEFAULT_BR_TEXT, $defaultSpanText=DEFAULT_SPAN_TEXT)
    {
        // We DO force the tags to be terminated.
        $dom = new simple_html_dom(null, $lowercase, $forceTagsClosed, $target_charset, $stripRN, $defaultBRText, $defaultSpanText);
        // For sourceforge users: uncomment the next line and comment the retreive_url_contents line 2 lines down if it is not already done.
        $contents = file_get_contents($url, $use_include_path, $context, $offset);
        // Paperg - use our own mechanism for getting the contents as we want to control the timeout.
        //$contents = retrieve_url_contents($url);
        if (empty($contents) || strlen($contents) > MAX_FILE_SIZE)
        {
            return false;
        }
        // The second parameter can force the selectors to all be lowercase.
        $dom->load($contents, $lowercase, $stripRN);
        return $dom;
    }

// get html dom from string
    function str_get_html($str, $lowercase=true, $forceTagsClosed=true, $target_charset = DEFAULT_TARGET_CHARSET, $stripRN=true, $defaultBRText=DEFAULT_BR_TEXT, $defaultSpanText=DEFAULT_SPAN_TEXT)
    {
        $dom = new simple_html_dom(null, $lowercase, $forceTagsClosed, $target_charset, $stripRN, $defaultBRText, $defaultSpanText);
        if (empty($str) || strlen($str) > MAX_FILE_SIZE)
        {
            $dom->clear();
            return false;
        }
        $dom->load($str, $lowercase, $stripRN);
        return $dom;
    }

// dump html dom tree
    function dump_html_tree($node, $show_attr=true, $deep=0)
    {
        $node->dump($node);
    }
}