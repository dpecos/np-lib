<?


class NP_YUI_Assignement {
   
   public function __construct($assignableClass, $targetClass) {
      $this->assignableClass = $assignableClass;
      $this->targetClass = $targetClass;
   }
	
   public function setAssignementName($assignementName) {
   	$this->assignementName=$assignementName;
   }
   
   public function setAssignableClassNameField($assignableClassName) {
   	   $this->assignable_class_name = $assignableClassName;
   }
	
   public function setAssignableClassIdField($assignableClassId) {
   	   $this->assignable_class_id = $assignableClassId;
   }
   
   public function setTargetClassNameField($targetClassName) {
   	   $this->target_class_name = $targetClassName;
   }
	
   public function setTargetClassIdField($targetClassId) {
   	   $this->target_class_id = $targetClassId;
   }
   
   public function setTargetClassLabel($label) {
   	   $this->target_class_label = $label;
   }
   
   public function setAssignableClassUnassignedLabel($label) {
   	   $this->assignable_class_unassigned_label = $label;
   }
   
   public function setAssignableClassAssignedLabel($label) {
   	   $this->assignable_class_assigned_label = $label;
   }
   
   public function onLoad($response) {
   	   $target_class = $this->assignementName.$this->targetClass;
   	   $lower_target_class = strtolower($target_class);?>
   	   <?=$lower_target_class?>OnLoad(<?=$response?>);
   	   <?
   }
  
   public function generateContentHTML() {
   	   $target_class = $this->assignementName.$this->targetClass;
   	   $lower_target_class = strtolower($target_class);
   	   $assignable_class = $this->assignementName.$this->assignableClass;
       $lower_assignable_class = strtolower($assignable_class);
       ?>
   	   <div class="buttonBox" id="<?=$lower_assignable_class?>s_buttons"></div>
           <?= $this->target_class_label ?> <input type="button" id="<?=$lower_target_class?>_list" name="<?= $lower_target_class ?>_list"/>
           <select id="<?= $lower_target_class?>_list_select" name="<?= $lower_target_class?>_list_select"></select>
           <table id="<?=$lower_assignable_class?>s_form_table">
              <tr><td>
                 <h3><?= $this->assignable_class_unassigned_label ?></h3>
                 <ul id="unassigned_<?=$lower_assignable_class?>s" class="draglist"></ul>
              </td><td>
                 <h3><?= $this->assignable_class_assigned_label ?></h3>
                 <ul id="assigned_<?=$lower_assignable_class?>s" class="draglist"></ul>
              </td></tr>
           </table>
   
   	   <?
   }
   
