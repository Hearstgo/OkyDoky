<?php

/**
 * Provide a way to manipulate a particular user
 */
class User {

    //// ATTRIBUTES

    /**
     * DB link
     */
    private $_db;

    private $_id;
    private $_nickname;
    private $_display_name;
    private $_description;
    private $_profile_picture_name;
    private $_email;
    /** not yet confirmed email */
    private $_new_email;


    //// METHODS

    // GETTERS

    /**
     * Instantiate user from an ID
     */
    public function __construct(mysqli $db, int $id) {
        $this->_db = $db;
        $this->_id = $id;
                
        if (!is_id_correct($this->_db, Config::TABLE_USER, $id)) {
            throw new InvalidID('id "' . $id . '" in table "' . Config::TABLE_USER . '" isn`t correct.');
        }

        $sql = "SELECT * FROM `%s` WHERE `id_%s` = %s";
        $sql = sprintf($sql, Config::TABLE_USER, Config::TABLE_USER, $id);
        $row = $db->query($sql)->fetch_assoc();
    
        $this->_nickname = $row['nickname'];
        $this->_display_name = $row['display_name'];
        $this->_description = $row['description'];
        $this->_profile_picture_name = $row['profile_picture'];
        $this->_email = $row['email'];
        $this->_new_email = $row['new_email'];
    }

    /**
     * Get id
     */
    public function id() : int { return $this->_id; }

    /**
     * Get nickname
     */
    public function nickname() : string { return $this->_nickname; }

    /**
     * Get display name
     */
    public function display_name() : ?string {
        if ( $this->_display_name == ""){
            return $this->_nickname;
        }else{
            return $this->_display_name;
        }
        
    }

    /**
     * Get description
     */
    public function description() : ?string {
        return htmlspecialchars_decode($this->_description); 
    }

    /**
     * Get email
     */
    public function email() : ?string { return $this->_email; }

    /**
     * Get new email
     */
    public function new_email() : ?string { return $this->_new_email; }

    /**
     * Get url to profile pic
     */
    public function profile_pic() : string {
        if (isset($this->_profile_picture_name)) {
            $url = $this->_profile_picture_name;
            $url = "/data/user/" . $url;
            return Routes::url_for($url);
        }
        return Routes::url_for("/img/img1.jpg");
    }

    //SETTERS

    /**
     * set nickname
     */
    public function set_nickname($n){
        $id=$this->_id;
        $sql="UPDATE `user` SET `nickname` = '$n' WHERE `user`.`id_user` = $id;";
        $result = $this->_db->query($sql);
        if($result){
            $this->_nickname=$n;
        }
         
    }

    /**
     * Change the display name
     *
     * @return bool True if successful.
     */
    public function set_display_name($d_n) : bool {
        
        $id=$this->_id;
        $d_n = sanitize_text($d_n);
        $sql="UPDATE `user` SET `display_name` = '$d_n' WHERE `user`.`id_user` = $id;";
        $result = $this->_db->query($sql);
        if($result){
            $this->_display_name=$d_n;
        }
        return (bool) $result;
    }

    /**
     * set description
     */
    public function set_description($d){
        $id=$this->_id;
        $d = sanitize_text($d);
        $sql="UPDATE `user` SET `description` = '$d' WHERE `user`.`id_user` = $id;";
        $result = $this->_db->query($sql);
        if($result){
            $this->_description=$d;
        }
    }

    
    // COMMUNITY INTERACTIONS

    /**
     * Get array of communities followed by user.
     * 
     * @param $flags int|null representing a Permission. 
     *        Will return only communities matching the flag(s) if specified.
     * @return Community[] Array of communities.
     * @todo limit request ?
     * 
     * eg: $myuser->get_communities(P::OWNER); // will return communities where user is the owner
     *     $myuser->get_communities(P::VIEW); // will return communities where user is not banned from
     */
    public function get_communities(?int $flags=null) : array {
        $sql = "SELECT `community` FROM `%s` WHERE `user` = %d AND `permission` != 0";
        $sql = sprintf($sql, Config::TABLE_USER_COMMUNITY, $this->id());
        $result = $this->_db->query($sql);

        if ($result) {
            for ($list = array();
                 $row = $result->fetch_row();
                 $list[] = new Community($this->_db, $row[0]));
            return $list;
        }
        return array();
    }

