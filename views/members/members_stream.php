<div id="content-row" xmlns="http://www.w3.org/1999/html" style="min-height: 500px;">
    <div class="container">
        <!--Inner Left Part-->
        <div class="row">
            <div id="component" class="span7">
                <!--Comment Box-->
                <?php echo $render_comment_box; ?>
                <!--Comment Box-->
                <!--User Detail-->
                <div class="" id="comment_history">
                    <?php
                    //var_dump($stream_data);
                    foreach ($stream_data as $data) {
                        ?>

                        <?php
                        $user_id = isset($user_id) ? $user_id : $this->session->userdata('user_id');
                        $user_img = '/_assets/img/user/' . $user_id . '.jpg';
                        $current_user = $this->session->userdata((string)$data['stream_current_user_id']);
                        if (!file_exists(BASEPATH . $user_img)) {
                            $user_img = '/_assets/img/wall/user_3.jpg';
                        }

                        $user = $this->ion_auth->user($user_id)->row();
                        $comment = '';
                        $commentCount = '';
                        if(isset($data['comments'])) {
                            $commentCount = "(".count($data['comments']).")";
                            foreach($data['comments'] as $commentData) {
                                $replyImg  = '/_assets/img/user/' . $user_id . '.jpg';
                                if (!file_exists(BASEPATH . $replyImg)) {
                                    $replyImg = '/_assets/img/wall/user_3.jpg';
                                }
                                $comment .= '<div class="comment_post_user" id="reply_'.$commentData['_id'].'">
                                                <div class="comment_post_image">
                                                    <img src="'.$replyImg.'" alt=""/>
                                                </div>
                                                <div class="comment_post_data">
                                                    <h5><a href="/profile/'.$commentData['user_name'].'">'.$commentData['user_name'].'</a></h5>
                                                    <p>'.$commentData['message'].'</p>
                                                </div>

                                            </div>';
                            }
                        }
                        //var_dump($userInfo);
                        $stream_item = false;
                        if (isset($data['stream_shared']['stream_share_type'])) { //prevent notice -- check if object is there first
                            switch($data['stream_shared']['stream_share_type']) {
                                case 'youttube':
                                    $arrVid = $this->youtube_video_info->is_youtube_url($data['stream_message']);
                                    $stream_item = $this->wall_lib->show_new_video_share($data['stream_message'], (string)$data['_id'], $arrVid, $user->username, $user->id, $user_img, timespan($data['stream_create_date']->sec, time()) . " ago");
                                    break;
                                case 'link':
                                    //echo "URL: ".$data['stream_shared']['stream_share_URL'];
                                    //$arrLink = $this->wall_lib->get_link($data['stream_shared']['stream_share_URL']);
                                    //var_dump($arrLink);
                                    $arrLink = array (
                                        "title" => $data['stream_shared']['stream_share_title'],
                                        "description" => $data['stream_shared']['stream_share_desc'],
                                        "url" => $data['stream_shared']['stream_share_URL'],
                                    );

                                    $stream_item = $this->wall_lib->show_new_link_share($data['stream_message'], (string)$data['_id'], $arrLink, $user->username, $user->id, $user_img, timespan($data['stream_create_date']->sec, time()) . " ago");
                                    break;
                                case 'comment':
                                default:
                                    $stream_item = $this->wall_lib->show_new_share($data['stream_message'], (string)$data['_id'], $user->username, $user->id, $user_img, timespan($data['stream_create_date']->sec, time()) . " ago");
                                    break;
                            }
                        }
                        $stream_item = preg_replace('@\[WALL_REPLIES\]@',$comment,$stream_item);
                        $stream_item = preg_replace('@\[REPLY_COUNT\]@',$commentCount,$stream_item);


                        echo $stream_item;
                        ?>

                    <?php
                    }

                    ?>


                </div>
            </div>

                <div id="component" class="span5">
                    sidebar
                </div>

        </div>
    </div>
</div>
<!-- extra div to solve misplacement todo -->