   public static function CommonJS() {
?> 
   DDList = function(id, sElement, config) {
		    DDList.superclass.constructor.call(this, id, sElement, config);

		    var el = this.getDragEl();
		    YAHOO.util.Dom.setStyle(el, "opacity", 0.67); // The proxy is slightly transparent

		    this.goingUp = false;
		    this.lastY = 0;
		};

		YAHOO.extend(DDList, YAHOO.util.DDProxy, {

		    startDrag: function(x, y) {
		        // make the proxy look like the source element
		        var dragEl = this.getDragEl();
		        var clickEl = this.getEl();
		        YAHOO.util.Dom.setStyle(clickEl, "visibility", "hidden");

		        dragEl.innerHTML = clickEl.innerHTML;

		        YAHOO.util.Dom.setStyle(dragEl, "color", YAHOO.util.Dom.getStyle(clickEl, "color"));
		        YAHOO.util.Dom.setStyle(dragEl, "backgroundColor", YAHOO.util.Dom.getStyle(clickEl, "backgroundColor"));
		        YAHOO.util.Dom.setStyle(dragEl, "border", "2px solid gray");
		    },

		    endDrag: function(e) {

		        var srcEl = this.getEl();
		        var proxy = this.getDragEl();

		        // Show the proxy element and animate it to the src element's location
		        YAHOO.util.Dom.setStyle(proxy, "visibility", "");
		        var a = new YAHOO.util.Motion(
		            proxy, {
		                points: {
		                    to: YAHOO.util.Dom.getXY(srcEl)
		                }
		            },
		            0.2,
		            YAHOO.util.Easing.easeOut
		        )
		        var proxyid = proxy.id;
		        var thisid = this.id;

		        // Hide the proxy and show the source element when finished with the animation
		        a.onComplete.subscribe(function() {
		                YAHOO.util.Dom.setStyle(proxyid, "visibility", "hidden");
		                YAHOO.util.Dom.setStyle(thisid, "visibility", "");
		            });
		        a.animate();
		    },

		    onDragDrop: function(e, id) {

		        // If there is one drop interaction, the li was dropped either on the list,
		        // or it was dropped on the current location of the source element.
		        if (YAHOO.util.DragDropMgr.interactionInfo.drop.length === 1) {

		            // The position of the cursor at the time of the drop (YAHOO.util.Point)
		            var pt = YAHOO.util.DragDropMgr.interactionInfo.point;

		            // The region occupied by the source element at the time of the drop
		            var region = YAHOO.util.DragDropMgr.interactionInfo.sourceRegion;

		            // Check to see if we are over the source element's location.  We will
		            // append to the bottom of the list once we are sure it was a drop in
		            // the negative space (the area of the list without any list items)
		        	
		            if (!region.intersect(pt)) {
		                var destEl = YAHOO.util.Dom.get(id);
		                var destDD = YAHOO.util.DragDropMgr.getDDById(id);
						if (destEl.nodeName.toLowerCase() != "li") {		                 
		                	destEl.appendChild(this.getEl());
		                	destDD.isEmpty = false;
		                	YAHOO.util.DragDropMgr.refreshCache();
		                }
		            }

		        }
		    },

		    onDrag: function(e) {

		        // Keep track of the direction of the drag for use during onDragOver
		        var y = YAHOO.util.Event.getPageY(e);

		        if (y < this.lastY) {
		            this.goingUp = true;
		        } else if (y > this.lastY) {
		            this.goingUp = false;
		        }

		        this.lastY = y;
		    },

		    onDragOver: function(e, id) {

		        var srcEl = this.getEl();
		        var destEl = YAHOO.util.Dom.get(id);

		        // We are only concerned with list items, we ignore the dragover
		        // notifications for the list.
		        if (destEl.nodeName.toLowerCase() == "li") {
		            var orig_p = srcEl.parentNode;
		            var p = destEl.parentNode;

		            if (this.goingUp) {
		                p.insertBefore(srcEl, destEl); // insert above
		            } else {
		                p.insertBefore(srcEl, destEl.nextSibling); // insert below
		            }

		            YAHOO.util.DragDropMgr.refreshCache();
		        }
		    }
		});
<?}
   
   private function generateInitJS() {
   		$target_class = $this->assignementName.$this->targetClass;
      $lower_target_class = strtolower($target_class);
      $assignable_class = $this->assignementName.$this->assignableClass;
      $lower_assignable_class = strtolower($assignable_class);?>
      
	var <?= $lower_target_class ?>_list = new YAHOO.widget.Button("<?= $lower_target_class ?>_list", {
          type: "menu",
          menu: "<?= $lower_target_class ?>_list_select"
    });

    new YAHOO.util.DDTarget("unassigned_<?= $lower_assignable_class ?>s");
    new YAHOO.util.DDTarget("assigned_<?= $lower_assignable_class ?>s");
    var save<?= $assignable_class ?>sButton = new YAHOO.widget.Button({
          label:"Guardar",
          id:"save<?= $assignable_class ?>sButton",
          container:"<?= $lower_assignable_class ?>s_buttons" });
    save<?= $assignable_class ?>sButton.on("click", assign<?= $assignable_class ?>s);
<?   	
   }
   