    /**
     * Make the user join a community
     */
    //public function join(Community $comm) : bool;

    // AUTHENTIFICATION & PROFILE

    /**
     * Send an email to verify its authenticity
     *
     * @return bool True if successfule
     */
    private function _send_verify_email() : bool {
        $token = bin2hex(random_bytes(50));
        $url = 'https://' . Config::URL_ROOT(false) . "/verify/" . $this->nickname() . "/$token";

        $sql = "UPDATE %s SET `email_token` = '%s' WHERE `id_%s` = %d";
        $sql = sprintf($sql, Config::TABLE_USER, $token, Config::TABLE_USER, $this->id());
        $result = $this->_db->query($sql);

        if ($result) {
            $sender = new MailSender($this);
            $sender->verify($url);
            return true;
        }
        return false;
    }

    /**
     * Change the email if the token is valid
     *
     * @param string $token The token sent by email.
     * @return bool True if successful.
     */
    public function validate_email_token(string $token) : bool {
        $sql = 'SELECT `new_email`,`email_token` FROM %s WHERE `id_%s` = %d';
        $sql = sprintf($sql, Config::TABLE_USER, Config::TABLE_USER, $this->id());
        $result = $this->_db->query($sql);

        $token_match = false;
        $update_ok = false;
        if ($result) {
            $row = $result->fetch_assoc();
            $token_match = $token == $row['email_token'];
            if ($token_match) {
                $sql = "UPDATE %s SET `new_email` = NULL, `email_token` = NULL, `email` = '%s', `email_verified` = 1 WHERE `id_%s` = %d";
                $sql = sprintf($sql, Config::TABLE_USER, $row['new_email'], Config::TABLE_USER, $this->id());
                echo $sql . PHP_EOL;
                $update_ok = $this->_db->query($sql);

                if ($update_ok) {
                    $this->_email = $row['new_email'];
                    $this->_new_email = null;
                }
            }
        }
        return $token_match && $update_ok;
    }

    /**
     * Change user email.
     *
     * @return bool True if successful.
     */
    public function set_email_to(string $new_email) : bool {
        $sanitized_email = filter_var($new_email, FILTER_SANITIZE_EMAIL);
        if (filter_var($sanitized_email, FILTER_VALIDATE_EMAIL)) {
            $sql = "UPDATE `%s` SET `new_email` = '%s', `email_verified` = 0 WHERE `id_%s` = %s";
            $sql = sprintf($sql, Config::TABLE_USER, $sanitized_email, Config::TABLE_USER, $this->id());
            $update_ok = $this->_db->query($sql);

            if ($update_ok) {
                $this->_new_email = $sanitized_email;
            }
            $mail_ok = $this->_send_verify_email();

            return $update_ok && $mail_ok;
        }
        return false;
    }

    /**
     * Check whenever the user email is verified
     */
    public function is_email_verified() : bool {
        $sql = "SELECT `email_verified` FROM `%s` WHERE `id_%s` = %s";
        $sql = sprintf($sql, Config::TABLE_USER, Config::TABLE_USER, $this->id());

        $result = $this->_db->query($sql);

        if ($result) {
            $is_verified = $result->fetch_assoc()['email_verified'];
            $is_verified = $is_verified == 1;
            return $is_verified;
        }

        return false;
    }

    /**
     * Check if $pwd is indeed the user's password
     *
     * @return bool True if valid.
     */
    public function is_pwd_correct(string $pwd) : bool {
        $sql = "SELECT `pwd_hash` FROM `%s` WHERE `id_%s` = %s";
        $sql = sprintf($sql, Config::TABLE_USER, Config::TABLE_USER, $this->id());
        $result = $this->_db->query($sql);

        if (!$result) { return false; } // No hash of password in DB.

        $hash = $result->fetch_assoc()['pwd_hash'];

        return password_verify($pwd, $hash);
    }

