var PC_AjaxCore = {
    //make our common vars PC_AjaxCore.SendAjaxRequest
    strFormName:'',
    boolViewCart:false,
    objPresentForm:null,

    /**
    *   make an ajax call
    *   @param varAjaxContent - Various content types
    *       -string curl formed string
    *       -form data direct POST/GET
    *   @return bool
    */
    SendAjaxRequest:function(varAjaxContent){
      var arrPayLoad = {};

      //check to see if it's a string
      if(typeof varAjaxContent == 'string')
        arrPayLoad = this.ConvertCurlToJson(varAjaxContent);

      //check to see if it's an array
      else if(varAjaxContent instanceof Object)
        arrPayLoad = this.ConvertFormToJson(varAjaxContent);

      //check to see if it's an object
      else if(variable.constructor === Array)
        arrPayLoad = this.ConvertFormToJson(varAjaxContent);
      //can't make empty requests
      if( arrPayLoad.length < 1 ){
        console.log( 'Ajax request failed.');
        return false;
      }
      //send our content
    	jQuery.ajax({
    		type: "post",
            url: "/wp-admin/admin-ajax.php",
            data: { action: 'PC_AjaxHandler', PC_payload: arrPayLoad},
    		success: function(varAjaxReturn){
          			return PC_AjaxCore.HandleAjaxReturns(varAjaxReturn);
    		}
    	}); //close jQuery.ajax(
    },

    /**
    *   prepare a curl string for JQuery delivery
    *   @param strCURLData
    *   @return JQuery ready JSON object
    */
    ConvertCurlToJson:function(strCurlData){
      var arrPayLoad = {};
      var arrCurlArray = strCurlData.split("&");
      //make our array
      for(i=0;i<arrCurlArray.length;i++){
       var arrDataPair = arrCurlArray[i].split("=");
       arrPayLoad[arrDataPair[0]] = arrDataPair[1];
      }
      //give back our json object
      return JSON.stringify(arrPayLoad);
    },

    /**
    *   prepare form data for JQuery delivery
    *   @param objForm
    *   @return JQuery ready JSON object
    */
    ConvertFormToJson:function(objForm){
      //go through the form and assemble our payload
      var arrPayLoad = {};
      if(objForm.elements && objForm.elements.length > 0){
          for(i=0;i<objForm.elements.length;i++){
            if(objForm.elements[i].type == 'select-multiple'){
              for(e=0;e<objForm.elements[i].length;e++){
                if(objForm.elements[i][e].selected)
                    arrPayLoad[objForm.elements[i].name] = objForm.elements[i][e].value;
              }
            }
            else{
              if(objForm.elements[i].type != 'button'){
                if(objForm.elements[i].type != 'checkbox')
                    arrPayLoad[objForm.elements[i].name] = objForm.elements[i].value;
                else{
                  if(objForm.elements[i].checked)
                    arrPayLoad[objForm.elements[i].name] = objForm.elements[i].value;
                }
              }
            }
          }
      }
      //give back our json object
      return JSON.stringify(arrPayLoad);
    },

    /**
    *   Handle ajax request returns
    *   @param varAjaxReturn
    *   @return bool
    */
    HandleAjaxReturns:function(varAjaxReturn){
      //check to see if it's a string
      if(typeof varAjaxReturn == 'string')
        varAjaxReturn = this.ConvertStringToJson(varAjaxReturn);
        if(varAjaxReturn.intIntent == 0){
              if(varAjaxReturn.strHandlerKey == "ale"){
              //varAjaxReturn.varResponse == should be alerted to user
                alert(varAjaxReturn.varResponse );
              }
              if(varAjaxReturn.strHandlerKey == "alu"){
              //varAjaxReturn.varResponse == should be alerted to user
                alert(varAjaxReturn.varResponse );
                 location.reload();
              }
              if(varAjaxReturn.strHandlerKey == "err"){
                if(document.getElementById("error1"))
                  document.getElementById("error1").innerHTML = varAjaxReturn.varResponse ;
              }
              if(varAjaxReturn.strHandlerKey == "ref"){
                 location.reload();
                 window.location = window.location.href;
              }
              if(varAjaxReturn.strHandlerKey == "uer"){
                  if(document.getElementById("formbox")){
                    var objFormBox = document.getElementById("formbox");
                    objFormBox.style.textAlign = 'center';
                    objFormBox.style.fontSize = '25px';
                    objFormBox.innerHTML = varAjaxReturn.varResponse ;
                  }
              }
              if(varAjaxReturn.strHandlerKey == "red"){
                 window.location = varAjaxReturn.varResponse ;
              }
              if(varAjaxReturn.strHandlerKey == "log"){
                 console.log(varAjaxReturn.varResponse);
              }
          }
          if(varAjaxReturn.intIntent == 1){
              if(varAjaxReturn.strHandlerKey == "lnk")
                  window.location = varAjaxReturn.varResponse ;
              if(varAjaxReturn.strHandlerKey == 'udl'){
                  var strWindowLocation = window.location.href;
                  var strBaseLocation = strWindowLocation.split("?");
                  window.location = strBaseLocation[0];
              }
              if(varAjaxReturn.strHandlerKey == "frm"){
                  DataPopup('popup-1',varAjaxReturn.varResponse );
      	    }
              if(varAjaxReturn.strHandlerKey == "gfm"){
                  DataPopup('popup-1',varAjaxReturn.varResponse );
      	    }
              if(varAjaxReturn.strHandlerKey == "urd"){//update a row background color
                var objFormData = JSON.parse(varAjaxReturn.varResponse );
               //is it there?
               if(objFormData.rowid != ''){
                  var varResponseElement;
                  //lets see if it exists first
                  if(varResponseElement = GEO(objFormData.rowid,'')){
                   //ok, it exists, lets chnage the background color
                   if(objFormData.deleterow == 'true')
                    jQuery('#'+varResponseElement.id).remove();
                   else{
                    varResponseElement.className = objFormData.classname;
                    jQuery('#'+varResponseElement.id).switchClass( objFormData.classname, "background-white", 2000, "easeInOutQuad" );
                   }
                  }
               }
              }
              if(varAjaxReturn.strHandlerKey == "aec"){//update a row background color
                var objFormData = JSON.parse(varAjaxReturn.varResponse );
               //is it there?
               if(objFormData.rowid != ''){
                  var varResponseElement;
                  //lets see if it exists first
                  if(varResponseElement = GEO(objFormData.rowid,'')){
                   //ok, it exists, lets chnage the background color
                    varResponseElement.className += ' '+objFormData.classname;
                  }
               }
              }
              if(varAjaxReturn.strHandlerKey == "ude"){
                //update the HTML now
                var objJSonReturn = jQuery.parseJSON(varAjaxReturn.varResponse );
                UpdateHTMLElements(objJSonReturn);

              }
              if(varAjaxReturn.strHandlerKey == "udc"){//update our chat
                //if we have no updates, skip this
                if(varAjaxReturn.varResponse != 'true'){
                  var objJSonReturn = jQuery.parseJSON(varAjaxReturn.varResponse );
                  UpdateChatElements(objJSonReturn);
                }

              }
              if(varAjaxReturn.strHandlerKey == "lfd"){
             // return true;
                //load form data from a JSON array
                var objFormData = JSON.parse(varAjaxReturn.varResponse );
                if(typeof this.objPresentForm  == 'object'){
                  for(i=0;i<this.objPresentForm.elements.length;i++){
                    if(this.objPresentForm.elements[i].type == 'button')
                      continue;
                    if(this.objPresentForm.elements[i].type == 'select-multiple'){
                      for(var key in objFormData.name){
                        //if(objFormData.name[key])
                        objFormData.name[key].selected = true;
                      }
                    }
                    if(this.objPresentForm.elements[i].type == 'checkbox'){
                    //console.log(objFormData[this.objPresentForm.elements[i].value]);
                        if(objFormData[this.objPresentForm.elements[i].value] != '')
                          this.objPresentForm.elements[i].checked = true;
                    }
                    else if(this.objPresentForm.elements[i].tagName == 'SELECT'){
                      var opts = this.objPresentForm.elements[i].options;
                      for(var opt, j = 0; opt = opts[j]; j++) {
                          if(opt.value == objFormData[this.objPresentForm.elements[i].name]) {
                              this.objPresentForm.elements[i].selectedIndex = j;
                              break;
                          }
                      }
                    }
                    else{
                      if(this.objPresentForm.elements[i].tagName == 'INPUT' || this.objPresentForm.elements[i].tagName == 'TEXTAREA'){  
                        if(objFormData[this.objPresentForm.elements[i].name] != '' && objFormData[this.objPresentForm.elements[i].name] != 'undefined' && typeof objFormData[this.objPresentForm.elements[i].name] != 'undefined')
                          this.objPresentForm.elements[i].value =  objFormData[this.objPresentForm.elements[i].name];
                      }
                    }
                  }
                }
                else alert('This is not an object ['+typeof this.objPresentForm+']');
              }
              if(varAjaxReturn.strHandlerKey == "rpe"){
                //repurpose an element
                var objUpdateData = JSON.parse( varAjaxReturn.varResponse );
                RepurposeElement(objUpdateData);
              }
              if(varAjaxReturn.strHandlerKey == "apd"){
              //append an existing html element.innerHTML
                var objParentElement = this.objPresentForm;
                if(objParentElement){
                   var objNewDiv = document.createElement('div');
                   objNewDiv.innerHTML = varAjaxReturn.varResponse ;
                   objParentElement.appendChild(objNewDiv);
                }
              }
              if(varAjaxReturn.strHandlerKey == "afj"){
              //append an existing html element.innerHTML and fire a javascript call
               var objUpdateData = JSON.parse(varAjaxReturn.varResponse );
                var objParentElement = this.objPresentForm;
                if(objParentElement){
                   var objNewDiv = document.createElement('div');
                   objNewDiv.innerHTML = objUpdateData.strnewdata ;
                   objParentElement.appendChild(objNewDiv);
                }
                if(typeof(objUpdateData.strjavascript) !== 'undefined'){//fire our javascript
                  //pass our function the json data        
                  window[objUpdateData.strjavascript](objUpdateData.objjsonparameters);
                }
              }
              if(varAjaxReturn.strHandlerKey == "dfu"){
               //dynamic form update - dfu requires this.objPresentForm is set first
               //let's unpack our results
               var objUpdateData = JSON.parse(varAjaxReturn.varResponse );
               if(objUpdateData.strbackgroundcolor != ''){
                this.objPresentForm.style.backgroundColor = objUpdateData.strbackgroundcolor;
                jQuery('#'+this.objPresentForm.id).switchClass( this.objPresentForm.classname, "background-white", 2000, "easeInOutQuad" );
               }
               if(objUpdateData.hasOwnProperty('strclassname') && objUpdateData.strclassname != ''){
                jQuery('#'+this.objPresentForm.id).addClass(objUpdateData.strclassname);
                jQuery('#'+this.objPresentForm.id).switchClass( objUpdateData.strclassname, "background-white", 2000, "easeInOutQuad" );
               }
               if(objUpdateData.hasOwnProperty('strmessage') && objUpdateData.strmessage != ''){
                 this.objPresentForm.innerHTML = objUpdateData.strmessage;
               }
              }
              if(varAjaxReturn.strHandlerKey == "mfm"){
               //append a select box from a form submission via ajax
               if(varAjaxReturn.varResponse == 'false'){
                  this.objPresentForm.style.backgroundColor = '#FFD2D2';
                   var objNewDiv = document.createElement('p');
                   objNewDiv.className = "userreport";
                   objNewDiv.innerHTML = 'Record NOT updated or inserted.';
                   this.objPresentForm.appendChild(objNewDiv);
               }
               else{
                  this.objPresentForm.style.backgroundColor = '#CEFFDB';
                  this.objPresentForm.innerHTML = '';
                   var objNewDiv = document.createElement('p');
                   objNewDiv.className = "userreport";
                   objNewDiv.innerHTML = 'Record updated or inserted.';
                   this.objPresentForm.appendChild(objNewDiv);
               }
               this.objPresentForm = null;
               SubmitNextForm();
              }
          }
          jQuery( document ).ready(function(  ) {
              $('[data-toggle="tooltip"]').tooltip({'placement': 'right', 'html' : 'true'});
              $('[data-toggle="popover"]').popover({trigger: 'hover','placement': 'top','html': 'true'});
          });
    },///HandleAjaxReturns

    /**
    *   given a string of data, parse it into a JSON array
    *   @param strAjaxReturn
    *   @return JSON array
    */
    ConvertStringToJson:function(varAjaxReturn){
      var objJsonObject = {};
      objJsonObject.intIntent = varAjaxReturn.slice(0,1);//output to screen 0, or update a variable = 1
      objJsonObject.strHandlerKey = varAjaxReturn.slice(1,4);//code execution key
      objJsonObject.varResponse = varAjaxReturn.slice(4,varAjaxReturn.length );//actual info to update or insert
      return objJsonObject;
    }
  };//end class