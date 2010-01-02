<?
/**
 *
 * NPLib_YUI available event hooks:
 * {DATA event}
 *  - Insert: 
 *       boolean beforeInsert(formObject)
 *       boolean afterInsert()
 *  - Delete: 
 *       boolean beforeDelete(list)
 *       boolean afterDelete()
 *  - Update: 
 *       boolean beforeUpdate(record, column, oldValue, newValue)  
 *       boolean afterUpdate()
 *  - Load: 
 *       void onLoad(response), 
 *
 * {UI event}
 *  - InsertDialog: 
 *       boolean beforeInsertDialog()
 *       boolean afterInsertDialog(formObject)
 *
 */

class NP_YUI {
   
   public function __construct($className, $options = null) {
      $lower_class = strtolower($className);
      
      if (!array_key_exists("ajaxURL", $options))
          $options["ajaxURL"] = "./ajax/".$lower_class.".php";
      if (!array_key_exists("idFields", $options))
          $options["idFields"] = "id";
          
      $this->className = $className;
      $this->options = $options;

   }
   
   public function setColumns($columns) {
      $this->columns = $columns;
   }
   
   public function setDataFields($fields) {
      $this->fields = $fields;
   }
   
   public function generateJS() {
      $class = $this->className;
      $lower_class = strtolower($class);
      if ($this->options == null)
         $extraOptions = array();
      else 
         $extraOptions = $this->options;
?>
   var <?= $lower_class ?>_dataSource = null;
   var <?= $lower_class ?>_datatable = null;
   var <?= $class ?>AddDialog = null;
   
   function init_<?= $class ?>(<?= $class ?>ColumnDefs, <?= $class ?>Fields, <?= $class ?>Hooks) {
          
      <?= $lower_class ?>_dataSource = new YAHOO.util.DataSource("<?= $extraOptions["ajaxURL"] ?>" + "?");
      <?= $lower_class ?>_dataSource.connMethodPost = true;
      <?= $lower_class ?>_dataSource.responseType = YAHOO.util.DataSource.TYPE_JSON; 
      <?= $lower_class ?>_dataSource.connXhrMode = "queueRequests"; 
      <?= $lower_class ?>_dataSource.responseSchema = {
            resultsList: "Results",
            fields: <?= $class ?>Fields
      };
      <?= $lower_class ?>_dataSource.doBeforeCallback = function(oRequest , oFullResponse , oParsedResponse) {
         var count = <?= $lower_class ?>_datatable.getRecordSet().getLength();
         <?= $lower_class ?>_datatable.deleteRows(0,count);
         
         if (<?= $class ?>Hooks["onLoad"] != null)
            <?= $class ?>Hooks["onLoad"](oParsedResponse);
         
         return oParsedResponse;
      }
      
      <?= $lower_class ?>_datatable = new YAHOO.widget.DataTable("<?= $class ?>_datatable", <?= $class ?>ColumnDefs, <?= $lower_class ?>_dataSource, {initialRequest:"op=list"});
      <?= $lower_class ?>_datatable.subscribe("rowMouseoverEvent", <?= $lower_class ?>_datatable.onEventHighlightRow);
      <?= $lower_class ?>_datatable.subscribe("rowMouseoutEvent", <?= $lower_class ?>_datatable.onEventUnhighlightRow);
      <?= $lower_class ?>_datatable.subscribe("rowClickEvent", <?= $lower_class ?>_datatable.onEventSelectRow);
      <?= $lower_class ?>_datatable.subscribe("cellClickEvent", <?= $lower_class ?>_datatable.onEventShowCellEditor);
      
      var <?= $class ?>AddButton = new YAHOO.widget.Button({ 
            label:"Create new <?= $class ?>...", 
            id:"<?= $class ?>AddButton", 
            container:"<?= $class ?>_buttons" });
      <?= $class ?>AddButton.on("click", show<?= $class ?>AddDialog);
      var <?= $class ?>DelButton = new YAHOO.widget.Button({ 
            label:"Delete selected <?= $class ?>", 
            id:"<?= $class ?>DelButton", 
            container:"<?= $class ?>_buttons" });
      <?= $class ?>DelButton.on("click", delete<?= $class ?>);
      
      <?= $class ?>AddDialog = new YAHOO.widget.Dialog("<?= $class ?>_form_table", { 
            effect: {effect:YAHOO.widget.ContainerEffect.FADE, duration:0.25},
            fixedcenter: true,
            draggable: true,
            constraintoviewport: true,
            text: "Add new <?= $class ?>",
            modal: true,
            close: false,
            buttons: [ 
                { text:"Cancel", handler:defaultButtonHandler },
                { text:"Add", handler:add<?= $class ?>, isDefault:true } 
            ],
            form: YAHOO.util.Dom.get("<?= $class ?>_form")
         });
      <?= $class ?>AddDialog.setHeader("Add <?= $class ?>");

      if (<?= $class ?>Hooks["contextMenu"] != null) {
      	var <?= $class ?>ContextMenu = new YAHOO.widget.ContextMenu("<?= $lower_class ?>_contextmenu", { trigger:<?= $lower_class ?>_datatable.getTbodyEl() });
      	<?= $class ?>ContextMenu.addItems(<?= $class ?>Hooks["contextMenu"]["items"]);
      	<?= $class ?>ContextMenu.render("<?= $class ?>_datatable");
      	<?= $class ?>ContextMenu.clickEvent.subscribe(<?= $class ?>Hooks["contextMenu"]["handler"], <?= $lower_class ?>_datatable);
      }

   }
   
   function show<?= $class ?>AddDialog() {
      if (<?= $class ?>Hooks["beforeInsertDialog"] == null || <?= $class ?>Hooks["beforeInsertDialog"] != null && <?= $class ?>Hooks["beforeInsertDialog"]()) {
         <?= $class ?>AddDialog.render(document.body);
         <?= $class ?>AddDialog.show();
      }
   }
   
   function add<?= $class ?>() {
      var formObject = document.getElementById('<?= $class ?>_form');
      if (<?= $class ?>Hooks["afterInsertDialog"] == null || <?= $class ?>Hooks["afterInsertDialog"] != null && <?= $class ?>Hooks["afterInsertDialog"](formObject)) {
         if (<?= $class ?>Hooks["beforeInsert"] == null || <?= $class ?>Hooks["beforeInsert"] != null && <?= $class ?>Hooks["beforeInsert"](formObject)) {
            YAHOO.util.Connect.setForm(formObject);    
            var transaction = YAHOO.util.Connect.asyncRequest('POST', "<?= $extraOptions["ajaxURL"] ?>", {success:add<?= $class ?>Callback});
         }
      }
   }
   
   function add<?= $class ?>Callback(response) {
      
      if (response.responseText.trim() == "OK") {
         <?= $class ?>AddDialog.hide();   
         var formObject = document.getElementById('<?= $class ?>_form');
         formObject.reset();
         
         if (<?= $class ?>Hooks["afterInsert"] == null || <?= $class ?>Hooks["afterInsert"] != null && <?= $class ?>Hooks["afterInsert"]()) {
            var count = <?= $lower_class ?>_datatable.getRecordSet().getLength();
            <?= $lower_class ?>_datatable.deleteRows(0,count);
      
            <?= $lower_class ?>_dataSource.sendRequest("op=list", {success : <?= $lower_class ?>_datatable.onDataReturnAppendRows, scope: <?= $lower_class ?>_datatable})
         }
   
      } else {
         box_error("<?= $class ?>add_result", response.responseText);
      }
   }  
   
   function delete<?= $class ?>() {
      var l = <?= $lower_class ?>_datatable.getSelectedRows().length;
      if (l > 0)
         box_question("<?= $class ?>del_question", "Are you sure you want to delete the " + l + " selected <?= $lower_class ?>?", delete<?= $class ?>Confirm);
      else 
         box_warn("<?= $class ?>del_warn", "No <?= $lower_class ?> selected");
   }
     
   function delete<?= $class ?>Confirm(list) {
      if (YAHOO.lang.isObject(list)) {
         var list = "";
         var rows = <?= $lower_class ?>_datatable.getSelectedRows();
         for (var id in rows) {  
            var record = <?= $lower_class ?>_datatable.getRecord(rows[id]);
            if (record != null) {
            <?
            $fields = split(",", $extraOptions["idFields"]);
            foreach ($fields as $f) {
               if ($f != null && $f != "") {
            ?>            
               list += record.getData("<?= trim($f) ?>");
            <?
            if (count($fields) > 0) {
            ?>
               list += "#";
            <?
                  }
               }
            }
            ?>
               list = list.substring(0, list.length - 1) + ",";               
            }
         }
         
         list = list.substring(0, list.length - 1);
         this.hide();
      }
      if (<?= $class ?>Hooks["beforeDelete"] == null || <?= $class ?>Hooks["beforeDelete"] != null && <?= $class ?>Hooks["beforeDelete"](list)) {
         var postdata = "op=delete&list=" + list;
         var transaction = YAHOO.util.Connect.asyncRequest('POST', "<?= $extraOptions["ajaxURL"] ?>", {success:delete<?= $class ?>Callback}, postdata);
      }
   }
   
   function delete<?= $class ?>Callback(response) {
      if (response.responseText.trim() == "OK") {
         if (<?= $class ?>Hooks["afterDelete"] == null || <?= $class ?>Hooks["afterDelete"] != null && <?= $class ?>Hooks["afterDelete"]()) {
            var count = <?= $lower_class ?>_datatable.getRecordSet().getLength();
            <?= $lower_class ?>_datatable.deleteRows(0, count);
      
            <?= $lower_class ?>_dataSource.sendRequest("op=list", {success : <?= $lower_class ?>_datatable.onDataReturnAppendRows, scope: <?= $lower_class ?>_datatable})
         }
      } else {
         box_error("<?= $class ?>del_result", response.responseText);
      }
   }
   
   function update<?= $class ?>DatatableField(callback, newValue) {
      var record = this.getRecord();
      var column = this.getColumn();
      var oldValue = this.value;
      var datatable = this.getDataTable();
      
      if (oldValue != newValue) {
         if (<?= $class ?>Hooks["beforeUpdate"] == null || <?= $class ?>Hooks["beforeUpdate"] != null && <?= $class ?>Hooks["beforeUpdate"](record, column, oldValue, newValue)) {
            var postdata = "op=update";
            <?
            $fields = split(",", $extraOptions["idFields"]);
            foreach ($fields as $f) {
               if ($f != null && $f != "") {
            ?>
            postdata += "&<?= trim($f) ?>=" + record.getData("<?= trim($f) ?>");
            <?
               }
            }
            ?>
            postdata += "&" + column.key + "=" + newValue;
            
            YAHOO.util.Connect.asyncRequest("POST", "<?= $extraOptions["ajaxURL"] ?>", {
               success: function (o) {
                  if (<?= $class ?>Hooks["afterUpdate"] == null || <?= $class ?>Hooks["afterUpdate"] != null && <?= $class ?>Hooks["afterUpdate"]()) {
                     callback(true, newValue);
                  }
               },
               failure: function (o) {
                  box_error("<?= $class ?>field_update", o.responseText);
                  callback();
               },
               scope: this
            }, postdata);
         }
      } else {
         callback(true, oldValue);
      }
   }
<?       
   }
   