    /**
     * Change password of user
     *
     * @return bool True if successful.
     */
    public function set_pwd(string $pwd) : bool {
        $pwd_hash = password_hash($pwd, PASSWORD_DEFAULT);

        $sql = "UPDATE `%s` SET `pwd_hash` = '%s' WHERE `id_%s` = %s";
        $sql = sprintf($sql, Config::TABLE_USER, $pwd_hash, Config::TABLE_USER, $this->id());

        return $this->_db->query($sql);
    }

    /**
     * Send a link to user to reset password
     */
    //public function retrieve_pwd() : bool;

    /**
     * Change profile picture
     *
     * @param $pic array is a picture from $_FILES.
     * @return bool True if successful.
     */
    public function set_profile_picture(array $pic) : bool {
        
        // @todo change size
        if ($pic['size'] != 0 && $pic['size'] < 50000000) {

            if (!is_writable(Config::DIR_DOCUMENT)) {
                throw new NotWritable('Directory ' . Config::DIR_USER . ' is not writable');
            }

            // remove old profile picture
            $sql = "SELECT profile_picture FROM `%s` WHERE `id_%s` = %d";
            $sql = sprintf($sql, Config::TABLE_USER, Config::TABLE_USER, $this->id());
            $previous_name = $this->_db->query($sql)->fetch_row()[0];

            if(isset($previous_name)) {
                $previous_path = Config::DIR_PROFILE_PIC . $previous_name;
                if (file_exists($previous_path)) {
                    unlink($previous_path);
                }
            }

            // move tmp file to permanent path
            $ext = pathinfo($pic['name'], PATHINFO_EXTENSION); 
            $file_name = basename(sha1($this->id() . $this->nickname() . $pic['name']) . '.' . $ext);
            $file_path = Config::DIR_PROFILE_PIC . $file_name;
            move_uploaded_file($pic['tmp_name'], $file_path);

            // update DB 
            $sql = "UPDATE `%s` SET `profile_picture` = '%s' WHERE `id_%s` = %d";
            $sql = sprintf($sql, Config::TABLE_USER, $file_name, Config::TABLE_USER, $this->id());
            $this->_db->query($sql);

            // update Model
            $url = $this->_profile_picture_name = $file_name;

            return true;
        }
        return false;
    }

    /**
     * Connect user if password is correct
     *
     * If passeword is correct, the user is stored in $_SESSION['user'].
     * To check if the user is connected, use User::is_connected().
     * To access the user, use User::current().
     *
     * @return bool True if successful
     */
    public function connect(string $pwd) : bool {
        $is_correct = $this->is_pwd_correct($pwd);
        $_SESSION['user'] = $is_correct ? $this->id() : $_SESSION['user'];
        return $is_correct;
    }

    /**
     * Disconnect user
     *
     * $_SESSION['user'] will be null.
     */
    public function disconnect() : void {
        unset($_SESSION['user']);
        $_SESSION["current_community"]=0;
    }

    // POSTS INTERACTIONS

    /**
     * Get array of posts written by user
     * 
     * @param $comm Community|null if you want posts from a specific community.
     * @param $visible bool|null if the post are visible, not visible, or all.
     * @param $limit int the limit of Post to return.
     * @param $offset int the offset of posts.
     * @return Post[] an array of posts.
     */
    public function get_posts(?Community $comm = null, bool $visible = true, int $limit = 10, int $offset = 0) : array {
		$visible = $visible ? 1 : 0;
		$sql = "SELECT `id_%s` FROM `%s` WHERE `visible` = %d AND `publisher` = %d LIMIT %d OFFSET %d";
		$sql = sprintf($sql, Config::TABLE_POST, Config::TABLE_POST, $visible, $this->id(), $limit, $offset);
        if (isset($comm)) {
            $sql .= " WHERE `community` = " . $comm->id();
        }

		$result = $this->_db->query($sql);
		if($result) {
			for ($list = array();
				 $row = $result->fetch_row();
				 $list[] = new Post($this->_db, $row[0]));
			return $list;
		}

		return array();
    }

