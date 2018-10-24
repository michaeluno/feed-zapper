<?php
/**
 * Feed Zapper
 *
 * [PROGRAM_URI]
 * Copyright (c) 2018 Michael Uno
 *
 */

/**
 * Called when the user clicks on feed item link.
 * So the items can be added to the user black list and will be blocked in the next page loads.
 */
class FeedZapper_Action_Ajax_FeedItems_Clicked extends FeedZapper_Action_Ajax_FeedItems_Base {

    protected $_sActionHookName     = 'wp_ajax_feed_zapper_action_collect_clicked_feed_items'; // wp_ajax_ + action hook name // for logged-in users
    protected $_iCallbackParameters = 1;
    protected $_sSubjectPostKey     = 'visited_feed_post_id';
    protected $_sActionTermPrefix   = 'visited_by_'; // + user id

}