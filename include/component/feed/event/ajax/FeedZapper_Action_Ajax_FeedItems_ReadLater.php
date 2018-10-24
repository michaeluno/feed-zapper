<?php
/**
 * Feed Zapper
 *
 * [PROGRAM_URI]
 * Copyright (c) 2018 Michael Uno
 *
 */

/**
 * Called when the user clicks on the Read Later button of a feed item.
 */
class FeedZapper_Action_Ajax_FeedItems_ReadLater extends FeedZapper_Action_Ajax_FeedItems_Base {

    protected $_sActionHookName     = 'wp_ajax_feed_zapper_action_read_later_feed_item'; // wp_ajax_ + action hook name // for logged-in users
    protected $_iCallbackParameters = 1;
    protected $_sSubjectPostKey     = 'read_later_feed_post_ids';
    protected $_sActionTermPrefix   = 'read_later_by_'; // + user id

}