   public function generateJS() {
   		$this->generateInitJS();
   		$target_class = $this->assignementName.$this->targetClass;
      	$lower_target_class = strtolower($target_class);
      	$assignable_class = $this->assignementName.$this->assignableClass;
      	$lower_assignable_class = strtolower($assignable_class);
      	$target_class_name=$this->target_class_name;
      	$target_class_id=$this->target_class_id;
      	$assignable_class_name=$this->assignable_class_name;
      	$assignable_class_id=$this->assignable_class_id;
      
?>
	function <?= $lower_target_class ?>OnLoad (response) {
		<?= $lower_target_class ?>_list.getMenu().clearContent();
        <?= $lower_target_class ?>_list.set("label", "Seleccione <?= $this->target_class_label ?>");
    	emptyList("unassigned_<?= $lower_assignable_class ?>s");
        emptyList("assigned_<?= $lower_assignable_class ?>s");

        for (id in response.results) {
            var <?= $lower_target_class ?> = response.results[id];
            if (typeof(<?= $lower_target_class ?>) != "function")
               <?= $lower_target_class ?>_list.getMenu().addItem({ text: <?= $lower_target_class ?>.<?= $target_class_name?>, value: <?= $lower_target_class ?>.<?= $target_class_id?>, onclick: { fn: populate<?= $assignable_class ?>sLists } });
        }
        <?= $lower_target_class ?>_list.getMenu().render(document.body);
	}
	
	   function assign<?= $assignable_class ?>s() {
	      if (<?= $lower_target_class ?>_list.getMenu().activeItem != null) {
	         var <?= $lower_target_class ?> = <?= $lower_target_class ?>_list.getMenu().activeItem.value;

	         var parseList = function(listName) {
	              ul = YAHOO.util.Dom.get(listName);
	              var items = ul.getElementsByTagName("li");
	              var list = "";
	              for (i=0; i<items.length; i=i+1) {
			      //list += items[i].innerHTML + ",";
			      list += items[i].title + ",";
	              }
	              list = list.substring(0, list.length - 1);
	              return list;
	          };

	          var list = parseList("assigned_<?= $lower_assignable_class ?>s");
	          var transaction = YAHOO.util.Connect.asyncRequest('POST', "<?= npadmin_setting('NP-ADMIN', 'BASE_URL') ?>/ajax/unidadesNegocio.php", {success:assign<?= $assignable_class ?>sCallback, argument:[<?= $lower_target_class ?>]}, "op=assign<?= $assignable_class ?>s&<?= $lower_target_class ?>="+<?= $lower_target_class ?>+"&list="+list);
	       }
	   }

   function populate<?= $assignable_class ?>sLists(p_sType, p_aArgs, p_oItem) {
	     var <?= $lower_target_class ?>_id = null;
	     var <?= $lower_target_class ?>_text = null;
	     if (p_oItem != null) {
	         <?= $lower_target_class ?>_id = p_oItem.value;
	         <?= $lower_target_class ?>_text = p_oItem.cfg.getProperty("text");
	      } else {
	    	 <?= $lower_target_class ?>_id = p_sType;
	    	 <?= $lower_target_class ?>_text = p_aArgs;
	      }
	      recoverData<?= $assignable_class ?>sLists(<?= $lower_target_class ?>_id, <?= $lower_target_class ?>_text);
	   }

	   function recoverData<?= $assignable_class ?>sLists(<?= $lower_target_class ?>_id, <?= $lower_target_class ?>_text) {
	      for (itemIdx in <?= $lower_target_class ?>_list.getMenu().getItems()) {
	         var item = <?= $lower_target_class ?>_list.getMenu().getItem(parseInt(itemIdx));
	         if (item.value == <?= $lower_target_class ?>_id) {
	        	 <?= $lower_target_class ?>_list.getMenu().activeItem = item
	            break;
	         }
	      }
	      <?= $lower_target_class ?>_list.set("label", <?= $lower_target_class ?>_text);

	      emptyList("unassigned_<?= $lower_assignable_class ?>s");
	      emptyList("assigned_<?= $lower_assignable_class ?>s");
	      var transaction = YAHOO.util.Connect.asyncRequest('POST', "<?= npadmin_setting('NP-ADMIN', 'BASE_URL') ?>/ajax/unidadesNegocio.php", {success:<?= $lower_assignable_class ?>ListCallback, argument:["unassigned_<?= $lower_assignable_class ?>s"]}, "op=listUnassigned<?= $assignable_class ?>s&<?= $lower_target_class ?>="+<?= $lower_target_class ?>_id);
	      var transaction = YAHOO.util.Connect.asyncRequest('POST', "<?= npadmin_setting('NP-ADMIN', 'BASE_URL') ?>/ajax/unidadesNegocio.php", {success:<?= $lower_assignable_class ?>ListCallback, argument:["assigned_<?= $lower_assignable_class ?>s"]}, "op=listAssigned<?= $assignable_class ?>s&<?= $lower_target_class ?>="+<?= $lower_target_class ?>_id);
	   }
	   
	   function <?= $lower_assignable_class ?>ListCallback(response) {
		      var listId = response.argument[0];

		      <?= $lower_assignable_class ?>sList = document.getElementById(listId);

		      data = YAHOO.lang.JSON.parse(response.responseText).Results;

		      for(id in data) {
		         <?= $lower_assignable_class ?> = data[id];
		         if (typeof(<?= $lower_assignable_class ?>) != "function") {
		            var <?= $lower_assignable_class ?>_element = document.createElement('li');
		            <?= $lower_assignable_class ?>_element.innerHTML = <?= $lower_assignable_class ?>.<?= $assignable_class_name ?>;
		            <?= $lower_assignable_class ?>_element.setAttribute("id", listId + "_" + <?= $lower_assignable_class ?>.<?= $assignable_class_id ?>);
		            <?= $lower_assignable_class ?>_element.setAttribute("title", <?= $lower_assignable_class ?>.<?= $assignable_class_id ?>);
		            <?= $lower_assignable_class ?>_element.className = "li_" + listId;
		            <?= $lower_assignable_class ?>sList.appendChild(<?= $lower_assignable_class ?>_element);
		            new DDList(listId + "_" + <?= $lower_assignable_class ?>.<?= $assignable_class_id?>);
		         }
		      }
		   }

	   function assign<?= $assignable_class ?>sCallback(response) {
		      var <?= $lower_assignable_class ?> = response.argument[0];
		      box_info("panel_rols_info", "Guardado correctamente");
		      recoverData<?= $assignable_class ?>sLists(<?= $lower_assignable_class ?>);
		   }

<?      
   }
   