    /**
     * User giving a good evaluation for a post
     */
    public function upvote(Post $post) : bool {
        return $post->upvote($this);
    }

    /**
     * User giving a bad evaluation for a post
     */
    public function downvote(Post $post) : bool {
        return $post->downvote($this);
    }
    // PROFIL INTERACTION

    /** 
     * Gives common communities between the user and another
     * 
     * @param $user User the second user
     * @return int[] array of common community 
     */
    public function common_communities_with(User $user) : array{
        $users = array($this, $user);
        $commsArray = array();
        $common_comm = array();
        foreach($users as $u){
            $sql = "SELECT c.id_%s FROM `%s` c JOIN `%s` uc ON c.id_%s = uc.%s WHERE uc.`user` = %s ";
            $sql = sprintf($sql,Config::TABLE_COMMUNITY,Config::TABLE_COMMUNITY,Config::TABLE_USER_COMMUNITY,Config::TABLE_COMMUNITY,Config::TABLE_COMMUNITY,$u->id());
            $res = $this->_db->query($sql);
            if ($res) {
                for ($list = array();
                    $row = $res->fetch_assoc();
                    $list[] = $row['id_community']);
            }
            $commsArray[] = $list;
        }
        foreach($commsArray[0] as $c){
            if(in_array($c,$commsArray[1])){
                $common_comm[] = $GLOBALS['communities']->get_by_id((int) $c);
            }
        }
        return $common_comm;
    }

    /**
     * Check if a user is certified regarding a community
     */
    public function is_certified(Community $comm) : bool {
        $sql = "SELECT `certified` FROM `%s` WHERE `user` = %d AND `community` = %d";
        $sql = sprintf($sql, Config::TABLE_USER_COMMUNITY, $this->id(), $comm->id());

        $result = $this->_db->query($sql);

        if ($result) {
            $is = (int) $result->fetch_row()[0];
            return $is == 1;
        }
        return false;
    }
    /**
     * Certify an user in a community
     * 
     * @param Community The community where you want to be certified
     * @return bool if it worked or not
     */
    public function certify_in_comm(Community $comm){
        return $comm->certify_user($this);
    }
    
    /**
     * Uncertify an user in a community
     * 
     * @param Community The community where you want to be uncertified
     * @return bool if it worked or not
     */
    public function uncertify_in_comm(Community $comm){
        return $comm->uncertify_user($this);
    }

    /**
     * Get user's level and xpoints in a community
     * 
     * @param Community The community where you want those numbers
     * @return int[]|null the level and points of the user in the given community
     */
    public function level_in_community(Community $comm){
        $sql = sprintf("SELECT level, xpoints FROM `%s` WHERE `user` = %d AND `community` = %d",Config::TABLE_USER_COMMUNITY,$this->id(),$comm->id());
        $result = $this->_db->query($sql);
        if ($result) {
            return $result->fetch_row();
        }
        return null;
    }

    /**
     * Get user's coins in a community
     * 
     * @param Community The community where you want those numbers
     * @return int[]|null the coins of the user in the given community
     */
    public function coins_in_community(Community $comm){
        $sql = sprintf("SELECT coins FROM `%s` WHERE `user` = %d AND `community` = %d",Config::TABLE_USER_COMMUNITY,$this->id(),$comm->id());
        $result = $this->_db->query($sql);
        if ($result) {
            return $result->fetch_row()[0];
        }
        return null;
    }

    /**
     * Add coins to the user in a community
     * 
     * @param Community The community where you want to update those numbers
     * @param int the coins to add
     * @return bool if it worked or not
     */
    public function add_coins_in_community(Community $comm, int $coins){
        $sql = sprintf("UPDATE `%s` SET coins = (coins + %d) WHERE `user` = %d AND `community` = %d",Config::TABLE_USER_COMMUNITY,$coins,$this->id(),$comm->id());
        $result = $this->_db->query($sql);
        if ($result) {
            return true;
        }
        return false;
    }

