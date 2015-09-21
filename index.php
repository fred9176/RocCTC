<?php


session_start();

require_once('RocCTC.class.php');
require_once('RocCS.class.php');


/* INITIALIZATION */
$rocCTC = new RocCTC();


/* ACTIONS */
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
switch($action){
    
    /* EXECURE SWITCH COMMAND (AJAX) */
    case 'AjaxSwitchCommand':
        $rocCTC->sendSwitchCommand($_REQUEST['switchID'], $_REQUEST['direction']);
        break;
    
    /* RETRIEVE SWITCHES STATES (AJAX) */
    case 'AjaxPanelSwitchListState' :
        echo $rocCTC->getSwitchesStateFromRocrail();
        break;
        
    /* DISPLAY PANEL */
    default : 
        $rocCTC->displayPanel();
}
