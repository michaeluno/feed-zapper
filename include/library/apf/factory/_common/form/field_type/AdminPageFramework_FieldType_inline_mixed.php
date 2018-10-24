<?php 
/**
	Admin Page Framework v3.8.18 by Michael Uno 
	Generated by PHP Class Files Script Generator <https://github.com/michaeluno/PHP-Class-Files-Script-Generator>
	<http://en.michaeluno.jp/feed-zapper>
	Copyright (c) 2013-2018, Michael Uno; Licensed under MIT <http://opensource.org/licenses/MIT> */
class FeedZapper_AdminPageFramework_FieldType__nested extends FeedZapper_AdminPageFramework_FieldType {
    public $aFieldTypeSlugs = array('_nested');
    protected $aDefaultKeys = array();
    protected function getStyles() {
        return ".feed-zapper-fieldset > .feed-zapper-fields > .feed-zapper-field.with-nested-fields > .feed-zapper-fieldset.multiple-nesting {margin-left: 2em;}.feed-zapper-fieldset > .feed-zapper-fields > .feed-zapper-field.with-nested-fields > .feed-zapper-fieldset {margin-bottom: 1em;}.with-nested-fields > .feed-zapper-fieldset.child-fieldset > .feed-zapper-child-field-title {display: inline-block;padding: 0 0 0.4em 0;}.feed-zapper-fieldset.child-fieldset > label.feed-zapper-child-field-title {display: table-row; white-space: nowrap;}";
    }
    protected function getField($aField) {
        $_oCallerForm = $aField['_caller_object'];
        $_aInlineMixedOutput = array();
        foreach ($this->getAsArray($aField['content']) as $_aChildFieldset) {
            if (is_scalar($_aChildFieldset)) {
                continue;
            }
            if (!$this->isNormalPlacement($_aChildFieldset)) {
                continue;
            }
            $_aChildFieldset = $this->getFieldsetReformattedBySubFieldIndex($_aChildFieldset, ( integer )$aField['_index'], $aField['_is_multiple_fields'], $aField);
            $_oFieldset = new FeedZapper_AdminPageFramework_Form_View___Fieldset($_aChildFieldset, $_oCallerForm->aSavedData, $_oCallerForm->getFieldErrors(), $_oCallerForm->aFieldTypeDefinitions, $_oCallerForm->oMsg, $_oCallerForm->aCallbacks);
            $_aInlineMixedOutput[] = $_oFieldset->get();
        }
        return implode('', $_aInlineMixedOutput);
    }
}
class FeedZapper_AdminPageFramework_FieldType_inline_mixed extends FeedZapper_AdminPageFramework_FieldType__nested {
    public $aFieldTypeSlugs = array('inline_mixed');
    protected $aDefaultKeys = array('label_min_width' => '', 'show_debug_info' => false,);
    protected function getStyles() {
        return ".feed-zapper-field-inline_mixed {width: 98%;}.feed-zapper-field-inline_mixed > fieldset {display: inline-block;overflow-x: visible;padding-right: 0.4em;}.feed-zapper-field-inline_mixed > fieldset > .feed-zapper-fields{display: inline;width: auto;table-layout: auto;margin: 0;padding: 0;vertical-align: middle;white-space: nowrap;}.feed-zapper-field-inline_mixed > fieldset > .feed-zapper-fields > .feed-zapper-field {float: none;clear: none;width: 100%;display: inline-block;margin-right: auto;vertical-align: middle; }.with-mixed-fields > fieldset > label {width: auto;padding: 0;}.feed-zapper-field-inline_mixed > fieldset > .feed-zapper-fields > .feed-zapper-field .feed-zapper-input-label-string {padding: 0;}.feed-zapper-field-inline_mixed > fieldset > .feed-zapper-fields > .feed-zapper-field > .feed-zapper-input-label-container,.feed-zapper-field-inline_mixed > fieldset > .feed-zapper-fields > .feed-zapper-field > * > .feed-zapper-input-label-container{padding: 0;display: inline-block;width: 100%;}.feed-zapper-field-inline_mixed > fieldset > .feed-zapper-fields > .feed-zapper-field > .feed-zapper-input-label-container > label,.feed-zapper-field-inline_mixed > fieldset > .feed-zapper-fields > .feed-zapper-field > * > .feed-zapper-input-label-container > label{display: inline-block;}.feed-zapper-field-inline_mixed > fieldset > .feed-zapper-fields > .feed-zapper-field > .feed-zapper-input-label-container > label > input,.feed-zapper-field-inline_mixed > fieldset > .feed-zapper-fields > .feed-zapper-field > * > .feed-zapper-input-label-container > label > input{display: inline-block;min-width: 100%;margin-right: auto;margin-left: auto;}.feed-zapper-field-inline_mixed .feed-zapper-input-label-container,.feed-zapper-field-inline_mixed .feed-zapper-input-label-string{min-width: 0;}";
    }
}
