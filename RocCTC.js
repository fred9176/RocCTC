
$(document).on("pageinit", "#index", function (event) {


    // Set default switch direction for current panel according to RocRail
    
    var panelID = getURLParameter($(this).data('url'), 'panelID');
    if(panelID == 'null'){panelID = defaultPanelID;}
    
    console.log('panelIDinit='+panelID);
    
    setSwitchesState(panelID);

    // Add event when switch is clicked
    $('div.gridSwitch').bind('click', function () {

        var switchItem = $(this);
        var switchID = switchItem.attr('id');
        if (switchID != '') {

            // Get current direction
            var currentDirection = $(this).hasClass('left') ? 'left' : 'right';

            console.debug(switchID + '/' + currentDirection);

            // Direction change
            var newDirection = currentDirection == 'left' ? 'right' : 'left';

            // Send Ajax command 
            $.ajax({
                type: 'GET',
                url: 'index.php',
                data: {action: 'AjaxSwitchCommand', switchID: switchID, direction: newDirection},
                datatyle: 'html',
                cache: 'false',
                success: function (response) {
                    //console.debug(switchItem.attr('id') + '@' + newDirection);
                    switchItem.removeClass(currentDirection).addClass(newDirection);
                    $('.info').html(response);

                },
                error: function () {
                    $('.info').html('couln\'t change ' + switchID + ' to ' + newDirection);
                }
            });



        }
        else {
            $('.info').html('not a switch');
        }

    }); //End onclick
    
    
    $(document).on('pagehide', 'div', function (event, ui) { 
    var page = jQuery(event.target);
    if (page.attr('data-cache') == 'never') { 
        page.remove(); 
    }; 
});


});

// Set sitches state for current panel according to Rocrail status
function setSwitchesState(panelID) {
    console.log('debut setswitchesState ede');
    
    $.getJSON("./?action=AjaxPanelSwitchListState&panelID="+panelID, function (data) {

        //console.log("data="+data);
        $.each(data, function (switchID, direction) {
            // We remove existing direction classes and add the new one
            //console.log (switchID);
            $('#' + switchID).removeClass('left').removeClass('right').addClass(direction);
        });


    });
    
    console.log('fin');
}



function getURLParameter(url, name) {
    return decodeURI(
        (RegExp(name + '=' + '(.+?)(&|$)').exec(url)||[,null])[1]
    );
}