    /**
     * Say if user can collect daily coins in a community
     * 
     * @param Community The community where you want to update those numbers
     * @return bool if yes or not its possible
     */
    public function can_collect_daily_coins_in_community(Community $comm){
        $test = sprintf("SELECT DATEDIFF(NOW(), last_collect) FROM `%s` WHERE `user` = %d AND `community` = %d",Config::TABLE_USER_COMMUNITY,$this->id(),$comm->id());
        $res = $this->_db->query($test);
        if($res) {
            return $res->fetch_row()[0]>0;
        }
        return false;
    }

    /**
     * Collect daily coins in a community
     * 
     * @param Community The community where you want to update those numbers
     * @return bool if yes or not its possible
     */
    public function collect_daily_coins_in_community(Community $comm){
        if($this->can_collect_daily_coins_in_community($comm)) {
            $this->add_coins_in_community($comm,5);
            $sql = sprintf("UPDATE `%s` SET last_collect = NOW() WHERE `user` = %d AND `community` = %d",Config::TABLE_USER_COMMUNITY,$this->id(),$comm->id());
            $res = $this->_db->query($sql);
            return true;
        }
        return false;
    }

    /**
     * Say if user can collect daily coins in a community
     * 
     * @param Community The community where you want to update those numbers
     * @return bool if yes or not its possible
     */
    public function can_collect_daily_coins_at_least_one(){
        $cs = $this->get_communities();
        $canYou = false;
        foreach ($cs as $c) {
            if($this->can_collect_daily_coins_in_community($c)) {
                $canYou=true;
            }
        }
        return $canYou;
    }

    /**
     * Collect all daily coins
     * 
     * @return int number of collected dailies
     */
    public function collect_all_dailies(){
        $cs = $this->get_communities();
        foreach ($cs as $c) {
            $this->collect_daily_coins_in_community($c);
        }
    }


    /**
     * Add xpoints to the user in a community and if successful, updates the level if needed
     * 
     * @param Community The community where you want to update those numbers
     * @param int the points to add
     * @return bool if it worked or not
     */
    public function add_points_in_community(Community $comm, int $points){
        $sql = sprintf("UPDATE `%s` SET xpoints = (xpoints + %d) WHERE `user` = %d AND `community` = %d",Config::TABLE_USER_COMMUNITY,$points,$this->id(),$comm->id());
        $result = $this->_db->query($sql);
        if ($result) {
            $this->update_level_in_community($comm);
            return true;
        }
        return false;
    }

    /**
     * Updates the level of the user in a given community
     * 
     * @param Community The community where you want to update those numbers
     * @return bool if it updated or not
     */
    public function update_level_in_community(Community $comm){
        $tabLvl = $this->level_in_community($comm);
        $pointsToReach = User::hmptlvlup($tabLvl[0]);
        if ($pointsToReach <= $tabLvl[1]) {
            $sql = sprintf("UPDATE `%s` SET level = (level + 1), xpoints = (xpoints - %d) WHERE `user` = %d AND `community` = %d",Config::TABLE_USER_COMMUNITY,$pointsToReach,$this->id(),$comm->id());
            $result = $this->_db->query($sql);
            if ($result) {
                $this->update_level_in_community($comm);
            }
            return true;
        }
        elseif (0 > $tabLvl[1]) {
            if ($tabLvl[0] == 1) {
                $sql = sprintf("UPDATE `%s` SET level = 1, xpoints = 0 WHERE `user` = %d AND `community` = %d",Config::TABLE_USER_COMMUNITY,$this->id(),$comm->id());
                $result = $this->_db->query($sql);
            } else {
                $sql = sprintf("UPDATE `%s` SET level = (level - 1), xpoints = (xpoints + %d) WHERE `user` = %d AND `community` = %d",Config::TABLE_USER_COMMUNITY,User::hmptlvlup($tabLvl[0]-1),$this->id(),$comm->id());
                $result = $this->_db->query($sql);
                if ($result) {
                    $this->update_level_in_community($comm);
                }
            }
            return true;
        }
        return false;
    }