   public function generateInitJS($hooks = null) {
      $class = $this->className;
      $lower_class = strtolower($class);
      
      $columnsJS = "";
   	  foreach ($this->columns as $name => $data) {
		 $columnsJS .= '{ key: "'.$name.'",';
		 if (array_key_exists("label", $data)) 
		 	$columnsJS .= 'label: "'.$data['label'].'",';
		 if (array_key_exists("sortable", $data)) 
		 	$columnsJS .= 'sortable: '.($data['sortable'] ? "true" : "false").',';		 	
		 if (array_key_exists("editor", $data)) 
		 	$columnsJS .= 'editor: '.$data['editor'].',';
		 $columnsJS = substr($columnsJS, 0, strlen($columnsJS) - 1);
		 $columnsJS .= "},";		 	
	  }
	  $columnsJS = substr($columnsJS, 0, strlen($columnsJS) - 1);
	  
	  $fieldsJS = "";
	  foreach ($this->fields as $f) {
	  	 $fieldsJS .= '"'.$f.'",';
	  }
	  $fieldsJS = substr($fieldsJS, 0, strlen($fieldsJS) - 1);
?>
      <?= $class ?>ColumnDefs = [ <?= $columnsJS ?> ];

      <?= $class ?>Fields = [ <?= $fieldsJS ?> ];
      
      init_<?= $class ?>(<?= $class ?>ColumnDefs,<?= $class ?>Fields, <?= $hooks ?>);
<?      
   }
   
