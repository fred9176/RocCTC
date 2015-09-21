<?php
/*
 * This class is the core of rocCTC
 */
class RocCTC {

    public $panelID = '';
    private $panel = null;
    private $config = null;

    /**
     * CLASS INIT : GET CONFIG
     */
    function __construct() {
        $this->getConfig();
    }

    /**
     * DISPLAY CURRENT PANEL WITH PICTURE AND SWITCHES
     */
    public function displayPanel() {

        $this->getCurrentPanel();

        // Get template
        $content = file_get_contents('RocCTC.tpl');

        // Replace template content
        $content = $this->changeContent($content, 'PANEL_TITLE', $this->panelID);
        $content = $this->changeContent($content, 'DEFAULT_PANEL_ID', $this->config['default_panel']);
        $content = $this->changeContent($content, 'PANEL_PICTURE', $this->panel['picture']);
        $content = $this->changeContent($content, 'PANELS_LIST', $this->generatePanelsList());
        $content = $this->changeContent($content, 'SWITCHES_GRID', $this->generateSwitchesGrid());

        echo $content;
    }

    /**
     * GET CURRENT PANEL
     */
    function getCurrentPanel() {

        // Try to get panelID from querystring 
        // (clic on a link of the navbar)
        $this->panelID = isset($_REQUEST['panelID']) ? $_REQUEST['panelID'] : '';
        if (!array_key_exists($this->panelID, $this->config['panels'])) {
            $this->panelID = '';
        }

        //If no specific panelID found => we get the one stored in session
        if ($this->panelID == '') {
            $this->panelID = $_REQUEST['panelID'];
        }

        // If no panel found in querystring or session then get default panel
        if ($this->panelID == '') {
            $this->panelID = $this->config['default_panel'];
        }

        // Store panel in session in order to use it somewhere else 
        // without needing to retrieve it from querystring
        //$_SESSION['panelID'] = $this->panelID;

        // Retrieve panel conifuration
        $this->panel = $this->config['panels'][$this->panelID];

    }

    /*
     * GET PANEL SWITCHES STATE FROM ROCRAL
     * 
     * @return string $switchesDir json of switches for current panel
     */

    public function getSwitchesStateFromRocrail() {

        // Get current panel to get switches list
        $this->getCurrentPanel();

        // Connect to Rocrail to retrieve all switches state
        $rocCS = new RocCS();
        $RocrailSwitches = $rocCS->getSwitches();


        if (!$RocrailSwitches) {
            return '[ERR]Can\'t get switches list from Rocrail' ;
        } else {

            $switchesDir = array();
            $switchesType = array();

            // We get state for each switch of the current panel
            foreach ($this->panel['switches'] as $switchID => $switchData) {

                // Create switches direction array to return to javascript
                $switchesDir[$switchID] = $this->getSwitchDirection($RocrailSwitches[$switchID]['type'], $RocrailSwitches[$switchID]['state']);

                // Create switches type for later use
                $switchesType[$switchID] = $RocrailSwitches[$switchID]['type'];
            }
        }

        // Store switches state in session to get them later
        // to send correct switch command to Rocrail 
        $_SESSION['switchesType'] = $switchesType;

        // Return json for switches direction
        return json_encode($switchesDir);
    }

    /**
     * GENERATE PANELS LIST
     */
    private function generatePanelsList() {


        $content = '<ul>';

        foreach ($this->config['panels'] as $panelID => $panelData) {
            //$content .= '<li><a href="?panelID=' . $panelID . '" data-ajax="false" ' . ($panelID == $this->panelID ? 'class="ui-btn-active"' : '') . '>' . $panelID . '</a></li>';
            $content .= '<li><a href="?panelID=' . $panelID . '" ' . ($panelID == $this->panelID ? 'class="ui-btn-active"' : '') . '>' . $panelID . '</a></li>';
        }

        $content .= '</ul>';

        return $content;
    }

