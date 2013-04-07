<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Created in JetBrains PhpStorm.
 * User: Joseff Betancourt
 * Date: 2/26/13
 * Time: 10:28 AM
 */

class model_stream extends CI_Model
{
    function __construct()
    {
        parent::__construct();
    }

    /**
     * Add stream comment method
     *
     * Will add a a comment to the stream. Handles the share function as well as the messaging. Uses an Inbox/Outbox method of streaming information to users.
     * A user will follow another users outbox stream. A persons inbox stream is reserved for private messaging. Inbox can be used to
     *  @param array[int|string]array[string]Object $dataArray - array with the stream information. Required
     * <code>
     *  $data_array = array (
        'stream_id' => $stream_id,
        'stream_origin_id' => '', //user id of original poster -- used for re-posting.
        'stream_user_to_id' => array($stream_user_to_id), //user id of poster
        'stream_user_from_id' => $stream_user_from_id, //used for private messages only
        'stream_current_user_id' => $stream_current_user_id, //user id of receiver if this is a private message
        'stream_message' => $stream_message,
        'stream_type' => $stream_type, // inbox or outbox
        'stream_create_date' => $stream_create_date,
        'stream_likes' => array(), //Leave Blank
        'stream_permission' => $stream_permission, //Public, Followers, Friends, Group, Private (Only Me)
        'stream_parent_id' => $stream_parent_id,
        'stream_children_id' => array(''), //leave blank -- no children yet. This will get updated after we post via a utility call to parent at bottom of this method.
        'stream_muted_by' => array(''), //leave blank -- no mutes yet. This will get updated via do_comment_mute
        'stream_hidden' => false, //like a draft mode -- only show for create user
        'stream_deleted' => false,
     *  'stream_shared' => array(
            'stream_share_URL' => $stream_share_URL,
            'stream_share_title' => $stream_share_title,
            'stream_share_desc' => $stream_share_desc,
            'stream_share_type' => $stream_share_type, //link, picture, poll
            'stream_share_image_id' => $stream_share_image_id,
            'stream_share_poll_id' => $stream_share_poll_id,
            )
     * );
     * </code>
     *
     *  ' $data_array['stream_current_user_id'] - Current user that this stream message belongs to. Required.
     *  ' $data_array['stream_message'] - The stream message. Required.
     *  ' $data_array['stream_user_to_id'] - 'TO' User ID  if this is an addressed stream. Defaults to current user.
     *  ' $data_array['stream_user_from_id'] - 'FROM' User ID  if this is an addressed stream. Defaults to current user.
     *  ' $data_array['stream_origin_id'] - 'Original' User ID. Defaults to current user on first entry. Never changes after.
     *  ' $data_array['stream_type'] - Determines stream mode. Defaults to Outbox. Inbox for personal/private messages.
     *  ' $data_array['stream_permission'] - View Permission of this thread entry. Public, Private, Followers Only, Friends Only, or specific group id.
     *  ' $data_array['stream_parent_id'] - If this is a reply thread the parent id. This is used to fill in the children ids. Defaults to current stream id.
     *  ' $data_array['stream_create_date'] - Time of stream create. defaults to current time date of post.
     *  ' $data_array['stream_shared']['stream_share_URL'] - shared URL.
     *  ' $data_array['stream_shared']['stream_share_title'] - Title of shared item -- may not need this.
     *  ' $data_array['stream_shared']['stream_share_desc'] - Description of shared item.
     *  ' $data_array['stream_shared']['stream_share_type'] - type of shared item. Options are link, picture, poll
     *  ' $data_array['stream_shared']['stream_share_image_id'] - Pic ID from users image library.
     *  ' $data_array['stream_shared']['stream_share_poll_id'] - Poll ID from system poll library.
     * @return $commentAdd array;
     * @access public
     * */
    function do_comment_add($data_array)
    {
        $stream_current_user_id = $data_array['stream_current_user_id'];
        $stream_message = $data_array['stream_message'];
        if (!$stream_current_user_id || !$stream_message) {
            //No from or message so not a valid stream, so exit and return false
            if (!$stream_current_user_id) {
            //No from or message so not a valid stream, so exit and return false
            throw new Exception('Stream Error 001: No "from user" id indicated in code');
            }
            if (!$stream_message) {
                //No from or message so not a valid stream, so exit and return false
                throw new Exception('Stream Error 002: No message indicated in code');
            }
        }
        $stream_id = new MongoId();
        //if not a repost add the from as the originator
        $stream_origin_uid = isset($data_array['stream_origin_id']) ? $data_array['stream_origin_id'] : $stream_current_user_id;

        $stream_type = isset($data_array['stream_type']) ? $data_array['stream_type'] : "outbox";
        //var_dump(in_array($data_array['stream_current_user_id'], $data_array['stream_user_to_id']));
        $is_private_message = false;
        $stream_user_to_id = isset($data_array['stream_user_to_id']) ? $data_array['stream_user_to_id'] : $data_array['stream_current_user_id'];
        $stream_user_from_id = isset($data_array['stream_user_from_id']) ? $data_array['stream_user_from_id'] : array($data_array['stream_current_user_id']);
        $stream_permission = isset($data_array['stream_permission']) ? $data_array['stream_permission'] : "public";

        if ($stream_user_to_id <> $stream_current_user_id) {
                if(in_array($data_array['stream_current_user_id'], $data_array['stream_user_to_id']) != null) {
                $is_private_message = true;
                $stream_permission = "private";
                //if the to and from are different then this message is private.
            }
        }

        $stream_parent_id = $data_array['stream_parent_id'] ? $data_array['stream_parent_id'] : $stream_id;
        $stream_create_date = isset($data_array['stream_create_date']) ? $data_array['stream_create_date'] : new MongoDate(time());

        $stream_share_URL = $data_array['stream_shared']['stream_share_URL'];
        $stream_share_desc = $data_array['stream_shared']['stream_share_desc'];
        $stream_share_type = $data_array['stream_shared']['stream_share_type'];
        $stream_share_image_id = $data_array['stream_shared']['stream_share_image_id'];
        $stream_share_poll_id = $data_array['stream_shared']['stream_share_poll_id'];

        //setup the query array
        $stream_schema = array(
            '_id' => $stream_id,
            'stream_origin_id' => $stream_origin_uid, //user id of original poster -- used for reposting.
            'stream_user_to_id' => $stream_user_to_id, //user id of poster
            'stream_user_from_id' => $stream_user_from_id, //used for private messages only
            'stream_current_user_id' => $stream_current_user_id, //user id of receiver if this is a private message
            'stream_message' => $stream_message,
            'stream_type' => $stream_type, // inbox or outbox
            'stream_create_date' => $stream_create_date,
            'stream_likes' => array(), //Leave Blank
            'stream_permission' => $stream_permission, //Public, Followers, Friends, Group, Private (Only Me)
            'stream_parent_id' => $stream_parent_id, //not sure if this and children are needed but leave it for future use.
            'stream_children_id' => array(), //leave blank -- no children yet. This will get updated after we post via a utility call to parent at bottom of this method.
            'stream_muted_by' => array(), //leave blank -- no mutes yet. This will get updated via do_comment_mute
            'stream_hidden' => false, //like a draft mode -- only show for create user
            'stream_deleted' => false,
            'stream_comments' => array()
        );

        if ($stream_share_URL || $stream_share_desc || $stream_share_type || $stream_share_image_id || $stream_share_poll_id) {
            //only add a share node if it's going to be used.
            $stream_shared = array(
                'stream_share_URL' => $stream_share_URL,
                'stream_share_desc' => $stream_share_desc,
                'stream_share_type' => $stream_share_type, //link, picture, poll
                'stream_share_image_id' => $stream_share_image_id, //
                'stream_share_poll_id' => $stream_share_poll_id,
            );
            $stream_schema['stream_shared'] = $stream_shared;
        }
        $commentAdd = $this->mongo_db->insert("stream",$stream_schema);
        //var_dump($stream_id." ".$stream_permission." ".$is_private_message);
        //If private message, update the receivers stream with this same message except change type from outbox to inbox.
        if ($commentAdd && $is_private_message && $stream_type == 'outbox') {
            //Todo: Add secondary table procedures here here:
            foreach($stream_user_to_id as $to_data) {
                $stream_schema['_id'] =  new MongoId();
                $stream_schema['stream_message_id'] =  new MongoId($stream_id);
                $stream_schema['stream_current_user_id'] =  $to_data;
                $stream_schema['stream_current_to_id'] =  $to_data;
                $stream_schema['stream_user_from_id'] = $stream_current_user_id;
                $stream_schema['stream_type'] = 'inbox';
                $stream_schema['stream_create_date'] = $stream_create_date; //preserve the time date between post.
                $returnVal = $this->do_comment_add($stream_schema);
                if(!$returnVal) {
                    $this->mongo_db->where(array("_id"=>$stream_id))->delete("stream",$stream_schema);
                    $this->mongo_db->where(array("stream_message_id"=>$stream_id))->delete_all("stream",$stream_schema);
                    throw new Exception("Stream Error 0031: Couldn't complete the private message");
                }
            }

            return $commentAdd;
        } elseif($commentAdd) {
            //update parent id with this self as child. Makes it easier to order later.
            if ($stream_parent_id) {
                $this->mongo_db
                    ->where(
                        array("_id" => $stream_parent_id)
                    )
                    ->push("stream_children_id", $stream_parent_id)
                    ->update('stream');
            }
            return $commentAdd;
        } else {
            //didn't work -- return a false.
            return false;
        }
    }

