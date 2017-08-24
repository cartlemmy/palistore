sl.require("js/ui/subItemView.js",function(){
	self.request("getAll",[],function(info){
		if (info.data || self.args[1] == "NEW") {
			self.info = info;
			var fields = {}, pauseUpdate = false;
			var item = info.data, isNew = false, history;
			var sect, fieldSection = {
				"status-fields":["status","paliSession"],
				"info-fields":["item"],
				"shipment-fields":["camper","camperNote"]
			};
			var menu = [], tools = [], changedFields = {};

			function getFieldSection(field) {
				for (var n in fieldSection) {
					if (fieldSection[n].indexOf(field) != -1) return n;
				}
				return false;
			};
			
			function setItem(item) {
				pauseUpdate = true;
				self.request("itemData",[item],function(res){
					var cont = self.view.element("info-fields");
					sl.removeChildNodes(cont,".horizontal dontRemove");
					if (res.subOptions) {
						for (var i = 0; i < res.subOptions.length; i++) {
							var opt = res.subOptions[i];
							
							var c2 = sl.dg("",cont,"fieldset",{"className":"horizontal"});
							sl.dg("",c2,"label",{"innerHTML":opt.label});

							fields[n] = new sl.field({
								"core":self.core,
								"view":self.view,
								"contEl":c2,
								"n":opt.field,
								"type":"select",
								"options":opt.opts,
								"listener":self
							});
							
							fields[n].setValue(info.data[opt.field]);
						}
					}
					pauseUpdate = false;
				});
			};

			if (self.args[1] == "NEW") {
				isNew = true;
				
				var def;
				for (var n in item) {
					if (info.setup.fields[n] && (def = info.setup.fields[n]["default"])) {
						if (typeof(def) == "string" && def.charAt(0) == "=") {
							eval("item[n] = "+def.substr(1));
						} else {
							item[n] = def;
						}
					}
				}
			}
				
			tools.push("save");
			
			function getTitle() {
				var dn = typeof(info.setup.displayName) == "string" ? [info.setup.displayName] : info.setup.displayName;
				
				var displayName = "";
				for (i = 0; i < dn.length; i++) {
					eval("displayName = "+dn[i]);
					displayName = displayName.trim();
					if (displayName != "") break;
				}
				
				return (self.args[1] == "NEW" ? "en-us|New " : "en-us|Edit ")+info.setup.singleName+(displayName.trim() != ""?sl.config.sep+displayName:"");
			};
			
			if (menu.length) tools.push("menu");
			
			self.createView({
				"title":getTitle(),
				"contentPadding":"8px",
				"tools":tools
			});
			
			self.view.setSaveState(self.args[1] == "NEW" ? "new" : "saved");
			
			self.view.setMenu(menu);
			
			function save() {
				if (isNew) {
					self.request("create",[info.data],function(res){
						if (self.args[1] == "NEW") self.view.removeMenuItem(0);
						info.setup.args[1] = self.args[1] = res;		
						setDBId(res);
						self.view.setSaveState("saved");
					});
					isNew = false;
				} else {
					var specialUpdated = false, upd = {};
					function doneSaving() {
						self.view.setSaveState("saved");
					};
					
					var saved = 0;
					for (var i in changedFields) {
						switch (i) {
							case "status": case "item": case "paliSession":
								specialUpdated = true;
								upd[i] = changedFields[i];
								break;
								
							default:
								console.log(i,changedFields[i]);
								self.request("set",[i,changedFields[i]],function(res){
									console.log(res);
									saved --;
									if (saved <= 0) doneSaving();
								});
								break;
						}
						saved ++;
					}
					if (specialUpdated) {
						self.request("updateSpecial",[upd],function(res){
							saved --;
							if (saved <= 0) doneSaving();
						});
						saved ++;
					}
					self.request("apply",[],function(res){
						console.log('applied',res);
					});
				}
				changedFields = {};
			};
			
			self.view.save = save;
			
			self.view.addEventListener("save-click",function(type,o) {
				save();
			});
			
			self.view.addEventListener("menu-click",function(type,o){
				switch (o.item.action) {
					case "create":
						save();
						break;
				}
			});
				
			self.serverListener = self.addServerListener("change-"+info.setup.table+"/"+info.setup.args[1],function(res){
				if (res == "DELETE") {
					self.destruct();
				} else {
					for (var n in res) {
						if (fields[n]) {
							fields[n].setValue(res[n]);
							info.data[n] = res[n];
						}
					}
					self.view.setTitle(getTitle());
				}
			});
			
			self.view.setContentFromHTMLFile();
				
			for (var n in info.setup.fields) {
				var field = info.setup.fields[n];
				if (!field['import'] && field.editable !== false) {
					if (sect = getFieldSection(n)) {
						var cont = sl.dg("",self.view.element(sect),"fieldset",{"className":"horizontal"});
						sl.dg("",cont,"label",{"innerHTML":field.label});
						
						var o = {
							"core":self.core,
							"view":self.view,
							"contEl":cont,
							"n":n,
							"cleaners":field.cleaners ? field.cleaners : [],
							"value":info.data[n],
							"listener":self
						};
						
						for (var i in field) {
							o[i] = field[i];
						}
						fields[n] = new sl.field(o);
						
						if (n == "item") cont.className = "horizontal dontRemove";
					}
				}
			}

			self.addEventListener("blur",function(t,o){
				if (o.changed && o.value !== false) {
					changedFields[o.field] = o.value;
					self.view.setSaveState("unsaved");
				}
			});
			
			self.addEventListener("change",function(t,o){
				if (pauseUpdate) return;
				if (o.value !== false) {
					if (o.field == "item") setItem(o.value);
					
					self.view.setSaveState("unsaved");
					changedFields[o.field] = info.data[o.field] = o.value;
					self.view.setTitle(getTitle());
				}
			});
			
			setItem(info.data.item);
					
			self.addEventListener("destruct",function() {
				if (self.serverListener) self.removeServerListener(self.serverListener);
			});
		} else {
			self.createView({
				"title":info.setup ? info.setup.singleName : "en-us|Error",
				"contentPadding":"8px"
			});
			
			self.view.setContentAsHTML("<div class=\"warn\">"+(info.setup ? sl.format("en-us|%% not found.",info.setup.singleName) : info.error)+"</div>");
		}
			
		function setDBId(id) {
			self.dbId = id;
			if (history) history.setFilter({"orderItemId":id});
		};
		
		if (info.data[info.setup.key]) {
			history = new sl.subItemView({
				"app":self,
				"scroller":self.view.element("history").slSpecial,
				"deleteCondition":'0',
				"table":"db/storeOrderItemHistory",
				"noNew":true
			});
			setDBId(info.data[info.setup.key]);
		}
		
		self.view.center();
		self.view.maximize();
	});
});