    /**
     * Add a friend to this user
     *
     * @param $u User The user aimed to be friend by this user
     * @return bool If it worked or not
     */
    public function add_friend(User $u) {
        $try = $GLOBALS['notifications']->get_friend_notif_by_sender_and_receiver(User::current(),$u);
        if ($this->is_friend($u)) {
            if($try != null) {
                $GLOBALS['notifications']->delete_notif($try);
            }
            return false;
        }
        if ($u->asked_to_be_friend($this)) {
            if($try != null) {
                $GLOBALS['notifications']->delete_notif($try);
            }
            return $u->become_friend($this);
        }
        if ($this->asked_to_be_friend($u)) {
            if($try != null) {
                $GLOBALS['notifications']->delete_notif($try);
            }
            return $this->remove_friend($u);
        }
        else {
            if($try == null) {
                $GLOBALS['notifications']->send_notif("friend",$u);
            }
            return $this->ask_user_to_be_friend($u);
        }
    }
    
    /**
     * Say if a user is a friend of this user
     *
     * @param $u User The user aimed to be friend by this user
     * @return bool If yes or no
     */
    public function is_friend(User $u) {
        $sql = sprintf("SELECT * FROM `%s` WHERE ( (`user1` = %d AND `user2` = %d) || (`user1` = %d AND `user2` = %d) ) AND `hasAccepted` = 1",Config::TABLE_FRIEND,$this->id(),$u->id(),$u->id(),$this->id());
        $result = $this->_db->query($sql);
        if ($result) {
            if ($result->fetch_assoc()) return True;
            else return False;
        }
        return False;
    }
    
    /**
     * Say if a user asked this user to be friend
     *
     * @param $u User The user aimed to be friend by this user
     * @return bool If yes or no
     */
    public function asked_to_be_friend(User $u) {
        $sql = sprintf("SELECT * FROM `%s` WHERE `user1` = %d AND `user2` = %d AND `hasAccepted` = 0",Config::TABLE_FRIEND,$this->id(),$u->id());
        $result = $this->_db->query($sql);
        if ($result) {
            if ($result->fetch_assoc()) return True;
            else return False;
        }
        return False;
    }

    /**
     * Become friend with the given user
     *
     * @param $u User The given user
     * @return bool If it worked or not
     */
    public function become_friend(User $u) {
        $sql = sprintf("UPDATE `%s` SET `hasAccepted` = 1, `ask_date` = NOW() WHERE `user1` = %d AND `user2` = %d",Config::TABLE_FRIEND,$this->id(),$u->id());
        return $this->_db->query($sql);
    }

    /**
     * Remove friend with someone else
     *
     * @param $u User The given user
     * @return bool If it worked or not
     */
    public function remove_friend(User $u) {
        $sql = sprintf("DELETE FROM `%s` WHERE ( (`user1` = %d AND `user2` = %d) || (`user1` = %d AND `user2` = %d) )",Config::TABLE_FRIEND,$this->id(),$u->id(),$u->id(),$this->id());
        return $this->_db->query($sql);
    }

    /**
     * Create a friend request to a given user
     *
     * @param $u User The given user
     * @return bool If it worked or not
     */
    public function ask_user_to_be_friend(User $u) {
        $sql = sprintf("INSERT INTO `%s` (`user1`, `user2`) VALUES (%d, %d)",Config::TABLE_FRIEND,$this->id(),$u->id());
        return $this->_db->query($sql);
    }