    /**
     * GENERATE SWITCHES GRID
     */
    private function generateSwitchesGrid() {
        $gridContent = '';


        // Placing switches into grid
        $switchGrid = array();

        foreach ($this->panel['switches'] as $switchID => $switchData) {
            $switchGrid[$switchData['row']][$switchData['col']] = $switchID;
        }


        // Creating each col of each row
        for ($row = 1; $row <= $this->panel['grid']['rows']; $row++) {

            $gridContent .= '<div class="gridRow">';

            for ($col = 1; $col <= $this->panel['grid']['cols']; $col++) {

                // Determine if switch is present for this cell
                $divID = '';
                $class = 'gridCell';
                $switchID = '';

                if (isset($switchGrid[$row][$col])) {
                    $switchID = $switchGrid[$row][$col];
                    $divID = 'id="' . $switchID . '"';
                    $class .= ' gridSwitch left';
                }

                // Generate Div
                $gridContent .= '<div class="' . $class . '" ' . $divID . '>' . $switchID . '</div>';
            }

            $gridContent .= '</div>';
            $gridContent .= '<div style="clear:both;" />';
        }

        return $gridContent;
    }

    /**
     * SEND A SWITCH COMMAND TO ROCRAIL ACCORDING
     */
    public function sendSwitchCommand($switchID, $direction) {
        //echo 'command : '.$switchID.' / '.$direction;
        //$direction = $direction == 'left' ? 'turnout' : 'straight';
 //print_r($_SESSION['switchesType'])   ;    
        $state = $this->getSwitchState($_SESSION['switchesType'][$switchID], $direction);
echo $switchID.'='.$state;        
        $rocCS = new RocCS();
        $rocCS->sendSwitchCommand($switchID, $state);
    }

    public function getSwitches() {
        
    }

    /*
     * READ CONFIG FILE
     */

    private function getConfig() {

        // Retrieve XML content in an array
        $xmlString = file_get_contents('RocCTC.xml');
        $xml = simplexml_load_string($xmlString);
        $json = json_encode($xml);
        $configtmp = json_decode($json, TRUE);

        // Format array
        $config = array();

        $config['default_panel'] = $configtmp['default_panel'];

        // Retrieve panels
        foreach ($configtmp['panels']['panel'] as $paneltmp) {
            $panelID = $paneltmp['@attributes']['id'];
            $panelData = array();

            $panelData['picture'] = $paneltmp['picture'];
            $panelData['grid'] = $paneltmp['grid']['@attributes'];


            // Retrieve switchs 
            // If only one switch Array from XML retrieve the node directly
            if (count($paneltmp['switches']['switch']) == 1) {
                $switchID = $paneltmp['switches']['switch']['@attributes']['id'];
                $switchData = $paneltmp['switches']['switch']['@attributes'];
                $panelData['switches'][$switchID] = $switchData;
            } else {

                foreach ($paneltmp['switches']['switch'] as $switchtmp) {

                    $switchID = $switchtmp['@attributes']['id'];
                    $switchData = $switchtmp['@attributes'];
                    unset($switchData['id']);

                    $panelData['switches'][$switchID] = $switchData;
                }
            }
            $config['panels'][$panelID] = $panelData;
        }


//        print_r($config);

        $this->config = $config;
    }

    /**
     * RREPLACE CONTENT
     */
    private function changeContent($content, $search, $replace) {
        $content = str_replace('##' . $search . '##', $replace, $content);
        return $content;
    }

    /*
     * GET SWITCH DIRECTION ACCORDING TO ROCRAIL TYPE AND STATE
     * 
     * @param string $type Type of switch (left, right, twoway)
     * @param string $state Current state (straigt, turnout)
     * 
     * @result string $direction Direction to display (left, right)
     */

    private function getSwitchDirection($type, $state) {
        switch ($type) {
            // right turnout
            case 'right' :
                $direction = $state == 'straight' ? 'left' : 'right';
                break;

            // Other types (left, twoway)
            default :

                $direction = $state == 'straight' ? 'right' : 'left';
                break;
        }

        return $direction;
    }

    /*
     * GET SWITCH STATE
     * 
     * @param string $type Type of switch (left, right, twoway)
     * @param string $direction Current state (left, right)
     * 
     * @result string $state State to set (straight, turnout)
     */

    private function getSwitchState($type, $direction) {
        switch ($type) {
            // right turnout
            case 'right' :
                $state = $direction == 'left' ? 'straight' : 'turnout';
                break;

            // Other types (left, twoway)
            default :

                $state = $direction == 'left' ? 'turnout' : 'straight';
                break;
        }

        return $state;
    }

}