    function do_comment_on_stream($stream_id, $message) {
        //requires ion_auth library for user info.
        $userInfo = $this->ion_auth->user($this->session->userdata('user_id'))->row();


        $commentArray = array (
            "user_id" =>  $userInfo->id,
            "user_name" => $userInfo->username,
            "message" => $message,
            "createDate" => new MongoDate(time()),
        );

        $this->mongo_db
            ->where(
                array("_id" => $stream_id)
            )
            ->push("comments", $commentArray)
            ->update('stream');
    }

    function get_stream($current_user_wall_id = null, $stream_type = "outbox", $limit = 10, $offset = 0) {
        /*
         * Second attempt
         * Need to do folllowing:
         * get self and followers streams
         * check permissions on each item and display if match.
         */

        //setup the current viewing person. This is the person viewing the wall.
        $viewing_user_id = $this->session->userdata('user_id');

        //setup the current wall id. If blank it's the person viewing self.
        $current_user_wall_id = $current_user_wall_id ? $current_user_wall_id : $this->session->userdata('user_id');

        //get a list of $current_user_wall_id followers.
        $followers = $this->get_followers($current_user_wall_id);

        if ($viewing_user_id <> $current_user_wall_id) {
            $isFollow = $this->get_followers($current_user_wall_id,$viewing_user_id,true);
            $isFriend = $this->get_friends($current_user_wall_id,$viewing_user_id,true);
            $permissionArray = array(
                "stream_permission" => "public",
            );
            if($isFollow) {$permissionArray['stream_permission'] = "followers";}
            if($isFriend) {$permissionArray['stream_permission'] = "friends";}
        }
        //get your own streams
        $result= $this->mongo_db
            ->where(
                array (
                "stream_current_user_id" => $current_user_wall_id,
                "stream_type" => $stream_type,
                )
            )
            ->order_by(
                array(
                    'stream_create_date' => 'Desc'
                )
            )
            ->limit($limit)
            ->offset($offset)
            ->get('stream');
        //var_dump($result);
        //get streams from followers that match permissions.
        foreach ($followers as $fdata) {
            $isFriend = $this->get_friends($fdata['follower_id'],$viewing_user_id,true);
            $permissionArray = array();
            $permissionArray[0]['stream_permission'] = "public";
            $permissionArray[1]['stream_permission'] = "followers";

            if ($isFriend) {
                $permissionArray[2]['stream_permission'] = "friends";
            }

            $result2= $this->mongo_db
                ->where(
                    array (
                        "stream_type" => $stream_type,
                        "stream_current_user_id" => $fdata['follower_id'],
                        $permissionArray,
                    )
                )
                ->order_by(
                    array(
                        'stream_create_date' => 'Desc'
                    )
                )
                ->limit($limit)
                ->offset($offset)
                ->get('stream');
            $result = array_merge($result, $result2);
        }
        //var_dump($result);
        //now we order the arrays based on stream_create_date
        //This function needs work.
       /* $result = usort($result, function($a1, $a2)
        {
            //var_dump($a1['stream_create_date']->sec);
            $v1 = $a1['stream_create_date']->sec;
            $v2 = $a2['stream_create_date']->sec;
            return $v1 - $v2; // $v2 - $v1 to reverse direction
        });*/

        //todo figure out a limiting pattern for merged array.
        return $result;

    }