   public function generateCSS() {
   	
      $assignable_class = $this->assignementName.$this->assignableClass;
      $lower_assignable_class = strtolower($assignable_class);
?>

ul.draglist {
    position: relative;
    width: 200px;
    height:240px;
    background: #f7f7f7;
    border: 1px solid gray;
    list-style: none;
    margin:0;
    margin-right: 10px;
    padding:0;
}

ul.draglist li {
    margin: 1px;
    cursor: move;
}

ul.draglist_alt {
    position: relative;
    width: 200px;
    list-style: none;
    margin:0;
    padding:0;
    /*
       The bottom padding provides the cushion that makes the empty
       list targetable.  Alternatively, we could leave the padding
       off by default, adding it when we detect that the list is empty.
    */
    padding-bottom:20px;
}

ul.draglist_alt li {
    margin: 1px;
    cursor: move;
}

li.li_unassigned_<?= $lower_assignable_class ?>s {
    background-color: #D1E6EC;
    border:1px solid #7EA6B2;
}

li.li_assigned_<?= $lower_assignable_class ?>s {
    background-color: #D8D4E2;
    border:1px solid #6B4C86;
}

.yui-button#save<?= $assignable_class ?>sButton button {
   padding-left: 2em;
   background: url(<?= npadmin_setting('NP-ADMIN', 'BASE_URL') ?>/static/img/save.gif) 5% 50% no-repeat;
}

<?      
   }
}
?>