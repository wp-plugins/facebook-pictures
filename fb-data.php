<?php
/*
Plugin Name: Facebook Pictures
Description: Retrieves public photos and albums from your Facebook Page or Profile.
Author: MiracleDevs
Version: 1.0
Tags: facebook graph api, facebook photos, data

*/
require plugin_dir_path(__FILE__) . '/facebook-php-sdk-master/src/facebook.php';
require plugin_dir_path(__FILE__) . '/fb-widget.php';

class FbDataHandler {
    var $appId;// = '414023008691674';
    var $secret;// = 'be3aa67b9180a98618f31c350f296e12';
    var $userId;// = '356968110704';
    var $currentSourceId;

    var $facebook;
    var $access_token;
    var $timespan;

    public function FbDataHandler() {

        $this->appId = get_option( 'fb_appId' );
        $this->secret = get_option( 'fb_appSecret' );

        $this->facebook = new Facebook(array(
          'appId'  => $this->appId,
          'secret' => $this->secret,
        ));

        if($_POST['fb_unlink'] == 'true')
            $this->unlink_fb_account();

        $this->init();

        if(is_admin())
            require plugin_dir_path(__FILE__) . '/templates/facebook-settings.php';
    }

    private function init() {
        $this->access_token = get_option( 'fb_token' );
        $this->userId = get_option( 'fb_userId' );
        $this->currentSourceId = get_option( 'fb_currentSourceId' );

        if ( $this->userId == NULL ) {
            // No userid, check if the user connected already but only if they are not trying to unlink the accounts
            if ( $this->facebook->getUser() != 0 && !isset($_POST['fb_unlink']) ) {
                // Got it, add it to the database
                update_option ('fb_userId', $this->facebook->getUser() );
                update_option( 'fb_currentSourceId', $this->facebook->getUser() );

                $this->userId = get_option( 'fb_userId' );
                $this->currentSourceId = get_option( 'fb_currentSourceId' );

                $this->setup_token();
            } else {
                // No user, there's nothing to do
            }
        } elseif ( $this->access_token == NULL ) {
            // We have a user, but no token
            $this->setup_token();
        } else {
            // We have everything, set the access token
            $this->facebook->setAccessToken($this->access_token);
        }
    }

    public function set_setting($setting, $value){
        if( !is_null( $this->{$setting} ) )
            $this->{$setting} = $value;
    }

    public function get_pictures($amount = 1, $offset = 0) {
        // If we don't have it saved already, we'll have to grab the pictures
        //if (false === ($photos = get_transient("fb_featured_pictures_{$amount}_{$offset}"))) {
        if(true){
            $this->timespan = $this->whenLastModified();
            // Get the list of albums we'll be getting our pictures from
            $albums = $this->get_recent_albums();
            $i = 0;
            $albums_to_include = '';

            if (empty($albums)) {
                echo 'No albums available.';
                return;
            }

            foreach ($albums as $album) {
                if ($i != 0)
                    $albums_to_include .= ' OR ';
                $albums_to_include .= 'album_object_id = ' . $album['object_id'];
                $i++;
            }


            // Now make the actual query
            $fql = "SELECT object_id, link, images, created, like_info.like_count, comment_info.comment_count, caption, src
                    FROM photo
                    WHERE ({$albums_to_include})
                        AND created > {$this->timespan}
                    ORDER BY comment_info.comment_count + like_info.like_count DESC
                    LIMIT {$amount}
                    OFFSET {$offset}";

            $param  =   array(
                'method'    => 'fql.query',
                'query'     => $fql,
                'callback'  => ''
            );
            try {
                $photos = $this->facebook->api($param);
            } catch (FacebookApiException $e) {
                echo 'No albums available.';
            }

            set_transient("fb_featured_pictures_{$amount}_{$offset}", $photos, 60 * 60 * 24);
        }
        return $photos;

    }

