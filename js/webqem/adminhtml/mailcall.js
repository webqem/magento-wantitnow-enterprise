/**
 * Webqem Mailcall
 *
 * @category    Webqem
 * @package     Webqem_Mailcall
 * @copyright   Copyright (c) 2012 webqem (http://www.webqem.com)
 * @author      webqem
 */

jQuery(function(){
    var baseUrl=top.location.href.split('index.php');
    var baseMediaUrl=baseUrl[0]+"media/wantitnow/";
    var displayLogosSelect=jQuery('#carriers_webqemmailcall_display_logos');
    displayLogosSelect.after('<p id="mailcall_logo_overview"></p>');
    
    var logoOverview=jQuery('#mailcall_logo_overview');
    function changeLogoImg(){
        var logoImg='<img src="'+baseMediaUrl+displayLogosSelect.val()+'.png" alt="Want it now">';
        logoOverview.html(logoImg);
        //jQuery('#carriers_webqemmailcall_title').val(logoImg+' <span style="display:none;">Mail Call</span>');
    }
    
    changeLogoImg();
    displayLogosSelect.change(function(){
        changeLogoImg();
    });
    
    var usefixedcost=jQuery('#carriers_webqemmailcall_usefixedcost');
    usefixedcost.change(function(){
        changeFixedFields();
    });

    function changeFixedFields(){
        var selectedVal=usefixedcost.find("option:selected").val();
        var selectedDisabled=usefixedcost.attr('disabled');
        if(selectedVal==0){
            jQuery('#carriers_webqemmailcall_withinkms').attr('disabled',true);
            jQuery('#carriers_webqemmailcall_fixedcost').attr('disabled',true);
            jQuery('#carriers_webqemmailcall_display_wantitnow').attr('disabled',true);
        }else{
            if(!selectedDisabled){
                jQuery('#carriers_webqemmailcall_withinkms').attr('disabled',false);
                jQuery('#carriers_webqemmailcall_fixedcost').attr('disabled',false);
                jQuery('#carriers_webqemmailcall_display_wantitnow').attr('disabled',false);
            }
            
        }
    }
    changeFixedFields();
    
    var mailcallTable=jQuery('#carriers_webqemmailcall');
    var checkReadyTime=jQuery('#carriers_webqemmailcall_check_readytime');
    var readyTime=mailcallTable.find("[type='time']");
    readyTime.eq(2).find('option').remove();
    jQuery('<option value="00">00</option>').appendTo(readyTime.eq(2));

    var defaultVal=new Array();
    defaultVal[checkReadyTime.val()]=new Array(readyTime.eq(0).val(),readyTime.eq(1).val());
    defaultVal[(checkReadyTime.val()==1)?2:1]=new Array('00','00');
    
    checkReadyTime.change(function(){
        
        readyTime.eq(0).find('option').remove();
        readyTime.eq(1).find('option').remove();
        if(jQuery(this).val()==2){
            for(var i=0;i<=3;i++){
                var val='0'+i;
                var selected=defaultVal[2][0]==val?' selected':'';
                jQuery('<option value="'+val+'"'+selected+'>'+val+'</option>').appendTo(readyTime.eq(0));
            }
            for(var i=0;i<=30;i=i+30){
                var val=i<10?'0'+i:i;
                var selected=defaultVal[2][1]==val?' selected':'';
                jQuery('<option value="'+val+'"'+selected+'>'+val+'</option>').appendTo(readyTime.eq(1));
            }
        }else{
            
            for(var i=0;i<=23;i++){
                var val=i<10?'0'+i:i;   
                var selected=defaultVal[1][0]==val?' selected':'';
                jQuery('<option value="'+val+'"'+selected+'>'+val+'</option>').appendTo(readyTime.eq(0));
            }
            for(var i=0;i<=59;i++){
                var val=i<10?'0'+i:i;
                var selected=defaultVal[1][1]==val?' selected':'';
                jQuery('<option value="'+val+'"'+selected+'>'+val+'</option>').appendTo(readyTime.eq(1));
            }
        }
    });
    function changeReadytime(){
        
    }
//    var flatRate=jQuery('#carriers_flatrate_active');
//    var mailcallFlatrate=jQuery('#carriers_webqemmailcall_flatrate');
//    
//    var selectedVal=flatRate.find("option:selected").val();
//    changeMailcallFlateRate(selectedVal);
//    
//    function changeFlateRate(curVal){
//        for(var i=0;i<flatRate.find("option").length;i++){
//            if(i==curVal){
//                flatRate.get(0).options[i].selected = false;
//            }else{
//                flatRate.get(0).options[i].selected = true;  
//            }
//        }
//    }
//    flatRate.change(function(){
//        var selectedVal=jQuery(this).find("option:selected").val();
//        changeMailcallFlateRate(selectedVal);
//    });
//    
//    function changeMailcallFlateRate(curVal){
//        for(var i=0;i<mailcallFlatrate.find("option").length;i++){
//            if(i==curVal){
//                mailcallFlatrate.get(0).options[i].selected = false;  
//            }else{
//                mailcallFlatrate.get(0).options[i].selected = true;  
//            }
//        }
//    }
//    
//    mailcallFlatrate.change(function(){
//        var selectedVal=jQuery(this).find("option:selected").val();
//        changeFlateRate(selectedVal);
//    });
});