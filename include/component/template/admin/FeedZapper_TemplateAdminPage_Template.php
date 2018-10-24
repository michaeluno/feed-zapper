<?php
/**
 * Feed Zapper
 * 
 * http://en.michaeluno.jp/externals/
 * Copyright (c) 2018 Michael Uno; Licensed GPLv2
 * 
 */

/**
 * Adds the `Templates` page.
 * 
 * @since       0.0.1
 */
class FeedZapper_TemplateAdminPage_Template extends FeedZapper_AdminPage_Page_Base {


    /**
     * A user constructor.
     * 
     * @since       1
     * @return      void
     */
    public function construct( $oFactory ) {
        
        // Tabs
        new FeedZapper_TemplateAdminPage_Template_ListTable(
            $this->oFactory,
            $this->sPageSlug,
            array( 
                'tab_slug'  => 'table',
                'title'     => __( 'Installed', 'externals' ),
            )
        );
//        new FeedZapper_TemplateAdminPage_Template_GetNew(
//            $this->oFactory,
//            $this->sPageSlug,
//            array(
//                'tab_slug'  => 'get',
//                'title'     => __( 'Get New', 'externals' ),
//            )
//        );

    }   
    

        
}