    function do_comment_edit($stream_data)
    {

    }

    function do_comment_delete($stream_id)
    {

    }

    function do_comment_hide($stream_id)
    {

    }
    function do_comment_mute($stream_id, $muter_id)
    {

    }
    function do_comment_like($stream_id, $muter_id)
    {

    }

    function add_stream_follower($id_to_follow, $my_id = false)
    {
        $my_id || $my_id = $this->session->userdata('user_id');

        $where = array ();
        $where['user_id'] = $my_id;
        $follower_schema = array(
            'followed_id' => '$id_to_follow',
            'follower_id' => $my_id,
            'follower_name' => $this->session->userdata('username'),
            'create_date' => new MongoDate(time()),
            'marked_as_spam_by_followed' => false,
            'blocked_by_followed' => false
        );

        $followerAdd = $this->mongo_db->insert("followers",$follower_schema);

        return $followerAdd;

    }

    function create_stream_group($group_name, $group_keywd_array = false, $grp_user_array = false, $my_id = false)
    {
        $my_id || $my_id = $this->session->userdata('user_id');
        $group_keywd_array || $group_keywd_array = array();
        $grp_user_array || $grp_user_array = array();
        $where = array ();
        $where['user_id'] = $my_id;
        $groups_schema = array(
            'created_by_id' => $my_id,
            'group_name' => $group_name,
            'group_keywords' => $group_keywd_array,
            'users_in_group' => $grp_user_array,
            'create_date' => new MongoDate(time()),
        );

        $groupAdd = $this->mongo_db->insert("groups",$groups_schema);

        return $groupAdd;

    }

