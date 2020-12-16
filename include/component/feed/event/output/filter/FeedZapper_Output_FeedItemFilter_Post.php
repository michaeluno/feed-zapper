<?php
/**
 * Feed Zapper
 *
 * [PROGRAM_URI]
 * Copyright (c) 2018 Michael Uno
 *
 */

/**
 * Filters feed items fetched by feed URL.
 *
 * @since    0.0.1
 */
class FeedZapper_Output_FeedItemFilter_Post extends FeedZapper_Output_FeedItemFilter_Base {

    /**
     * Filters out items by user settings.
     * @param array $aItem
     * @param $oItem
     * @param array $aArguments
     * @return array
     * @todo    complete this method
     */
    public function replyToDropItemByUser( array $aItem, $oItem, array $aArguments ) {

        if ( empty( $aItem ) ) {
            return $aItem;
        }

        // @todo this is an example and should be handled by an external filter
//        if ( $aItem[ 'title' ] === 'This RSS feed URL is deprecated' ) {
//            return array();
//        }

        $_iUserID  = get_current_user_id();
        if ( ! $_iUserID ) {
            return $aItem;
        }

        $_iNow = time();
        $_aMuteItems = $this->getAsArray( get_user_meta( $_iUserID, '_fz_mute_items', true ) );
        foreach( $_aMuteItems as $_iTimeOut => $_aMute ) {
            // For permanent mute items
            if ( $_iTimeOut < 0 ) {
                if ( $this->___isMuted( $aItem, $_aMute ) ) {
                    return array();
                }
                continue;
            }
            // If the mute item is timed out
            if ( $_iTimeOut < $_iNow ) {
                continue;
            }
            // At this point, the mute item is in effect
            if ( $this->___isMuted( $aItem, $_aMute ) ) {
                return array();
            }
        }

        return $aItem;
    }
        private function ___isMuted( array $aItem, array $aMute ) {
            foreach( $aMute[ 'in' ] as $_sKey ) {
                $_sSubject = $this->getElement( $aItem, $_sKey, '' );
                // AND operator
                if ( false !== strpos( $aMute[ 'pattern' ], ' AND ' ) ) {
                    $_aPatterns = explode( ' AND ', $aMute[ 'pattern' ] );
                    return $this->___hasAllPatterns( $_sSubject, $_aPatterns );
                }
                // OR operator
                if ( false !== strpos( $aMute[ 'pattern' ], ' OR ' ) ) {
                    $_aPatterns = explode( ' OR ', $aMute[ 'pattern' ] );
                    return $this->___hasAtLeastOnePattern( $_sSubject, $_aPatterns );
                }
                // Normal
                if ( false !== strpos( $_sSubject, $aMute[ 'pattern' ] ) ) {
                    return true;
                }
            }
            return false;
        }
            /**
             * Attempts to find whether all the given petterns matches the subject.
             * @param $sSubject
             * @param array $aPatterns
             *
             * @return bool
             */
            private function ___hasAllPatterns( $sSubject, array $aPatterns ) {
                foreach( $aPatterns as $_sPattern ) {
                    if ( false === strpos( $sSubject, $_sPattern ) ) {
                        return false;
                    }
                }
                return true;
            }
            private function ___hasAtLeastOnePattern( $sSubject, array $aPatterns ) {
                foreach( $aPatterns as $_sPattern ) {
                    if ( false !== strpos( $sSubject, $_sPattern ) ) {
                        return true;
                    }
                }
                return false;
            }


}