   public function generateCSS() {
   	  $class = $this->className;
      $lower_class = strtolower($class);
?>

   #<?= $class ?>_buttons {
       border: 1px solid black;
       margin-bottom:10px;
       padding:10px;
       background-color: rgb(190,211,206);
   }

   #<?= $class ?>_datatable {
      margin-top: 10px;
      margin-bottom: 10px;
   }
   
   #<?= $class ?>_form_table td {
      padding: 3px;
   }

   .yui-button#<?= $class ?>DelButton button {
      padding-left: 2em;
      background: url(<?= npadmin_setting('NP-ADMIN', 'BASE_URL') ?>/public/img/del.gif) 5% 50% no-repeat;
   }
   .yui-button#<?= $class ?>AddButton button {
      padding-left: 2em;
      background: url(<?= npadmin_setting('NP-ADMIN', 'BASE_URL') ?>/public/img/add.gif) 5% 50% no-repeat;
   }
<?      
   }
}

function NP_YUI_generateHTML($class) {
?>


<div style="visibility: hidden; display:none">
  <div id="<?= $class ?>_form_table">
     <div class="bd">
        <form id="<?= $class ?>_form">
           <table >
              <tr><td>Name:</td><td><input type="text" name="name"/></td><tr>
              <input type="hidden" name="op" value="add"/>
           </table>
        </form>
     </div>
  </div>
</div>

<?
}
?>