    function add_group_user($group_id, $user_id)
    {
        $groupAdd = $this->mongo_db->where(array("_id" => $group_id))->push("users_in_group",$user_id)->update('groups');
        return $groupAdd;
    }

    function get_friends($my_id = false, $match_user = false, $bool_result = false)
        //a friend is someone who is following you and you are following them.
    {
       $following_me = $this->get_followers($my_id, $match_user, $bool_result);
       $me_following = $this->get_people_I_follow($my_id, $match_user, $bool_result);
       $result = array_intersect($following_me, $me_following);

        if ($result) {
            if($match_user) {
                //if your seeing if a specific person is a friend, you may want to get a simple yes no rather then an array.
                return $result;
            } elseif($bool_result) {
                return true;
            } else {
               return $result;
            }
        } else {
            return false;
        }
    }
    function get_followers($my_id = false, $match_user = false, $bool_result = false)
    {
        $my_id || $my_id = $this->session->userdata('user_id');
        $where = array ();
        $where['followed_id'] = $my_id;
        if ($match_user) {
            $where['follower_id'] = $match_user;
        }
        $followingArray = $this->mongo_db
            ->where($where)
            ->get('followers');
        if ($bool_result && $followingArray) {
            return true;
        } else {
            return $followingArray;
        }
    }

    function get_people_I_follow($my_id = false, $match_user = false, $bool_result = false)
    {
        $my_id || $my_id = $this->session->userdata('user_id');
        $where = array ();
        $where['follower_id'] = $my_id;
        if ($match_user) {
            $where['followed_id'] = $match_user;
        }
        $followingArray = $this->mongo_db
            ->where($where)
            ->get('followers');
        if ($bool_result && $followingArray) {
            return true;
        } else {
            return $followingArray;
        }

    }

    function get_group_list($my_id = false)
    {
        $my_id || $my_id = $this->session->userdata('user_id');
        $groupAdd = $this->mongo_db->where(array("created_by_id" => $my_id))->get('groups');
        return $groupAdd;
    }

    function get_group_users($group_id, $match_user_id = false)
    {
        $where = array (
            "_id" => $group_id
        );

        $wherein = array();
        if($match_user_id) {
            $wherein = array('users_in_group',array($match_user_id));
        }
        //'users_in_group', array($match_user_id)
        $groupGet = $this->mongo_db->where($where)->where_in($wherein)->get('groups');

        if($match_user_id && $groupGet) {
            return true;
        } elseif ($groupGet) {
            //return Array of groups that belong to my_id.
            return $groupGet[0]['users_in_group'];
        } else {
            return false;
        }

    }
    function do_comment_repost($stream_data, $id_only = false)
    {


        if ($id_only) {

        }

        $this->load->library('session');
        //We store the user_id into the users session on login.
        $stream_data['stream_current_user_id'] =  $this->session->userdata('user_id');
        $stream_data['stream_user_to_id'] = $this->session->userdata('user_id');
        $stream_data['stream_user_from_id'] = $this->session->userdata('user_id');
        $stream_data['stream_type'] = 'outbox';
        $returnVal = $this->do_comment_add($stream_data);
        return $returnVal;
    }

    public function get_username_from_id($username = '')
    {
        //uses the ion_auth library schema for users.

        if (empty($username))
        {
            return FALSE;
        }

        $username = new MongoRegex('/^'.$username.'$/i');
        return $this->mongo_db
            ->where('username', $username)
            ->get('users');
    }

}
