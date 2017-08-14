
  function GEO(parentname,ind){
    var parentelement = false;
    if(typeof(window[parentname]) == "[object]"){
    //alert("0");
      parentelement = parentname;
    }
    else{
      if(document.getElementById(parentname) && ind != "all"){
        //alert("one");
        parentelement = document.getElementById(parentname);
      }
      else if(document.getElementsByTagName(parentname) && document.getElementsByTagName(parentname).length > 0){
        //alert("2");
        if(ind == "all" || ind == "")
          parentelement = document.getElementsByTagName(parentname);
        else
          parentelement = document.getElementsByTagName(parentname)[ind];
      }
      else if(document.getElementsByName(parentname) && document.getElementsByName(parentname).length > 0){
        //alert("3");
        if(ind == "all" || ind == "")
          parentelement = document.getElementsByName(parentname);
        else
          parentelement = document.getElementsByName(parentname)[ind];
      }
      else{
        return false;
      }
    }
    return parentelement;
  }

  //copy a form
  function CopyForm(intFormId){
    if(typeof(window['PC_AjaxCore']) != "undefined")
      PC_AjaxCore.SendAjaxRequest('dir=copyform&formid='+intFormId);
  }

  //load an entire form to ax
  function SubmitSelectedForm(objForm){
   PC_AjaxCore.objPresentForm = objForm;
    PC_AjaxCore.SendAjaxRequest(objForm);
    return false;
  }

  //hold the forms of the page
  var arrMultiForms = new Array();
  var arrMasterForm = '';
  /**
  * gather all of the forms and submit them via ajax, one by one.
  * @param strMasterFormName (string) name of the parent form, which should not be submitted.
  * @return bool
  */
  var intMaxSubmits = 0;
  var intSubmissions = 0;
  function SubmitAllForms(strMasterFormName){
   arrMultiForms = GEO('form','all');
   intMaxSubmits = arrMultiForms.length;
   intSubmissions = 0;
   if(strMasterFormName.indexOf(',') > 0)
    arrMasterForm = strMasterFormName.split(',');
   else{
      if(strMasterFormName !== "" && strMasterFormName != 'undefined'){
        arrMasterForm = new Array();
        arrMasterForm[0] = strMasterFormName;
      }
   }
   SubmitNextForm();
  }
  /**
  * submit a form stored in a global variable
  * @return bool
  */
  function SubmitNextForm(){
   if(intSubmissions > intMaxSubmits)
    return false;
   intSubmissions++;
   var arrAllForms = new Array();
   var boolFormTriggered = false;
   if(arrMultiForms.length > 0){
     for(var i=0;i<arrMultiForms.length;i++){
       if(i==0 && (arrMasterForm.indexOf(arrMultiForms[i].name) < 0 || (arrMasterForm.length < 1 && arrMultiForms[i].name != "" && arrMultiForms[i].name != "surrogate"))){
         PC_AjaxCore.objPresentForm = arrMultiForms[i];
         SubmitSelectedForm(arrMultiForms[i]);
         boolFormTriggered = true;
       }
       else if(arrMasterForm.indexOf(arrMultiForms[i].name) > 0){
         //we don't want to add this to the adjusted array
         //we do nothing
       }
       else if((arrMasterForm.length < 1 && arrMultiForms[i].name == "")){
         //do not add this back to the form list, as we are filtering against nameless forms
       }
       else{
            arrAllForms[(arrAllForms.length+0)] = arrMultiForms[i];
       }
     }
     //update the master forms array
     arrMultiForms = arrAllForms;
     if(boolFormTriggered)
        return true;
     else if(arrAllForms.length > 0)
        SubmitNextForm();
     else return true;
   }
   else return false;
  }


  /**
  * load a form by ID
  * @param
  * @return bool
  */
  function LoadFormAction(varFormId,intCollectionId,intMakeSubmit,intIsForm,strFormCSS,intUseFieldSet){
    intCollectionId = (typeof(intCollectionId) == 'undefined')? 0:intCollectionId;
    intMakeSubmit = (typeof(intMakeSubmit) == 'undefined')? 1:intMakeSubmit;
    intIsForm = (typeof(intIsForm) == 'undefined')? 1:intIsForm;
    strFormCSS = (typeof(strFormCSS) == 'undefined')? 'Formtable':strFormCSS;
    intUseFieldSet = (typeof(intUseFieldSet) == 'undefined')? 1:intUseFieldSet;
    if(typeof(window['PC_AjaxCore']) != "undefined"){
      var strAjaxCall = 'dir=loadform';
      strAjaxCall += '&collectionid='+intCollectionId;
      strAjaxCall += '&makesubmit='+intMakeSubmit;
      strAjaxCall += '&isform='+intIsForm;
      strAjaxCall += '&formcss='+strFormCSS;
      strAjaxCall += '&usefieldset='+intUseFieldSet;
      if(typeof varFormId == 'string')
        strAjaxCall += '&formalias='+varFormId;
      else if(typeof varFormId == 'number')
        strAjaxCall += '&formid='+varFormId;
      else{
       console.log('no valid form data to access ['+varFormId+']');
       return false;
      }
        PC_AjaxCore.SendAjaxRequest(strAjaxCall);
    }
    return false;
  }

  /**
  * make a form into a string
  * @param objForm
  * return string
  */
  function FormToString(objForm){
    var objPairs = [];
	for ( var i = 0; i < objForm.elements.length; i++ ) {
	   var objNextElement = objForm.elements[i];
       if(objNextElement.type == 'checkbox'){
         if(!objNextElement.checked){
            continue;
         }
         if(objNextElement.name == objNextElement.value)
            objNextElement.value = 'true';
       }
	   objPairs.push(objNextElement.name + "=" + objNextElement.value);
	}
	return objPairs.join("&");
  }

  /**
  * given a string form alias find the form and return it
  * @param string strAlias alternative name for the form
  * @param int intisForm is this a form
  * @param int intMakeSubmit do we need a submit button
  * @return bool
  */
  function GetFormByAlias(strAlias,intIsForm,intMakeSubmit,strExtraData){
    intMakeSubmit = (typeof(intMakeSubmit) == 'undefined')? 1:intMakeSubmit;
    intIsForm = (typeof(intIsForm) == 'undefined')? 1:intIsForm;
    strExtraData = (typeof(strExtraData) == 'undefined' || strExtraData.length == 0)? '':'&'+strExtraData;
    if(typeof(window['PC_AjaxCore']) != "undefined")
       PC_AjaxCore.SendAjaxRequest('dir=getformbyalias&formalias='+strAlias+'&makesubmit='+intMakeSubmit+'&isform='+intIsForm+strExtraData);
    return false;
  }


  /**
  * given a string form alias find the form and return it with data
  * @param string strAlias alternative name for the form
  * @param int intisForm is this a form
  * @param int intMakeSubmit do we need a submit button
  * @return bool
  */
  function GetFormAndDataByAlias(strAlias,intDataId,intIsForm,intMakeSubmit,strExtraData){
    intMakeSubmit = (typeof(intMakeSubmit) == 'undefined')? 1:intMakeSubmit;
    intIsForm = (typeof(intIsForm) == 'undefined')? 1:intIsForm;
    strExtraData = (typeof(strExtraData) == 'undefined' || strExtraData.length == 0)? '':'&'+strExtraData;
    if(typeof(window['PC_AjaxCore']) != "undefined"){
      var strAjax = 'dir=loadformbyalias';
      strAjax += '&dataid='+intDataId;
      strAjax += '&formalias='+strAlias;
      strAjax += '&isform='+intIsForm;
      strAjax += '&makesubmit='+intMakeSubmit;
      strAjax += strExtraData;
       PC_AjaxCore.SendAjaxRequest(strAjax);
    }
    return false;
  }


  //get the referral form
  function AddAnonymousAction(strAction,intDataId,strDataCollection,strMoreData){
    if(typeof(window['PC_AjaxCore']) != "undefined"){
        if(typeof(strAction) == "undefined"){
          alert('Cannot perform this anonymous action at this time.');
          return false;
        }
        intDataId = (typeof(intDataId) == 'undefined')? 0:intDataId;
        strMoreData = (typeof(strMoreData) == 'undefined')? 0:strMoreData;
        strDataCollection = (typeof(strDataCollection) == 'undefined')? 'null':strDataCollection;
       var strAjax = 'dir=doanonymousaction';
       strAjax += '&action='+strAction;
       strAjax += '&dataid='+intDataId;
       strAjax += '&datacollection='+strDataCollection;
       strAjax += '&'+strMoreData;
       PC_AjaxCore.SendAjaxRequest(strAjax);
    }
  }

  /**
  * get the how to form subject
  * @param strSubject
  * @return bool
  */
  function GetHowToSubject(strSubject){
    if(typeof(window['PC_AjaxCore']) != "undefined"){
       var strAjax = 'dir=gethowtosubject';
       strAjax += '&howtosubject='+strSubject;
       PC_AjaxCore.SendAjaxRequest(strAjax);
    }
  }

  //modify a form element
  function ModifyFormElement(intFormElementId){
    if(typeof(window['PC_AjaxCore']) != "undefined")
       PC_AjaxCore.SendAjaxRequest('dir=modifyformelement&elementid='+intFormElementId);
    return false;
  }

  //modify a form element
  function DeleteFormElement(intFormElementId){
    var boolDeleteElement = confirm('Are you sure you want to delete this element? Click cancel if not. No harm done yet. Deletion is PERMANENT.');
    if(boolDeleteElement && typeof(window['PC_AjaxCore']) != "undefined")
       PC_AjaxCore.SendAjaxRequest('dir=deleteformelement&elementid='+intFormElementId);
    return false;
  }

  //modify a form element
  function CreateFormElement(intFormId){
    if(typeof(window['PC_AjaxCore']) != "undefined")
       PC_AjaxCore.SendAjaxRequest('dir=createformelement&formid='+intFormId);
    return false;
  }

  //modify a form
  function UpdateForm(intFormId){
    if(typeof(window['PC_AjaxCore']) != "undefined")
       PC_AjaxCore.SendAjaxRequest('dir=updateform&formid='+intFormId);
    return false;
  }

  //uninstall a feature
  function UnInstallFeature(strFeature){
    var boolDeleteElement = confirm('Are you sure you want to remove this feature? Click cancel if not. No harm done yet. Removal is PERMANENT.');
    if(boolDeleteElement && typeof(window['PC_AjaxCore']) != "undefined")
       PC_AjaxCore.SendAjaxRequest('dir=uninstallfeature&feature='+strFeature);
  }

  //install a feature
  function InstallFeature(strFeature){
    if(typeof(window['PC_AjaxCore']) != "undefined")
       PC_AjaxCore.SendAjaxRequest('dir=installfeature&feature='+strFeature);
  }

  //delete a vendor
  function DeleteVendor(intVendorId,intMapGroup){
    var boolDeleteElement = confirm('Are you sure you want to delete this record? Click cancel if not. No harm done yet. Deletion is PERMANENT.');
    intVendorId = (typeof(intVendorId) == 'undefined')? 0:intVendorId;
    intMapGroup = (typeof(intMapGroup) == 'undefined')? 0:intMapGroup;
    if(typeof(window['PC_AjaxCore']) != "undefined")
       PC_AjaxCore.SendAjaxRequest('dir=deletevendor&vendorid='+intVendorId+'&mapgroupid='+intMapGroup);
    return false;
  }

  //delete a video
  function DeleteVideo(intVideoId){
    var boolDeleteElement = confirm('Are you sure you want to delete this record? Click cancel if not. No harm done yet. Deletion is PERMANENT.');
    intVideoId = (typeof(intVideoId) == 'undefined')? 0:intVideoId;
    if(typeof(window['PC_AjaxCore']) != "undefined")
       PC_AjaxCore.SendAjaxRequest('dir=deletevideo&videoid='+intVideoId);
    return false;
  }

  /**
  * update a form from ajax
  * @param objJSonReturn
  * @return bool
  */
  function UpdateHTMLElements(objJSonReturn){
    for(i in objJSonReturn.updateelements){
     var objElement = GEO(objJSonReturn.updateelements[i].elementid,'');
     if(objElement){
      if(objElement.tagName == 'SELECT'){
        var opts = objElement.options;
        for(var opt, j = 0; opt = opts[j]; j++) {
            if(opt.value == objJSonReturn.updateelements[i].elementcontent) {
                objElement.selectedIndex = j;
                break;
            }
        }
      }
      else if(objElement.type == 'checkbox'){
        if(objElement.value != '')
           objElement.checked = true;
      }
      else if(objElement.type == 'select-multiple'){
        for(var key in objElement){
          objElement[key].selected = true;
        }
      }
      else if(objElement.tagName == 'INPUT' || objElement.tagName == 'TEXTAREA'){
        if(objElement.name != '' && objElement.name != 'undefined' && typeof objElement.name != 'undefined')
         objElement.value =  objJSonReturn.updateelements[i].elementcontent;
      }
      else{
      //assuming this is a div or other element
        objElement.innerHTML = objJSonReturn.updateelements[i].elementcontent;
      }
      //disable it now that it's updated
      if(objJSonReturn.updateelements[i].disabled == 'true')
        objElement.disabled = true;
      else objElement.disabled = false;
     }
    }
    return true;
  }


  //delete a row in a table
  function DeleteTableRow(objRow) {
    var intRowIndex = objRow.parentNode.rowIndex;
    objRow.parentNode.parentNode.parentNode.deleteRow(intRowIndex);
    return true;
  }

  //load an entire string to ax
  function SubmitSelectedString(strAjax){
    //alert(objForm.name+'###');
    PC_AjaxCore.SendAjaxRequest(strAjax);
    return false;
  }

  //add a row to a table
  function AddTableRow(strTableName,objJSonData){
    var objTable = GEO(strTableName,'');
    if(objTable){
      var intLastRow = (objTable.rows.length - 2);
      var objRow = objTable.insertRow(intLastRow);
      for(i=0;i<objJSonData.length;i++){
        var objCell = objRow.insertCell(i);
        objCell.id = objJSonData[i].id;
        objCell.className = 'grouplist';
        objCell.innerHTML = objJSonData[i].content;
      }
    }
    return true;
  }

  //enable and disable an element
  function EnableDisableElement(strElementName,boolEnable){
    var objElement = GEO(strElementName,'');
    if(objElement){
       if(boolEnable)
        objElement.disabled = false;
       else
        objElement.disabled = true;
    }
  }

  //determine if a drop down is set to null and disable the listed element if not
  function DisableNonSelectedElements(objThis,strElements){
    var boolEnable = false;
    if(objThis.value == 'null')
      var boolEnable = true;
    if(strElements.indexOf(',') > 0){
      var arrElements = strElements.split(',');
      for(var i;arrElements.length;i++)
        EnableDisableElement(arrElements[i],boolEnable);
    }
    else{
      EnableDisableElement(strElements,boolEnable);
    }
    return true;
  }
  //make all of the checkboxes on a page active
  function UpdateCheckBoxes(objThis){
    var arrCheckBoxes = GEO('input','all');
    var boolChecked = (objThis.innerHTML == 'Check all')? true:false ;
    //imgloading(true);
    for(i=0;i<arrCheckBoxes.length;i++){
      if(arrCheckBoxes[i].type == "checkbox"){
        if(arrCheckBoxes[i].checked && !boolChecked){
          arrCheckBoxes[i].click();
          arrCheckBoxes[i].checked = false;
        }
        else if(!arrCheckBoxes[i].checked && boolChecked){
          arrCheckBoxes[i].click();
          arrCheckBoxes[i].checked = true;
        }
        else{
          //do nothing
        }
      }
    }
    //imgloading(false);
      if(boolChecked)
          objThis.innerHTML = 'Uncheck all';
      else
          objThis.innerHTML = 'Check all';
  }

  //update from another form
  function UpdateFilters(objThisForm,strFromFormName){
     var objFromForm = GEO(strFromFormName,'');
     if(objFromForm){
       objThisForm.vendorid.value = objFromForm.vendorid.value;
       objThisForm.status.value = objFromForm.status.value;
       objThisForm.startdate.value = objFromForm.startdate.value;
       objThisForm.enddate.value = objFromForm.enddate.value;
     }
  }


    //Data popup creation
    function DataPopup(strPopupId,strContent){
      var objNewDiv = '';
      var objBody = GEO('body','all');
      if(objBody[0]){
        objNewDiv = document.createElement('div');
        objNewDiv.id = strPopupId+'_1';
        objNewDiv.name = strPopupId+'_1';
        objNewDiv.className = 'popups';
        objBody[0].appendChild(objNewDiv);
        objNewDiv.innerHTML = strContent;
        //FadeBackGround('');
        jQuery('[data-popup="' + strPopupId + '"]').fadeIn(350);
      }
      return true;
    }

    function CloseDataPopUp(strPopupId){
        jQuery('[data-popup="' + strPopupId + '"]').fadeOut(350);
        jQuery( "#"+strPopupId+'_1' ).remove();
        jQuery( '.popups' ).remove();
        console.log(strPopupId);
        //ResetBackGroundFade('');
    }

  function FadeBackGround(strBoxName){
      jQuery('body:nth-child(2)').animate({"opacity" : .2,backgroundColor:'transparent'}, 100);      return true;
  }

  /*
  @brief Make the background full color again
  */
  function ResetBackGroundFade(strBoxName){
   jQuery('body:nth-child(2)').animate({"opacity" : 1,backgroundColor:'transparent'}, 200);
   return true;
  }

  /**
  * expand or contract an element
  * @param strElementId
  * @return bool
  */
  function ExpandContractElement(strElementId,objThis){
    jQuery("#"+strElementId).slideToggle('slow');
    if(objThis.innerHTML == '<i class="fa fa-arrow-down">&nbsp;</i>')
        objThis.innerHTML = '<i class="fa fa-arrow-up">&nbsp;</i>';
    else    objThis.innerHTML = '<i class="fa fa-arrow-down">&nbsp;</i>';
    return true;
  }

  /**
  * slideup an element
  * @param strElementId
  * @return bool
  */
  function SlideElementUp(strElementId){
    jQuery("#"+strElementId).slideUp('slow');
    return true;
  }

  /**
  * slidedown an element
  * @param strElementId
  * @return bool
  */
  function SlideElementDown(strElementId){
    jQuery("#"+strElementId).slideDown('slow');
    return true;
  }

  /**
  * show an element
  * @param strElementId
  * @return bool
  */
  function ShowElement(strElementId){
    jQuery("#"+strElementId).show('slow');
    return true;
  }


  /**
  * detect if the keydown was an enter and submit the chat data
  * @param objForm
  * @param objEvent
  * @return bool
  */
  function DetectChatEnter(objForm,objEvent,objThis,intSessionId){
    var strAjax = 'dir=newchatmessage';
        strAjax += '&message='+objForm.chattext.value;
        strAjax += '&rowid=pc_chat_entry';
        strAjax += '&chatsession='+intSessionId;
    if(objForm.anonusername)
        strAjax += '&anonusername='+objForm.anonusername.value;
    if(objThis.tagName == 'TEXTAREA'){
      if (objEvent.which == 13 || objEvent.keyCode == 13) {
          PC_AjaxCore.SendAjaxRequest(strAjax);
          objForm.chattext.value='';
          objEvent.preventDefault();
          return false;
      }
    }
    else if(objThis.tagName == 'INPUT'){
          PC_AjaxCore.SendAjaxRequest(strAjax);
          objForm.chattext.value='';
          objEvent.preventDefault();
          return false;
    }
    else
        return true;
  }

  /**
  * given a chat ID, find the session and load the messages
  * @param strChatId
  * @return bool
  */
  function RefreshChatMessages(strChatId){
    var objChatElement = GEO(('chat_'+strChatId),'');
    var intSessionId = objChatElement.value;
    if(intSessionId > 0){
      if(typeof(window['PC_AjaxCore']) != "undefined")
        PC_AjaxCore.SendAjaxRequest('dir=loadchatcontent&chatsession='+intSessionId);
      else
        console.log('PC_AjaxCore-'+typeof(window['PC_AjaxCore']));
    }
    return true;
  }

  /**
  * set the chat interval options
  * @param boolSetAdminChecks
  * @return bool
  */
  function SetChatCheckIntervals(boolSetAdminChecks,strChatId){
    window.setInterval(function(){RefreshChatMessages(strChatId);}, 5000);
    if(boolSetAdminChecks == 'true'){
      window.setInterval(function(){RefreshSessions(strChatId);}, 10000);
    }
    return true;
  }

  /**
  * set a new chat session for an admin user
  * @param intSessionId
  * @return bool
  */
  function RefreshSessions(strChatId){
    if(typeof(window['PC_AjaxCore']) != "undefined")
      PC_AjaxCore.SendAjaxRequest('dir=loadchatsessions');
    else
      console.log('PC_AjaxCore2-'+typeof(window['PC_AjaxCore']));
    return true;
  }

  /**
  * set a new chat session for an admin user
  * @param intSessionId
  * @return bool
  */
  function SetNewAdminChat(intSessionId,strChatId){
    if(typeof(window['PC_AjaxCore']) != "undefined"){
      var objChatElement = GEO(('chat_'+strChatId),'');
      objChatElement.value = intSessionId;
      PC_AjaxCore.SendAjaxRequest('dir=loadchatcontent&ownchat=true&chatsession='+intSessionId);
    }
    else
      console.log('PC_AjaxCore does not exist-'+typeof(window['PC_AjaxCore']));
    return true;
  }

  /**
  * update our chat form accordingly
  * @param objJSonReturn
  * @return bool
  */
  function UpdateChatElements(objJSonReturn){
    for(i in objJSonReturn.updateelements){
     var objElement = GEO(objJSonReturn.updateelements[i].elementid,'');
     if(objElement){
       if((objJSonReturn.updateelements[i].hasOwnProperty('classname')) && objJSonReturn.updateelements[i].classname != ''){
          objElement.className = objJSonReturn.updateelements[i].classname;
          $('#'+objElement.id).switchClass( objJSonReturn.updateelements[i].classname, "background-white", 2000, "easeInOutQuad" );
       }
       if((objJSonReturn.updateelements[i].hasOwnProperty('addclass')) && objJSonReturn.updateelements[i].addclass != ''  && !jQuery('#'+objElement.id).hasClass(objJSonReturn.updateelements[i].addclass)){
          jQuery('#'+objElement.id).addClass(objJSonReturn.updateelements[i].addclass);
       }
       if((objJSonReturn.updateelements[i].hasOwnProperty('removeclass')) && objJSonReturn.updateelements[i].removeclass != ''){
          jQuery('#'+objElement.id).removeClass(objJSonReturn.updateelements[i].removeclass);
       }
       if((objJSonReturn.updateelements[i].hasOwnProperty('flashclass')) && objJSonReturn.updateelements[i].flashclass != ''){
          var strPreviousClass = objElement.className;
          jQuery('#'+objElement.id).addClass(objJSonReturn.updateelements[i].flashclass);
          $('#'+objElement.id).switchClass( objJSonReturn.updateelements[i].flashclass, strPreviousClass, 2000, "easeInOutQuad" );
       }
       if((objJSonReturn.updateelements[i].hasOwnProperty('newhtml')) && objJSonReturn.updateelements[i].newhtml != ''){
          objElement.innerHTML = objJSonReturn.updateelements[i].newhtml;
       }
     }   
    }
    return true;
  }

  /**
  * close and clear a chat session
  * @param intSessionId
  * @return bool
  */
  function CloseChat(intSessionId){
    if(typeof(window['PC_AjaxCore']) != "undefined")
      PC_AjaxCore.SendAjaxRequest('dir=closechat&chatsession='+intSessionId);
  }

  /**
  * set the expanded state of the chat window to expanded or collapsed
  * @param objThis
  * @return bool
  */
  function SetChatCollapseState(objThis){
    if(objThis.innerHTML == '<i class="fa fa-arrow-down">&nbsp;</i>')
        document.cookie = "pcmw_chat_collapse=0; path=/";//chat box is not collapsed
    else    document.cookie = "pcmw_chat_collapse=1; path=/";
    return true;
  }

  //return the inputs char count
    function count_tag_chars(t,e,id,count){
      var maxlen = (count)? count:500 ;
      var tempelement = document.getElementById(id);//GEO(id,'');
      if(tempelement){
        tempelement.innerHTML = 'Available ('+(maxlen - t.value.length)+') Used ('+ t.value.length +')';
      }
      return (t.value.length < maxlen);
    }

    /**
    * add a new CSS option interface
    * @param strSection
    * @param objThisForm
    * @return bool
    */
    function LoadNewCSSInterface(strSection,objThisForm){
        var intCount = jQuery('[id^=dl_'+strSection+'_]').length;
        if(typeof(window['PC_AjaxCore']) != "undefined"){
            PC_AjaxCore.objPresentForm = objThisForm;
            PC_AjaxCore.SendAjaxRequest('dir=addcssoption&sectionname='+strSection+'&sectioncount='+intCount);
        }
        return true;
    }

    /**
    * register our new CSS option input for dynamic content
    * @param objThis - the datalist input object
    * @param objJsonParameters - registered values for option population
    * @return bool
    */
    function RegisterCSSHandler(objJsonParameters){
      var objDataList = GEO(objJsonParameters.strOptionId,'');
      if(objDataList){
        objDataList.addEventListener('input', function () {
           AddAnonymousAction('getcssparams',objJsonParameters.strOptionId,this.value,'');
        });
      }
    }

    /**
    * update our css input with the selected options
    * @param objJsonParameters
    * @return bool
    */
    function RepurposeElement(objJsonParameters){
      //make sure our element exists
      var objElement = jQuery('#'+objJsonParameters.strOptionId);
      if(objElement.length){
        if(objJsonParameters.strnewtype == 'select' || objJsonParameters.strnewtype == 'select-multiple'){
         var objNewElement = jQuery('<'+objJsonParameters.strnewtype+'></'+objJsonParameters.strnewtype+'>');
         jQuery.each(objElement.get(0).attributes, function(ii, attribute){
            objNewElement.attr(attribute.nodeName, attribute.nodeValue);
         });
         for(i in objJsonParameters.arroptions){
           objNewElement.append(jQuery('<option>', {value:objJsonParameters.arroptions[i].varvalue, text:objJsonParameters.arroptions[i].strText}));
         }
         //replace our element now
         objElement.replaceWith(objNewElement);
        }
        else{
        objElement.attr('type', objJsonParameters.strnewtype);
          if(objJsonParameters.varvalue != "")
            objElement.val(objJsonParameters.varvalue);
        }
      }
      else
        console.log('Element does not exist!');
      return true;
    }

    function LoadStyleSample(strSampleId,objThisForm){
      //load our destination objects
      var objElement = jQuery('#sa_'+strSampleId+'_sample');
      var objAltElement = jQuery('#sa_'+strSampleId+'_sample_alt');
      var intElementCount = 1;
      var intCount = jQuery('[id^='+strSampleId+'_]').length;
      if(objElement){
       for(i=1;i<intCount;i++){
         var objOption = jQuery('#'+strSampleId+'_'+i);
         //verify this exists
         if(!objOption.length || objOption.val() == '')
            continue;
         var objOptionValue = jQuery('#'+strSampleId+'_'+i+'_value');
           if(objOption.val() == 'class'){
            objElement.addClass(objOptionValue.val());
           }
           else if(objAltElement && objOption.val() == 'alternate-class'){
            objAltElement.addClass(objOptionValue.val());
           }
           else{
            objElement.css(objOption.val(),objOptionValue.val());
           }
       }
      }
      return true;
    }

    function LoadBodyStyleSample(strTag,strProperty,strValue){
      //load our destination objects
      var objElement = jQuery('#sa_'+strTag+'_sample');
      if(objElement){
        objElement.css(strProperty,strValue);
      }
      else  console.log(''+strTag+' sample does not exist');
      return true;
    }

    /**
    * given an ID, remove the styling or class
    * @param strElementId
    * @return bool
    */
    function RemoveElementProperty(strElementId){
      var objElement = jQuery('#'+strElementId);
      if(objElement.length){
        var arrSampleParts = strElementId.split('_');
        var objSampleElement = jQuery('#sa_'+arrSampleParts[0]+'_sample');
        if(objSampleElement){
          if(objElement.val() == 'class')
              objSampleElement.removeAttr( 'class' );
          else if(objElement.val() == 'alternate-class'){
            var objSampleElementAlt = jQuery('#sa_'+arrSampleParts[0]+'_sample_alt');
            objSampleElementAlt.removeAttr( 'class' );
          }
          else{
           //style option
           objSampleElement.css(objElement.val(),'');
          }
        }
        objElement.parent().remove();
      }
      return true;
    }