    /**
     * Get all friends (or friend requests) from this user
     *
     * @param $friendRequests bool if we need friend requests or friends
     * @return User[] Friends (or friend requests) from this user
     */
    public function get_all_friends(bool $friendRequests = false) {
        $sql = sprintf("SELECT `user1`, `user2` FROM `%s` WHERE (`user1` = %d OR `user2` = %d) AND `hasAccepted` = %d ORDER BY `ask_date` DESC",Config::TABLE_FRIEND,$this->id(),$this->id(),!$friendRequests);
        $result = $this->_db->query($sql);
        if ($result) {
            for ($list = array();
                    $row = $result->fetch_assoc();
                    $list[] = $row['user1'] == $this->id() ? new User($GLOBALS['db'],$row['user2']) : new User($GLOBALS['db'],$row['user1']));
            return $list;
        }
        return array();
    }


    /**
     * Search friends (or friend requests) from this user
     *
     * @param $research String the search term
     * @param $friendRequests bool if we need friend requests or friends
     * @return User[] Friends (or friend requests) from this user
     */
    public function search_by_friends(String $research, bool $friendRequests = false) {
        $sql = sprintf("SELECT f.`user1`, f.`user2` FROM `%s` f JOIN `%s` u1 ON f.`user1` = u1.`id_%s` JOIN `%s` u2 ON f.`user2` = u2.`id_%s` WHERE ( ( (u1.nickname LIKE '%%$research%%' OR u1.display_name LIKE '%%$research%%') AND `user2` = %d) OR (`user1` = %d AND (u2.nickname LIKE '%%$research%%' OR u2.display_name LIKE '%%$research%%') ) ) AND `hasAccepted` = %d", Config::TABLE_FRIEND, Config::TABLE_USER, Config::TABLE_USER, Config::TABLE_USER, Config::TABLE_USER, $this->id(), $this->id(), !$friendRequests);
        $result = $this->_db->query($sql);
        if ($result) {
            for ($list = array();
                    $row = $result->fetch_assoc();
                    $list[] = $row['user1'] == $this->id() ? new User($GLOBALS['db'],$row['user2']) : new User($GLOBALS['db'],$row['user1']));
            return $list;
        }
        return array();
    }

    

    /**
     * Get the permission object, that represent the permission of a user on a community
     *
     * @param $comm Community The community you want the permission of.
     * @return Permission|null The object representing the permission of $this on $comm. null if fails.
     */
    public function perm(Community $comm) : ?Permission {
        $sql = "SELECT permission FROM `%s` WHERE `user` = %d AND `community` = %d";
        $sql = sprintf($sql, Config::TABLE_USER_COMMUNITY, $this->id(), $comm->id());

        $result = $this->_db->query($sql);

        if ($result) {
            $nb = (int) $result->fetch_row()[0];
            return $nb >= 0 ? new Permission($nb) : null;
        }
        return null;
    }

    /**
     * Set a permission (or several) for a user on a community
     *
     * @param bool True if successful.
     */
    public function set_perm(Community $comm, Permission $p) : bool {
        $nb = $p->get();
        $sql = "UPDATE `%s` SET `permission` = %d WHERE `user` = %d AND `community` = %d";
        $sql = sprintf($sql, Config::TABLE_USER_COMMUNITY, $nb, $this->id(), $comm->id());
        return (bool) $this->_db->query($sql);
    }

    /**
     * Stringify to `(id) nickname` format. For debug purpose.
     */
    public function __toString() : string {
        return '('.$this->id().') '. $this->nickname();
    }
    /**
     * Test if two users are the same
     * 
     * @param User the second user
     * @return bool if they are the same or not
     */

    public function equals(User $user) : bool {
        return $this->id() === $user->id();
    }

    //// STATIC

    /**
     * Return true whenever a user is connected
     */
    public static function is_connected() : bool {
        return isset($_SESSION['user']) ? gettype($_SESSION['user']) == "integer" : false;
    }

    public static function hmptlvlup(int $actualLvl) : int {
        return 325 * (pow(1.041, ($actualLvl + 1))) - 188;
    }

    /**
     * Get current logged in user
     *
     * @return User|null Depending if a user is connected for this session or not
     */
    public static function current() : ?User {
        // Do not return session var directly, it may not be User|null if overwritten
        return self::is_connected() ? new User($GLOBALS['db'], $_SESSION['user']) : null; 
    }
}