    public function get_albums($amount = 3) {
        if (false === ($albums = get_transient("fb_get_{$amount}_albums"))) {

            $this->timespan = $this->whenLastModified();

            $fql = "SELECT object_id, name, link, photo_count, modified, cover_object_id
                    FROM album
                    WHERE owner = {$this->currentSourceId}
                    ORDER BY modified DESC
                    LIMIT {$amount}";

            $param  =   array(
                'method'    => 'fql.query',
                'query'     => $fql,
                'callback'  => ''
            );

            $albums = $this->facebook->api($param);

            if (empty($albums)) {
                echo 'No albums available.';
                return;
            }

            foreach ($albums as &$album) {
                // Get the cover picture for each album, plus two more
                $fql = "SELECT object_id, caption, images
                        FROM photo
                        WHERE object_id = {$album['cover_object_id']}
                        ";

                $param  =   array(
                    'method'    => 'fql.query',
                    'query'     => $fql,
                    'callback'  => ''
                );

                $album['cover'] = $this->facebook->api($param);

                $fql = "SELECT object_id, caption, images
                        FROM photo
                        WHERE album_object_id = {$album['object_id']}
                        ORDER BY comment_info.comment_count + like_info.like_count DESC
                        LIMIT 2";

                $param  =   array(
                    'method'    => 'fql.query',
                    'query'     => $fql,
                    'callback'  => ''
                );

                $album['pictures'] = $this->facebook->api($param);
            }

            set_transient("fb_get_{$amount}_albums", $albums, 60 * 60 * 24);
        }
        return $albums;
    }

    private function get_recent_albums() {

        $fql = "SELECT object_id, name
                FROM album
                WHERE owner = {$this->currentSourceId} AND modified > {$this->timespan}
                ORDER BY modified DESC";

        $param  =   array(
            'method'    => 'fql.query',
            'query'     => $fql,
            'callback'  => ''
        );

    try {
        $albums = $this->facebook->api($param);
    } catch (FacebookApiException $e) {
                echo 'There was an error getting recent albums';
                $msg = $e->getResult();
                echo $msg['error_msg'];
    }

        return $albums;
    }
    /*
     * Let's make sure we have at least an album that was updated in the last week before setting the timespan to that.
     * Else we go a week before that, and so on until we get one
     */
    private function whenLastModified() {

        $fql = "SELECT modified
                FROM album
                WHERE owner = {$this->userId}
                ORDER BY modified DESC
                ";

        $param  =   array(
            'method'    => 'fql.query',
            'query'     => $fql,
            'callback'  => ''
        );

        $lastModified = $this->facebook->api($param);

        if( count( $lastModified ) >= 5 )
            $lastModified = $lastModified[5];
        else
            $lastModified = array_pop($lastModified);

        $timespan = $lastModified['modified'];

        return $timespan;

    }

    private function setup_token() {
            $this->facebook->setExtendedAccessToken();
            $this->access_token = $this->facebook->getAccessToken();
            update_option( 'fb_token', $this->access_token);
    }

    public function display_users() {

        if ($this->userId == NULL) {
            // No user, display the login button
            $params = array(
              'scope' => 'read_stream, friends_likes, user_photos, manage_pages'
            );
            echo '<a class="button-primary" href="' . $this->facebook->getLoginUrl($params) . '">Connect to Facebook</a>';
        } else {
            echo '<form method="POST" id="link-fb">';
            echo '<label>Facebook Accounts</label>';
            echo '<input type="submit" class="button-primary" value="Unlink this account" />';
            echo '<input type="hidden" name="fb_unlink" value="true">';
            echo '</form>';
            echo '<h3>Pictures from:</h3>';
            // Display the user details
            $me = $this->facebook->api('/' . $this->userId);
            $class = $this->currentSourceId == $this->userId ? "selected" : "";

            echo '<div class="facebook-accounts">';
            echo '<div class="account ' . $class . '">';
            echo '<div class="hidden">' . $this->userId . '</div>';
            echo '<img src="https://graph.facebook.com/' . $this->userId . '/picture" alt="fb_picture">';
            echo '<h2>' . $me['name'] . '</h2>';
            echo '</div>';

            $pages = $this->facebook->api('/' . $this->userId . '/accounts');
            $pages = $pages['data'];

            foreach ($pages as $page) {
                $class = $this->currentSourceId == $page["id"] ? "selected" : "";

                echo '<div class="account '. $class . '">';
                echo '<div class="hidden">' . $page['id'] . '</div>';
                echo '<img src="https://graph.facebook.com/' . $page['id'] . '/picture" alt="fb_picture">';
                echo '<h2>' . $page['name'] . '</h2>';
                echo '</div>';
            }
                echo '<div class="clear"></div>';
            echo '</div>';
        }

    }
    public function unlink_fb_account() {
        update_option( 'fb_userId', NULL);
        update_option( 'fb_currentSourceId' , NULL);
        update_option( 'fb_token', NULL);
        session_destroy();
    }
}
function initFbData() {
    global $fbdata;
    $fbdata = new FbDataHandler();
}
add_action('init', 'initFbData');
?>
