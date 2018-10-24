<?php
/**
 * Feed Zapper
 *
 * [PROGRAM_URI]
 * Copyright (c) 2018 Michael Uno
 *
 */
class FeedZapper_MetaBox_Feeds_Preview extends FeedZapper_AdminPageFramework_MetaBox {

    /*
     * Use the setUp() method to define settings of this meta box.
     */
    public function setUp() {

        /**
         * Adds setting fields in the meta box.
         */
        $this->addSettingFields(
            array(
                'field_id'          => '_feed_preview',
                'before_fieldset'   => "<div class='feed-preview-button-container'>"
                        . "<input type='submit' id='feed-preview-button' class='button button-secondary button-small' value='" . esc_attr( __( 'Load', 'feed-zapper' ) ) . "' />"
                    . "</div>"
                    . "<p id='feed-preview-error' class='hidden error-message'>Error message will be set here.</p>",
                'content'           => '<div id="feed-preview-placeholder"><p class="preview-placeholder-text">'
                        . __( 'Set a URL above.', 'feed-zapper' )
                    . '</p></div>',
            )
        );
    }

}