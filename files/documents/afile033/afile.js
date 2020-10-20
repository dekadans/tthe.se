/*
aFile Beta 3 (0.3.3)

Copyright 2010, 2011, Tomas Thelander
Licensed under the GNU General Public License

http://www.tthe.se

	This file is part os aFile.

    aFile is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    aFile is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with aFile.  If not, see <http://www.gnu.org/licenses/>.
*/

aFile = {
	version : 'aFile Beta 3',
	vnr : '0.3.3',
	
	RelativePath : '',
	LogicPath : '',
	
	Server : '',
	ServerFile : '',
	
	ServerMode : '',
	ServerLogin : '',
	ServerUser : '',
	ServerUserPath : [],
	ServerUserAllow : [],
	ServerUserDeny : [],
	
	Div : 'aFile_Div',
	Root : '',
	SetRoot : './files/',
	
	Poweredby : true,
	
	OpType : '',
	OpFile : '',
	OpPath : '',
	
	CSS : {
		border : '1px solid #000000',
		marginRight : 'auto',
		marginLeft : 'auto',
		width : '400px',
		borderRadius : '10px',
		padding : '10px',
		fontFamily : 'Arial',
		textAlign : 'left',
		fontSize : '14px'
	},
	
	FileTypes : {
		txt : ['txt', 'config', 'java', 'c', 'cpp', 'h'],
		img : ['jpg', 'jpeg', 'gif', 'png', 'bmp', 'tiff', 'svg'],
		archive : ['zip', 'rar', '7z', 'tar', 'bz2', 'gz'],
		sound : ['mp3', 'wma', 'wav', 'm4a', 'm4p', 'flac', 'ogg'],
		doc : ['doc', 'docx', 'odt', 'pages'],
		pp : ['ppt', 'pptx'],
		sheet : ['xls','xlsx'],
		exec : ['exe', 'dll', 'deb', 'pkg', 'rpm', 'msi', 'jar', 'bat', 'sh', 'apk', 'py', 'pyc', 'rb', 'rbw', 'm'],
		video : ['avi', 'flv', 'mov', 'mp4', 'wmv', '3gp', 'mkv', 'webm'],
		pdf : ['pdf'], 
		web: ['html', 'xhtml', 'xml', 'htm', 'php', 'asp', 'aspx', 'asmx', 'css', 'js', 'war', 'jsp']
	},
	
	init : function(args) {
		if (typeof jQuery == 'undefined')
		{
			alert('aFile: Could not find jQuery.\nPlease include it to your website.');
			return;
		}
		
		if (args != null)
		{
			if (args.div != null)
				this.Div = args.div;
			
			if (args.server != null)
			{
				this.Server = args.server;
			}
			else
			{
				this.autoSetServer();
			}
			
			if (args.root != null)
				this.SetRoot = args.root;
			
			if (args.css != null)
				this.CSS = args.css;
			
			if (args.powered != null)
				this.Poweredby = args.powered;
		}
		else
		{
			this.autoSetServer();
		}
		
		if (this.Server != '' && this.Server.charAt(this.Server.length-1) != '/')
		{
			this.Server += '/';
		}
		this.ServerFile = this.Server + 'afile.php';
		
		if (typeof this.SetRoot == 'object')
		{
			for (var root in this.SetRoot)
			{
				this.Root = this.SetRoot[root];
				break;
			}
		}
		else
		{
			this.Root = this.SetRoot;
		}
		
		this.RelativePath = this.Server + this.Root;
		this.LogicPath = '/';
		
		jQuery('#' + this.Div).html('<div id="aFile_Content"></div>');
		
		if (args != null)
		{
			if (args.before != null && typeof args.before == 'function')
			{
				args.before();
			}
		}
		
		this.check();
	},
	
	autoSetServer : function() {
		var thisfile = $('script[src$="afile.js"]').attr('src');
		thisfile = thisfile.split('/');
		var serverp = '';
		
		if (thisfile.length != 1)
		{
			for (i = 0; i < thisfile.length-1; i++)
			{
				serverp += thisfile[i] + '/';
			}
		}
		
		this.Server = serverp;
	},
	
	changeRoot : function(root) {
		this.Root = this.SetRoot[root];
		this.RelativePath = this.Server + this.Root;
		this.LogicPath = '/';
		
		this.write();
	},
	
	check : function() {
		url = this.ServerFile + '?do=check';
		jQuery.getJSON(url, function(data){
			aFile.ServerMode = data.mode;
			aFile.ServerLogin = data.login;
			aFile.ServerUser = data.user;
			
			if (data.login == 'true')
			{
				aFile.ServerUserPath = data.userpath;
				aFile.ServerUserAllow = data.allow;
				aFile.ServerUserDeny = data.deny;
			}
			else
			{
				aFile.ServerUserPath = [];
				aFile.ServerUserAllow = [];
				aFile.ServerUserDeny = [];
			}
			
			aFile.write();
		});
	},
	
	del : function(sName){
		jQuery.prompt('Delete "'+ sName +'"?', {buttons:{ Ok: true, Cancel: false }, callback: function(v,m,f){
			if(v)
			{
				jQuery("#aFile_Loading").css('visibility','visible');
				url = aFile.ServerFile + '?do=delete&root=' + aFile.Root + '&ad=' + aFile.LogicPath + '&file=' + sName;
				jQuery.getJSON(url, function(data){
					if (data.status == 'ok')
					{
						aFile.write();
					}
					else
					{
						jQuery("#aFile_Loading").css('visibility','hidden');
						aFile.showAlert(data.message);
					}
				});
			}
		}});
	},
	
	edit : function(sName){
		jQuery("#aFile_Loading").css('visibility','visible');
		jQuery.get(aFile.ServerFile, {'do' : 'edit', 'root' : this.Root, 'ad' : this.LogicPath, 'file' : sName}, function(ldata){
			if (ldata != '0')
			{
				if (ldata == '-1')
				{
					ldata = '';
				}
				aFile.newFileView(function(){
					jQuery('#aFile_NewFilename').val(sName);
					jQuery('#aFile_NewFileContent').val(decodeURIComponent(ldata));
				});
			}
			else
			{
				jQuery("#aFile_Loading").css('visibility','hidden');
				aFile.showAlert('Error!');
			}
		});
	},
	
	extractZip : function(sName){
		txt = '<p>Extract '+ sName +' to: <input type="text" name="aFile_ExtractTo" id="aFile_ExtractTo" value="'+ aFile.LogicPath +'" /></p>';
		jQuery.prompt(txt,
			{
				buttons:{ Ok: true, Cancel: false },
				submit : function(v,m,f){
					if (v)
					{
						if (f.aFile_ExtractTo == '')
						{
							return false;
						}
						return true;
					}
					else return true;
				},
				callback: function(v,m,f){
					if (v)
					{
						jQuery("#aFile_Loading").css('visibility','visible');
						jQuery.get(aFile.ServerFile, {'do' : 'extract', 'root' : aFile.Root, 'ad' : aFile.LogicPath, 'file' : sName, 'path' : f.aFile_ExtractTo},function(data){
							jQuery("#aFile_Loading").css('visibility','hidden');
							if (data.status == 'ok')
							{
								aFile.write();
							}
							else
							{
								aFile.showAlert(data.message);
							}
						});
					}
				}
			});
	},
	
	gotoRoot : function(){
		this.LogicPath = '/';
		this.RelativePath = this.Server + this.Root;
		this.write();
	},
	
	login : function() {
		var txt = '<p><b>Sign in</b></p><p>Username: <input type="text" name="aUsername" id="aUsername" /></p><p>Password: <input type="password" name="aPassword" id="aPassword" /></p>';
		jQuery.prompt(txt,{
			submit : function(v,m,f){
				if (v)
				{
					if (f.aUsername == '' || f.aPassword == '')
					{
						return false;
					}
					return true;
				}
				else return true;
			},
			buttons: {Ok:true, Cancel:false},
			callback : function(v,m,f){
				if (v)
				{
					jQuery("#aFile_Loading").css('visibility','visible');
					var url = aFile.ServerFile + '?do=login';
					jQuery.post(url, {'user':f.aUsername, 'pw':f.aPassword}, function(data){
						if (data.status == 'ok')
						{
							aFile.check();
						}
						else
						{
							jQuery("#aFile_Loading").css('visibility','hidden');
							aFile.showAlert(data.message);
						}
					});
				}
			},
		});
	},
	
	logout : function() {
		jQuery.get(this.ServerFile, {'do':'logout'}, function(d){
			aFile.check();
		});
	},
	
	move : function(sName) {
		aFile.OpType = 'move';
		aFile.OpFile = sName;
		aFile.OpPath = aFile.LogicPath;
		aFile.write();
	},
	
	moveCancel : function() {
		aFile.OpType = '';
		aFile.OpFile = '';
		aFile.OpPath = '';
		aFile.write();
	},
	
	moveSend : function(){
		jQuery("#aFile_Loading").css('visibility','visible');
		jQuery.get(aFile.ServerFile, {'do' : 'move', 'root' : aFile.Root, 'ad' : aFile.LogicPath, 'src': aFile.OpPath, 'file' : aFile.OpFile}, function(data){
			jQuery("#aFile_Loading").css('visibility','hidden');
			if (data.status != 'ok')
			{
				aFile.showAlert(data.message);				
			}
			aFile.OpType = '';
			aFile.OpFile = '';
			aFile.OpPath = '';
			aFile.write();
		});
	},
	
	newDir : function() {
		txt = '<p>Name of new directory: <input type="text" name="aFile_DirName" id="aFile_DirName" /></p>';
		jQuery.prompt(txt,
			{
				buttons:{ Ok: true, Cancel: false },
				submit : function(v,m,f){
					if (v)
					{
						if (f.aFile_DirName == '')
						{
							return false;
						}
						return true;
					}
					else return true;
				},
				callback: function(v,m,f){
					if (v)
					{
						jQuery("#aFile_Loading").css('visibility','visible');
						url = aFile.ServerFile + '?do=newdir&root=' + aFile.Root + '&ad=' + aFile.LogicPath + '&name=' + f.aFile_DirName;
						jQuery.getJSON(url, function(data){
							if (data.status == 'ok')
							{
								aFile.write();
							}
							else
							{
								jQuery("#aFile_Loading").css('visibility','hidden');
								aFile.showAlert(data.message);
							}
						});
					}
				}
			});
	},
	
	newFile : function(bOverwrite){
		var sName = jQuery("#aFile_NewFilename").val();
		var sContent = jQuery("#aFile_NewFileContent").val();
		
		nameD = sName.split('.');
		if (nameD[nameD.length-1] == 'desc')
		{
			sContent = encodeURIComponent(sContent);
		}
		
		var url = this.ServerFile + "?do=newtxt&root="+ this.Root +"&ad="+ this.LogicPath;
		
		jQuery.post(url, {'name' : sName, 'content' : sContent, 'overwrite' : bOverwrite}, function(data){
			jQuery("#aFile_newNoteResult").html(data.message);
			jQuery("#aFile_newNoteResult").css('color','#FF0000');
			
			if (data.message == "Error, the file already exists")
			{
				if (confirm(data.message + "\nOverwrite?"))
				{
					aFile.newFile(true);
				}
			}
		});
	},
	
	newFileView : function(func){
		if (func == null)
		{
			func = function(){};
		}
		
		var sNewText = '<table id="aFile_NewFile">';
		sNewText += '<tr><td colspan="2">New textfile in: '+ this.LogicPath +'</td></tr>';
		sNewText += '<tr><td colspan="2"><span id="aFile_newNoteResult">&nbsp;</span></td></tr>';
		sNewText += '<tr><td>Name (incl. extension):</td><td><input type="text" id="aFile_NewFilename" /></td></tr>';
		sNewText += '<tr><td colspan="2">Content:</td></tr>';
		sNewText += '<tr><td colspan="2"><textarea rows="10" cols="45" id="aFile_NewFileContent"></textarea></td></tr>';
		sNewText += '<tr><td colspan="2"><input type="button" value="Save" onclick="aFile.newFile(false)" style="background-color:#FFFFFF; border: 1px solid;" /></td></tr>';
		sNewText += '<tr><td colspan="2"><a onclick="aFile.write()" style="cursor: pointer;">Back</a></td></tr>';
		sNewText += '</table>';
		
		jQuery('#aFile_Content').html(sNewText);
		jQuery('#aFile_NewFile').css(aFile.CSS);
		func();
	},
	
	newLink : function(NameIn, UrlIn, EditName) {
		if (EditName)
		{
			var txt = '<p>Create Link</p><p>Name: <input type="text" name="aFile_Linkname" id="aFile_Linkname" value="'+ NameIn +'" /></p><p>URL: <input type="text" name="aFile_Linkurl" id="aFile_Linkurl" value="'+ UrlIn +'" /></p>';
		}
		else
		{
			var txt = '<p>Create Link</p><p>Name: <input type="text" name="aFile_Linkname" id="aFile_Linkname" value="'+ NameIn +'" readonly="readonly" /></p><p>URL: <input type="text" name="aFile_Linkurl" id="aFile_Linkurl" value="'+ UrlIn +'" /></p>';
		}
		
		jQuery.prompt(txt,{
			submit : function(v,m,f){
				if (v)
				{
					if (f.aFile_Linkname == '' || f.aFile_Linkurl == '')
					{
						return false;
					}
					return true;
				}
				else return true;
			},
			buttons: {Ok:true, Cancel:false},
			callback : function(v,m,f){
				if (v){
					sName = f.aFile_Linkname + '.alnk';
					sContent = '{"name" : "'+ f.aFile_Linkname +'", "url" : "'+ f.aFile_Linkurl +'"}';
					
					jQuery("#aFile_Loading").css('visibility','visible');
					
					var url = aFile.ServerFile + "?do=newtxt&root="+ aFile.Root +"&ad="+ aFile.LogicPath;
					jQuery.post(url, {'name' : sName, 'content' : sContent, 'overwrite' : 'true'}, function(data){
						if (data.status == 'ok')
						{
							aFile.write();
						}
						else
						{
							aFile.showAlert(data.message);
							jQuery("#aFile_Loading").css('visibility','hidden');
						}
					});
				}
				else {
					jQuery("#aFile_Loading").css('visibility','hidden');
				}
			},
		});
	},
	
	rename : function(sName) {
		txt = '<p>Rename '+ sName +': <input type="text" name="aFile_NewName" id="aFile_NewName" /></p>';
		jQuery.prompt(txt,
			{
				buttons:{ Ok: true, Cancel: false },
				submit : function(v,m,f){
					if (v)
					{
						if (f.aFile_NewName == '')
						{
							return false;
						}
						return true;
					}
					else return true;
				},
				callback: function(v,m,f){
					if (v)
					{
						url = aFile.ServerFile + '?do=rename&root=' + aFile.Root + '&ad=' + aFile.LogicPath + '&file=' + sName + '&newname=' + f.aFile_NewName;
						jQuery.getJSON(url, function(data){
							if (data.status == 'ok')
							{
								aFile.write();
							}
							else
							{
								aFile.showAlert(data.message);
							}
						});
					}
				}
			});
	},
	
	returnFileType : function(ext) {
		if (jQuery.inArray(ext,this.FileTypes.img) > -1) return 'img';
		else if (jQuery.inArray(ext,this.FileTypes.txt) > -1) return 'txt';
		else if (jQuery.inArray(ext,this.FileTypes.archive) > -1) return 'archive';
		else if (jQuery.inArray(ext,this.FileTypes.sound) > -1) return 'sound';
		else if (jQuery.inArray(ext,this.FileTypes.doc) > -1) return 'doc';
		else if (jQuery.inArray(ext,this.FileTypes.pp) > -1) return 'pp';
		else if (jQuery.inArray(ext,this.FileTypes.exec) > -1) return 'exec';
		else if (jQuery.inArray(ext,this.FileTypes.video) > -1) return 'video';
		else if (jQuery.inArray(ext,this.FileTypes.pdf) > -1) return 'pdf';
		else if (jQuery.inArray(ext,this.FileTypes.web) > -1) return 'web';
		else if (jQuery.inArray(ext,this.FileTypes.sheet) > -1) return 'sheet';
		else return 'file';
	},
	
	shortenURL : function(sLink){
		var url = this.ServerFile + "?do=shorten&link="+sLink;
		jQuery("#aFile_Loading").css("visibility","visible");
		jQuery.getJSON(url, function(data){
			jQuery("#aFile_Loading").css("visibility","hidden");
			if (data.data.url != null)
			{
				jQuery('#aFile_Short').html(data.data.url);
			}
			else
			{
				jQuery('#aFile_Short').html(data.status_txt);
			}
		});
	},
	
	showAlert : function(Text){
		jQuery.prompt(Text, {persistent:false});
	},
	
	showInfo : function(id, type, size){
		var link = document.getElementById("link" + id);
		var descr = jQuery('#link' + id).attr('data-desc');
		if (size != null)
			var txt = '<h3>'+ link.innerHTML +'</h3><p><span style="text-decoration: underline">Description:</span><br />'+ decodeURIComponent(descr) +'</p><p><span style="text-decoration: underline">File size:</span><br />'+ size +' kB</p><p><span style="text-decoration: underline">Link:</span><br />'+ link.href +'</p><p><span id="aFile_Short"></span></p>';
		else
			var txt = '<h3>'+ link.innerHTML +'</h3><p><span style="text-decoration: underline">Description:</span><br />'+ decodeURIComponent(descr) +'</p><p><span style="text-decoration: underline">Link:</span><br />'+ link.href +'</p><p><span id="aFile_Short"></span></p>';
		
		if (aFile.ServerLogin != 'true')
		{
			jQuery.prompt(txt, {buttons: {Ok: true, 'Shorten Url': false}, persistent:false, submit : function(v,m,f){
				if (!v)
				{
					aFile.shortenURL(encodeURIComponent(link.href));
					return false;
				}
			}});
		}
		else
		{
			jQuery.prompt(txt, {buttons: {Ok: true, 'Shorten Url': 'short', 'Edit desctription': 'edit'}, persistent:false, submit : function(v,m,f){
				if (v == 'short')
				{
					aFile.shortenURL(encodeURIComponent(link.href));
					return false;
				}
				else if (v == 'edit')
				{
					
					if (descr == 'Not set.')
					{
						aFile.newFileView(function(){
							if (type == 'file') jQuery('#aFile_NewFilename').val(link.innerHTML + '.desc');
							else if (type == 'link') jQuery('#aFile_NewFilename').val(link.innerHTML + '.alnk.desc');
						});
					}
					else
					{
						if (type == 'file') aFile.edit(link.innerHTML + '.desc');
						else if (type == 'link') aFile.edit(link.innerHTML + '.alnk.desc')
					}
				}
			}});
		}
	},
	
	switchDir : function(sName){
		if (sName != "..")
		{
			if (this.LogicPath == "/")
			{
				this.LogicPath += sName;
			}
			else
			{
				this.LogicPath += "/" + sName;
			}
			this.RelativePath += sName + "/";
		}
		else
		{
			var arrPathSplit = this.LogicPath.split("/");
			this.LogicPath = "";
			
			if (arrPathSplit.length != 2)
			{
				for (i=0; i<arrPathSplit.length-1; i++)
				{
					if (i != 0)
					{
						this.LogicPath += "/" + arrPathSplit[i];
					}
					else
					{
						this.LogicPath += arrPathSplit[i];
					}
				}
			}
			else
			{
				this.LogicPath = "/";
			}
			
			var arrFPathSplit = this.RelativePath.split("/");
			this.RelativePath = "";
			
			if (arrFPathSplit.length != 1)
			{
				for (i=0; i<arrFPathSplit.length-2; i++)
				{
					this.RelativePath += arrFPathSplit[i] + "/";
				}
			}
			
		}
		this.write();
	},
	
	toolbar : function(){
		v = jQuery('input:radio[name=selFile]:checked').val();
		
		if (v == null)
		{
			v = 'empty||empty';
		}
		h = '';
		
		vs = v.split('||');
		
		if (vs[0] == 'dir' && vs[1] !== '..')
		{
			h += '<img src="' + aFileImages.del + '" border="0" alt="del." title="Delete '+ vs[1] +'" onclick="aFile.del(\''+ vs[1] +'\')" style="cursor: pointer;" />&nbsp;';
			h += '<img src="' + aFileImages.rename + '" border="0" alt="rename" title="Rename '+ vs[1] +'" onclick="aFile.rename(\''+ vs[1] +'\')" style="cursor: pointer;" />&nbsp;';
			h += '<img src="' + aFileImages.zip + '" border="0" alt="zip" title="Zip '+ vs[1] +' [beta]" onclick="aFile.zipDir(\''+ vs[1] +'\')" style="cursor: pointer;" />';
		}
		else if (vs[0] == 'file')
		{
			var namesplit = vs[1].split(".");
			var extension = namesplit[namesplit.length-1].toLowerCase();
			var type = aFile.returnFileType(extension);
			
			h += '<img src="' + aFileImages.del + '" border="0" alt="del." title="Delete '+ vs[1] +'" onclick="aFile.del(\''+ vs[1] +'\')" style="cursor: pointer;" />&nbsp;';
			h += '<img src="' + aFileImages.rename + '" border="0" alt="rename" title="Rename '+ vs[1] +'" onclick="aFile.rename(\''+ vs[1] +'\')" style="cursor: pointer;" />&nbsp;';
			h += '<img src="' + aFileImages.move + '" border="0" alt="Move" title="Move '+ vs[1] +'" onclick="aFile.move(\''+ vs[1] +'\')" style="cursor: pointer;" />&nbsp;';
			
			if (type == 'txt')
			{
				h += '<img src="' + aFileImages.edit + '" border="0" alt="link" title="Edit '+ vs[1] +'" onclick="aFile.edit(\''+ vs[1] +'\')" style="cursor: pointer;" />&nbsp;';
			}
			else if (extension == 'zip')
			{
				h += '<img src="' + aFileImages.zip + '" border="0" alt="link" title="Extract '+ vs[1] +'" onclick="aFile.extractZip(\''+ vs[1] +'\')" style="cursor: pointer;" />&nbsp;';
			}
		}
		else if (vs[0] == 'link')
		{
			h += '<img src="' + aFileImages.del + '" border="0" alt="del." title="Delete '+ vs[1] +'" onclick="aFile.del(\''+ vs[2] +'\')" style="cursor: pointer;" />&nbsp;';
			h += '<img src="' + aFileImages.edit + '" border="0" alt="link" title="Edit link" onclick="aFile.newLink(\''+ vs[1] +'\', \''+ vs[3] +'\', false)" style="cursor: pointer;" />&nbsp;';
			h += '<img src="' + aFileImages.move + '" border="0" alt="Move" title="Move '+ vs[1] +'" onclick="aFile.move(\''+ vs[2] +'\')" style="cursor: pointer;" />';
		}
		
		jQuery('#aFile_Toolbar').html(h);
	},
	
	
	uploaded : function(sResult){
		jQuery('#aFile_UploadResult').css('color','#FF0000');
		jQuery('#aFile_UploadResult').html(sResult);
	},
	
	uploadView : function(){
		var sUpText = '<form id="aFile_UploadForm" action="' + this.ServerFile + '?do=upload" method="post" enctype="multipart/form-data" target="aFile_UploadIframe">';
		sUpText += '<input type="hidden" name="aFile_Form" value="upload" />';
		sUpText += '<input type="hidden" name="aFile_Root" value="'+ this.Root +'" />';
		sUpText += '<input type="hidden" name="aFile_AD" value="'+ this.LogicPath +'" />';
		sUpText += '<table id="aFile_UploadTable">';
		sUpText += '<tr><td colspan="2">Upload to: '+ this.LogicPath +'</td></tr>';
		sUpText += '<tr><td colspan="2"><span id="aFile_UploadResult">&nbsp;</span></td></tr>';
		sUpText += '<tr><td>Filename:</td><td><input type="text" name="aFile_UploadName" /></td></tr>';
		sUpText += '<tr><td><span style="color: #FF0000">* </span>File:</td><td><input type="file" name="aFile_UploadFile" /></td></tr>';
		sUpText += '<tr><td>Extract zip: <input type="checkbox" name="aFile_UploadZip" value="true" /></td><td>&nbsp;</td></tr>';
		sUpText += '</table>';
		sUpText += '<iframe id="aFile_UploadIframe" name="aFile_UploadIframe" style="width:0px;height:0px;border: 0px;"></iframe>';
		sUpText += '</form>';
		
		jQuery.prompt(sUpText, {buttons:{Upload:true,Close:false}, submit:function(v,m,f){
			if(v)
			{
				aFile.uploaded('Uploading...');
				document.forms['aFile_UploadForm'].submit();
				return false;
			}
		}});
	},
	
	userInfo : function() {
		if (aFile.ServerLogin == 'true')
		{
			txt = '<h3>Signed in as: ' + aFile.ServerUser + '</h3>';
			txt += '<p>In the following directories (and their subdirectories):<br />';
			
			jQuery.each(aFile.ServerUserPath, function(i, entry){
				txt += entry + '<br />';
			});
			
			txt += '</p><p>these permissions apply:<br />';
			
			jQuery.each(aFile.ServerUserAllow, function(i, entry){
				txt += '<span style="color: #00AA00">' + entry + '</span><br />';
			});
			jQuery.each(aFile.ServerUserDeny, function(i, entry){
				txt += '<span style="color: #FF0000">' + entry + '</span><br />';
			});
			
			txt += '</p>';
		}
		else if (aFile.ServerLogin == 'false')
		{
			txt = '<h3>You are currently using aFile as: '+ aFile.ServerUser +'</h3>';
		}
		else
		{
			txt = '<p>aFile runs in MODE_LITE, no logins are accepted.</p>';
		}
		aFile.showAlert(txt);
	},
	
	write : function() {
		jQuery('#aFile_Loading').css('visibility','visible');
		
		url = this.ServerFile + '?do=list&root=' + this.Root + '&ad=' + this.LogicPath;
		jQuery.getJSON(url, function(data){
			html = '<table id="aFile_Table">';
			
			if (typeof aFile.SetRoot == 'object')
			{
				html += '<tr><td colspan="4"><span style="font-size:8pt;">';
				
				for (var root in aFile.SetRoot)
				{
					if (aFile.SetRoot[root] == aFile.Root)
					{
						html += ' | <a onclick="aFile.changeRoot(\''+ root +'\')" style="cursor: pointer;text-decoration:underline;">' + root + '</a>';
					}
					else
					{
						html += ' | <a onclick="aFile.changeRoot(\''+ root +'\')" style="cursor: pointer;">' + root + '</a>';
					}
				}
				
				html += ' |</span></td>';
			}
			
			if (aFile.ServerMode == 'lite')
			{
				html += '<tr><td colspan="3"><a onclick="aFile.gotoRoot()" style="cursor: pointer;">Path: '+ aFile.LogicPath +'</a></td>';
			}
			else if (aFile.ServerMode == 'auth')
			{
				html += '<tr><td colspan="2"><a onclick="aFile.gotoRoot()" style="cursor: pointer;">Path: '+ aFile.LogicPath +'</a></td>';
				
				if (aFile.ServerLogin == 'true')
				{
					html += '<td style="text-align: right;"><img src="' + aFileImages.info +'" onclick="aFile.userInfo()" title="User info" border="0" alt="Info" style="cursor: pointer;" />&nbsp;<img src="' + aFileImages.login +'" onclick="aFile.logout()" title="Sign out '+ aFile.ServerUser +'" border="0" alt="Sign out" style="cursor: pointer;" /></td></tr>';
					if (aFile.OpType == 'move')
					{
						html += '<tr><td colspan="3"><span id="aFile_Loading">Clipboard: "'+ aFile.OpFile +'"</span></td></tr>';
						html += '<tr><td colspan="3"><span id="aFile_Loading"><a onclick="aFile.moveSend()" style="cursor: pointer;">Paste</a> | <a onclick="aFile.moveCancel()" style="cursor: pointer;">Cancel</a></span></td></tr>';
					}
				}
				else if (aFile.ServerLogin == 'false')
				{
					html += '<td style="text-align: right;"><img src="' + aFileImages.login +'" onclick="aFile.login()" title="Sign in" border="0" alt="Sign in" style="cursor: pointer;" /></td></tr>';
				}
			}
			
			html += '<tr><td colspan="3"><span id="aFile_Loading" style="text-align:center; visibility:hidden;"><b>Loading...</b></span></td></tr>';
			
			html += '<tr><td>&nbsp;</td><td><b>Name</b></td><td>&nbsp;</td></tr>';
			
			jQuery.each(data.files, function(entryIndex, entry){
				if (entry.type == 'dir')
				{
					if (entry.name == '..')
					{
						html += '<tr><td style="width: 50px;">&nbsp;</td><td><a onclick="aFile.switchDir(\''+ entry.name +'\')" style="cursor: pointer;">&larr; Back</a></td>';
					}
					else
					{
						if (aFile.ServerLogin == 'true')
						{
							html += '<tr><td style="width: 50px;"><input type="radio" id="aFile_Radio'+ entryIndex +'" name="selFile" value="dir||'+ entry.name +'" /><label for="aFile_Radio'+ entryIndex +'"><img title="Directory" src="' + aFileImages.dir +'" border="0" alt="pic" /></label></td>';
						}
						else
						{
							html += '<tr><td style="width: 50px;"><img title="Directory" src="' + aFileImages.dir +'" border="0" alt="pic" /></td>';
						}
						
						html += '<td><a onclick="aFile.switchDir(\''+ entry.name +'\')" style="cursor: pointer;">'+ entry.name +'</a></td>';
					}
					html += '<td>&nbsp;</td></tr>';
				}
				
				else if (entry.type == 'file')
				{
					var name = entry.name;
					var namesplit = name.split(".");
					var extension = namesplit[namesplit.length-1].toLowerCase();
					var type = aFile.returnFileType(extension);
					
					html += '<tr><td style="width: 50px;">';
					
					if (aFile.ServerLogin == 'true')
					{
						html += '<input id="aFile_Radio'+ entryIndex +'" type="radio" name="selFile" value="file||'+ entry.name +'" /><label for="aFile_Radio'+ entryIndex +'">';
					}
					
					switch(type)
					{
						case 'txt':
							html += '<img title="Textfile" src="' + aFileImages.text + '" border="0" alt="TXT" /></td>';
							break;
						case 'img':
							html += '<img title="Image" src="' + aFileImages.pic + '" border="0" alt="PIC" /></td>';
							break;
						case 'archive':
							html += '<img title="Archive" src="' + aFileImages.zip + '" border="0" alt="ZIP" /></td>';
							break;
						case 'sound':
							html += '<img title="Sound" src="' + aFileImages.sound + '" border="0" alt="MSC" /></td>';
							break;
						case 'doc':
							html += '<img title="Document" src="' + aFileImages.word + '" border="0" alt="WRD" /></td>';
							break;
						case 'pp':
							html += '<img title="Presentation" src="' + aFileImages.pp + '" border="0" alt="PP" /></td>';
							break;
						case 'exec':
							html += '<img title="Application/Script" src="' + aFileImages.exe + '" border="0" alt="EXE" /></td>';
							break;
						case 'video':
							html += '<img title="Video" src="' + aFileImages.video + '" border="0" alt="VID" /></td>';
							break;
						case 'pdf':
							html += '<img title="Portable Document Format" src="' + aFileImages.pdf + '" border="0" alt="PDF" /></td>';
							break;
						case 'web':
							html += '<img title="Web" src="' + aFileImages.web + '" border="0" alt="WEB" /></td>';
							break;
						case 'sheet':
							html += '<img title="Spreadsheet" src="' + aFileImages.excel + '" border="0" alt="SHEET" /></td>';
							break;
						default:
							html += '<img title="File" src="' + aFileImages.file + '" border="0" alt="FILE" /></td>';
					}
					
					if (aFile.ServerLogin == 'true')
					{
						html += '</label>';
					}
					
					html += '<td><a href="'+ aFile.RelativePath + entry.name +'" data-desc="'+ entry.desc +'"';
					
					if (type == 'img')
					{
						html += ' class="lightbox" rel="fancybox" id="link'+ entryIndex +'" target="_blank">' + entry.name + '</a></td>';
					}
					else if (type == 'pdf' || type == 'txt' || type == 'sound' || type == 'web')
					{
						html += ' id="link'+ entryIndex +'" target="_blank">' + entry.name + '</a></td>';
					}
					else
					{
						html += ' id="link'+ entryIndex+'">' + entry.name + '</a></td>';
					}
					
					html += '<td style="text-align: right;"><a title="Show info about '+ entry.name +'" onclick="aFile.showInfo('+ entryIndex +', \'file\', '+ Math.round(parseInt(entry.size) / 1024) +')" style="cursor: pointer;"><img src="' + aFileImages.info + '" border="0" alt="info" /></a></td></tr>';
				}
				
				else if (entry.type == 'link')
				{
					if (aFile.ServerLogin == 'true')
					{
						html += '<tr><td style="width: 50px;"><input id="aFile_Radio'+ entryIndex +'" type="radio" name="selFile" value="link||'+ entry.name +'||'+ entry.filename +'||'+ entry.url +'" /><label for="aFile_Radio'+ entryIndex +'"><img title="Link" src="' + aFileImages.linkfile +'" border="0" alt="pic" /></label></td>';
					}
					else
					{
						html += '<tr><td style="width: 50px;"><img title="Link" src="' + aFileImages.linkfile +'" border="0" alt="pic" /></td>';
					}
					
					html += '<td><a href="'+ entry.url +'" data-desc="'+ entry.desc +'" id="link'+entryIndex+'" target="_blank">' + entry.name + '</a></td>';
					
					html += '<td style="text-align: right;"><a title="Show info about '+ entry.name +'" onclick="aFile.showInfo('+ entryIndex +', \'link\', null)" style="cursor: pointer;"><img src="' + aFileImages.info + '" border="0" alt="info" /></a></td></tr>';
				}
			});
			
			if (aFile.ServerLogin == 'true')
			{
				html += '<tr><td id="aFile_Toolbar" colspan="2">&nbsp;</td>';
				html += '<td style="text-align: right;" colspan="2"><a onclick="aFile.uploadView()" style="cursor: pointer;"><img src="' + aFileImages.upload + '" title="Upload file" border="0" /></a>&nbsp;';
				html += '<a onclick="aFile.newDir()" style="cursor: pointer;"><img src="' + aFileImages.newdir + '" title="Create directory" border="0" /></a>&nbsp';
				html += '<a onclick="aFile.newLink(\'\',\'http://\', true)" style="cursor: pointer;"><img src="' + aFileImages.newlink + '" title="Create link" border="0" /></a>&nbsp';
				html += '<a onclick="aFile.newFileView(null)" style="cursor: pointer;"><img src="' + aFileImages.newfile + '" title="Create textfile" border="0" /></a></td></tr>';
			}
			
			if (aFile.Poweredby)
			{
				about = aFile.version + ' ('+ aFile.vnr +')<br>By Tomas Thelander.<br />Licensed under GNU General Public License, 2011';
				html += '<tr><td colspan="4">Powered by <a onclick="aFile.showAlert(\''+ about +'\')" style="cursor: pointer;">'+ aFile.version +'</a></td></tr>';
			}
			
			html += '</table>';
			
			jQuery('#aFile_Content').html(html);
			jQuery('#aFile_Table').css(aFile.CSS);
			
			if (aFile.ServerLogin == 'true')
			{
				jQuery('input:radio[name=selFile]').change(aFile.toolbar);
				jQuery('input:radio[name=selFile]:eq(0)').attr('checked',true);
				aFile.toolbar();
			}
			
			jQuery('a.lightbox').fancybox();
		});
	},
	
	zipDir : function(Name) {
		ZipPath = aFile.LogicPath + '/' + Name;
		txt = '<p>Enter name of zip-file: <input type="text" name="aFile_ZipName" id="aFile_ZipName" />.zip</p>';
		jQuery.prompt(txt,
			{
				buttons:{ Ok: true, Cancel: false },
				submit : function(v,m,f){
					if (v)
					{
						if (f.aFile_ZipName == '')
						{
							return false;
						}
						return true;
					}
					else return true;
				},
				callback: function(v,m,f){
					if (v)
					{
						jQuery("#aFile_Loading").css('visibility','visible');
						jQuery.get(aFile.ServerFile, {'do' : 'zipdir', 'root' : aFile.Root, 'ad' : aFile.LogicPath, 'name' : f.aFile_ZipName + '.zip', 'path' : ZipPath},function(data){
							jQuery("#aFile_Loading").css('visibility','hidden');
							if (data.status == 'ok')
							{
								aFile.write();
							}
							else
							{
								aFile.showAlert(data.message);
							}
						});
					}
				}
			});
	}
};

var aFileImages = {
	dir : "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAD+klEQVR42s2TDWwTZRjHn/c+uuvHtbjSspY5s0G26VzMgBjRaWSYsKRFIhkq8yMaJUqMIZKZCCEiCNPNKFF0Bkw2N+MgJEZGdJskggY0EJBBHCrJMiRi59jWrlt717v3+r6+V7qxMVZLoonP5bncXZ77/56P90HwHxv63wLW1/r9H7eHQv8q4JVn893BB92PLSj2P5ebN69i19sHC7buGb2cNeDlZ4oc5SVyZTwW12MKUZNJCrINOW8vkkoKizzV7nme5bLbKyKOS8V/1f51oGbjxa6MgA3rypwrlsqBhYX2GpvDVm1zylaL1YFIEoPpiF2cRQKEZhZ76dyp18qCPQ0ZAWePP/3SbR5xt+SQAesxRAhL+1p11z9P/QbqUH+7757vn8wI+OidZeW1Qd85TdeAF0WEOD5rAI6N/Oxb0n1XRsDjqxdyH765ZCihxnI5jgdOsGQ9dIJVbev2b+VPOlQ88W3lci96Ye2tq+fn2fIrgsfeTzV2sPeJQ3o8HASOv6kKzPd9rUfLX/1grNd8+aJpUfUdxe4dLod18cme0KpH1p8+lAKc7ArU5TlxI8Y6EiwShasDzQrw+y+/1YbGXZcr7vTsdDqslaBHwQBxcMt75ws+OziAU0q73lh096oq9wmsq4gXLFkD2DFGlMAV4C0eq8gj3ojSWAJgKKLuuH/tT69P/AhrAvnCzo3FYUJ0OZvdowyBBIl11AK6ngADY3DZrRCOREFVYvq6LX1lv/Yn+iYBpvV+U9UNxtgKQJlmwEZksVMxxw5JQwdd01DSMChzoIaCwFDp/s6RlnfbRupZvLnh2iSgu2Xxpnw31BuE3DBrUS4FSVKB7Ukq6yQ2wGDCgOPAJWMQjSaiDS3DDZ3HlcMsXGV+iXl8ErBtQ+G9Kx9w/UAInlEBpQg5vIXsbiAmfDVjrABvRBBKJujRU8rpbXvCe4dGyQUWPso8Yh5Oc1UmAQ9X3SJufn5+GKhmT8sjJp8CYMIjb0ERZb1G7OxTAQ+nsh4bN+L1zdHWL79Tj7CwcLotw8zHzTMwbQamHWku7ZJ4pZpQbvpCCQ6Ym+cFXhsEAbMcWJuO9WhnNjeNNw2MkIssZCCd8RhzY+q/0wAt2311JQU5jTrWp7SIgxyrhHzOccpWF8XiNF7fqjTvO6x10GvC0euFbwh4sUZe8OhDrgskmeAmAARE8OfqyCXp9MfeZE/d7kTjH1eIubl/pTPGkMFmHPoDb81tc8voKWykqgDNEKDUrygNn+OmTzuNNkJTWUf/SXhWwJpl4pzAfVKn2yUsNSs436+e2duhbeoP0bNpYT0b4VkBptkkEDxzoNIigKvvTzjBNjdys8IZAVPMnAXJRmg2+xuwJM4oqRcUzAAAAABJRU5ErkJggg==",
	file : "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAACO0lEQVR42q2VO6saQRTHZ3xcX8THBVFEEAS5ESSJhbYJWFilzYdIEVKHfAE7qxSmFW3sbCxEUwsKGwthG0EQRDC6vliN3M1/ZFcGGblG98Cy/5k9s79zzuzOoYSzw+HwcblcfqaUHsgVZrfbabvdVkul0qd6vf4s8qFngF+LxeL9NS9n5nA4CACkWCzKrVbr6UXAfr9vrlar3DPMAmNzvObH7A6ApdPpkHK5THa7nVytVp9eBKBEOUhNvwwf7WwNG1ObzaZJkkRqtRoFj67XazkcDr8uFAqaEIAoDACrpxE1r/nxab7b7R6zwt4Rv9/fymQyOSFAVdWbAMjkpN1u92+n0/lWCNhut01FUa4ukcDH4vF4JK/X+04IwAY3Ucf/zoDXiF4KBAKXAbeUiNco0WXAfD43Mri5RCyDYDAoBsxmM1MyCIVCYsB0OjUlg0gkIgZMJpPjn3xPBi6XS4pGo2LAeDw2BRCLxcSA0WhkCiAej4sBw+HQANy8BwyQSCTEAFmW786AfUXJZFIMGAwGd//JLINUKiUG9Pt9Uz7TdDotBvR6PVPOomw2exHQ3mw2H9A8NHa8H1dymh9f8kGX6wPwRghoNBrfcdx+0zTtLxcdPffjjC+lhoZjxZH/M5/Pfz0BKpVKHBF8wcX6qQ8vd0K/QkQP7DlrvbrW9GgJa9GsReK+x5SqzzG9wnrVarUqgA0w/kHRsK0Qj3DywcnHygj9qPf5B+gA9GmTOQDRy6JAq7rPXNdL6AX0n39HNKUhgn+24AAAAABJRU5ErkJggg==",
	linkfile : "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAEwElEQVR42qWUaVBTZxSGvyQkIQtKTILtwGCTQbCAkIhZKERFKFPBKtpSpKOi5YdtmS50aBEBEZD1ly2l8q+1LGV0FEEhgCwJCaSsDYmIFQcBLWQpGBL25N7bL3Rg2rGdSfT8u/fO9z7vd+55Dw68YuVfLiQjCOKHYhgDQ1Fgs9nmiwovqze+415FPC+/wM3VlZLo5+ebjicQuBazBTMajVNTT6dyV5aXfyr//jvspQGXcvNxUDwlJCQkh+5GYz0eGwdW6xogkcjgycT4lE6nSy4v+7btpQB250QSOSlULCrD4wmYQqHQNzXeKSGRyR6SffvPsdnsbZU/Xytisz2ynQYUFJZAcVIynxecSXZ1ZSm6FJNSadMVpUJ+0z8g0OdM0tkKFxLJt6aqshSCspwG5BcUnQgVicuodDqrs7NTL226W9z7q+rWu0eOrjEYzOzjx+LO9g0MEhtu1xWzPTzynAJ8/Mmn5LAwiXHnTh+6sqcHtDZLi3u6u29ERr39h6+v3xUen3dCr9cDuUzWof5t6KvA3UEjTgHe4HC2ZF/MNTEY7mDs0SPAYjHR2track9PLyQ6OjrVYDRiA/0D2va2lq+ZTJbq/n2txSkAh8NxT/ks9TmdRgHBwbtBb28f0GpHjFFRkdQZvZ42NDCoUXTJCk2m580icailtaUZdQogFIpE8QkJHSKBgKLRasH0tA4nkUiw0YcPgaJL3qNSdefB+e+NOBhprrt1E7OfcRiQmXUxjEajXt0nCQ/UaLTIsHYE7+XpiV9aXrbCdvUplfJ8FEF69wpEZjiy6Ma5/wVcLiiOJZKIKSiCUlbX1hCiC+H1yMiIXQMDg2h1dXXj5MSE3H4aQ7HVpaXFETwerw6X7LPcaahH/6nzAsAeIjjfSUKBoIzNZgGD4U8wNvY78H9zFxiGzq9WVPw4NfGklO7mNgt3z3rxePzV1taW1f8y+i9AzqU8NyqNlryHz4chorCGhgaxtZUVIBYLcQ9GR7GhIbX1l5qqwAMRB59CpyuOtHYTkJWdg6PR3VL2huzJocEQtbW166YmJ2ZOnz7JHx7WgGfPpgE0C6oqrzFnZqbnHP1364D08xdct7ozPnorVFy+vluUSsON67VpiwsLXgGBgadgC/z9/QMwlUqF1FRXsvV6nclhwOdfpOKoVFqMJDy8Yov7Vi+5vGuy+e/dUuuxfftrMLlfxsfHJ+kNRgymtl0u63jPYNCbHQYkJCQSbAiSm5FxIUMml8821NeVqnq6rx+KObzKZDIzYUKTYYio9hDJZO1ZQUHBrU2Nd1cdBsTFHSdaFhYK0tPPpykVisd37jac43K5Y1yuTwmfz0vU6Q32EHXDEOXbQ2QymeYdFV8HHDkaR5w3zaclnTmbvWa14sfHx3u2MdxN3t7exzZ2S1tbyzc2q61XIBSapU2NqFOAmNjDBOg86MOTpyp5PF4ADocDRCIRzM3OAbVabd8tRXC3SIVCseXevRanxDenSCQS0+DM73jnUGyui4uLD2JDwLx5/oFGo/4BJvn+gYgIc/3tOsxZ8U3A+/Ef4GD0qf39fR7wkWp/T6FQF7x37JjlcLiLzVLn2vICYKMkkv0EFEPxcGkBeBPUncFAYWJfyvlG/QXwBz/Ekfaf8gAAAABJRU5ErkJggg%3D%3D",
	text : "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAEFklEQVR42pWWf0xVZRjHv++53Mu9SpBraeUmDSsqBPkhDG4CkuKq2Wr9EXNsTmiCiBbJT4XBxYGCQNEQEVxgLnO21Vpr/Rjdw68AB/JDkMpMCzdz2pom0AXuvefpOQTtDjh/nHd7tud93u95Pud5n/c99wosHb5sMUKIo0TkZP8I2xtCkraRolSxT5Ik5SmKYmf/M7Zi1ppYe5j9HrYHnsnEouSvwLLyHIKi/F6L2yymZ2bw3eAwWYwmUZaajOzGFlVDNWkpovijT2hqZkZsDw+Fj8VMn7d3CYz13YdjKpk1Xy8H8IF5xU1jacuqXdZwCpZmhFMh/GFaScI5K6KFA71efnOAaOffoo/MpJi8xROzUzAK0CiZxdmeQThLUu5h+p91rJtcDIhFWGzH7trTqI/0x7XfbsLlcmPt46sxMTmF23f/wjMB6+aEv1wfx2OrH4HvQz64dfsueMsQuN4fmf3jOJO1BxjqimdZ12JAAjYlyG/XNsK2YQ19fO68mJiYQGpqCnV3d4vOrm7k5Bycq6C6ukbEx8WR1Rojmptb4OvnR8k7k0TJlTuoy0onXGrbyrq2pYCILfLe9xpQHLKWHNPTghsHo9FIqs7hcMBiscwB2Bfsz8WdTie4yWQxm8WRkVs4dTCDMNCuAQiPt2fUnIQt4km43W7oGQaDAbaB39GQvQ8Y7NAAhMXJaVX1sEUFkFtRFtZokW5h7hkngyQJW98NNOVmEoY6NQChsXJG9QmUWwP5yJMuAOcXhT1X0ZCznzDcpQHY+IL9QHU9jscHQd1/PYP7gLyOMdTlZAKXu5cHiBCrvK/qBCq3BHN6fRUIRuS3j+Jk7n6ikR4NQHCMPa3iA5QlbIS+9/8vUVHbZTQVvMO3rlcDsCFaTjtWi7JtYaQWracCtYii74fQdCiL6MpFDcDzUfJeBlS8FKnuj/j/SQ/dwtwzTvOwgm/7cUoF/NinAXguUj5QUYv3d0STENL8GnlW4zH3jJN6JsS7X/WiroABP/VrAJ7dZE8vq0Hljhjw51lXD/hcI58BjUXZoJ8vLQ+QAsPlt0qrUP7qZpIM+nqg3pvCL3/AhyW5pFwd1AA8HSan2ipR/nocGQySLoDbrYjCLzrRbMsn5dqQBuCpUHt6yTHUJm2F0ctL1xY5XS5kXbCjsfQQlF+HNQDrQ+Tdh8twPCmRTCajrgpmZ50i70IrzhwtIuX6iAYgIFjemVuC0jcTaYXFLObT8fnw0C3MPePsq5/34k9bcb6qlJQbo8sCQsUa/8GYtHyc3vUyVvn6YOlPtuYZwr0Hk9hz9hv0NlWC7oyHc3B4MUD1e7wiEqMfDooii7e3ri1y8B+A+2N9cA20XuS5dX59ySs+ylbN9+dFvgj6ukyKi++bzF4O258L4X8BBVrmKOQnojAAAAAASUVORK5CYII=",
	pic : "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAFWElEQVR42u1VW2wUZRT+zszet2XbDbAt5WItBQoEUcCYqMtFJNxSEgRaUcAHq3InPGDXXoGyctlaEDQCQX3wwVcTHzQgGBIuSYPhKlCBCgUKLWW73XZvszPHs9NKTHzwBd6cZDL//PP/5zvn+77zD+EZX/Q/wH8CFBUVKZFIRE2n0081sKIosNlsOm3ZsmVNWVnZR88i+6ampsMUlGv9xk2VD/sMeOwEq0LQ5ePNsI7umAESEu1WQlJjGAyo8n1kjgqXjRBLGSbLLvluVYFw3EAkyeiR55R8K+qqPg6ZABVr1gcuRWw8xEHkdSlo6Tb4h5YUtQmI7GerSqTJ0GYhzCi08fxiK7VHDe6OG+RxKHBYwIJFlx9qiKTA4ZhBFeMM7KwPhGjHjh3BDwQgqbo4z63Q9W4DR29rbBhEwwcpkjH46iODrnSmMTVfxcJiG6sEutqRYqcF9GdXEimdmRSihKHCbrfwhKEWmuBMoL56AGDtuvWVWVlZEAbQkWBIxYgKJZKIqEXQhIlLHQaUtIZBVkZnr4bOqIZYUkdf0oDTrqJkmBujh9iRl6WY1OUafWio/aSRGhoagqvXrgukrFk8xKlQWoJFU8x3enXKIDqtCseY6HaUkUxquN+jc1wH6ZrOLisoSRYUelTOc4FUYsQ1gyUBmpknFG2tDtH27duD769eV3lTc6O76zGyXU7k57jgERHtwkUkYaBTqtJUFboontGiRbTpEjGz7YpZdUIqyVYZ0WQacck+kdDw4Xhg97aaRtq2bVuw/N1VgbrDP3Lb/U4aOsiKUc8V8oxXp5GsR3u4lx0OO3l8PrjcThFa4Z5olM7fbOc7l89ToS8H6YISTjq99OjGdcTCj9lud1BtaQn27moImQBLy98JBEJH+GFXDxUNHwzFaueJY0ZQT9qJSxIoEo2TN9sFh02V6mx8724bXWlpY5/XTcN8uYjFYry8rJQedMVw/MxF1tI67dq4DPsad4do69atwbIVpYGmYwvY7lTJarGLM51saHbyeUdBN5wc7m0T3/TCojpg6ClOpYg6HqRYSeaIAl64HR52KDmUlePDmYutPHniCKqYU47QzlCI6uvrg2+/t7Dy0PlZUIRzs8XFFaqln990EjCkwzI3ZEZVyWwuMk8x8UFmXsTVdQNpsZuWYtlD2PDaL2jaeaiR6urqguWrFgS+ODedLdJQUExnciYGmJCWxiFxEYtlmTMQEjEzofSvycxlkNMaUzwmZkhmloCq5p/E53uOhEyAZSvmB/ad9psbxCyQpuHMdj1tHrcsQSjfU4LueDvyPWO5J9FJj6P3uMDzAklD4npbMzssuVSQM14cFeNbd89T7dLjOPCZANTW1gaXLJ9X+enPfrNs886klKlc6X/qYs3pE1biWtspFHgnwm3z4kLrMYwf6UfL3WZ09TzCtNGl+O2PE/A4B6O3rxObF32HL/d+3Ug1NTXBxeXzAlXfv86kQkQ2+4vJZLa/fAGlWZNW4vfbp/Hi8/O4s7udLtz6lWdOWkFhCd7afpUTyRTdab+BscMnyThOGxfvx8H934RMgEVL5wY2f+Vn0dcMShn2ZaxaM+EVHpybR1PGvInLraeQk+XjCzdPkcvm5ZdGv0Hnrp0QAyj8yrg5dPbiURSPmMxup5uWTN/UD1BdXR1c9Nbcyg37/MAAJRmO+g9iwGJ14OVxs/EgfBvh3gdiyUG4/+gWbKoLU0tmmAKdvXAU+d6RGDNqMhKpPpy7dhKNa3/CwQPfNlJVVVWwoqIikNQSPED/E4oG/hsDkE+uv9//Of+vsU0Sk2NoD/n9/tnFxcVz8PT/z9zc3Hz8L4wki8JRui6FAAAAAElFTkSuQmCC",
	zip : "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAEvUlEQVR42t1Va2wUVRQ+Z2Z2druvbmntWqBLK6S0lUetJpQYJJhGQzSCSNRoJLVWpAQJxCARQpRIja9ANdESQiJJMSgEIo9YpNRKsTQ0tPQNbUltbbfslr4f0+7OzhzvzLRrof3LD53J5N7M3Dnf+b7znXsRHvKF/32Aax9/Ns+zKmOj6HSAKcLyLyQBKIEgtP1WAv6aavCsSQdeNMOdC1dgpOseBIbG2Bq6L5gQYRlgw/Gs+go1DFBdcFSAYOBk3JLk9bwoohE6zI5kaRyqDh/DiVE/pW95Gfubu6ihsBhkKQDILiIDRZtr6y2uyO8VWd7+duN1NSzR3w0NewZr6vPw3j0QzOYZNElVwVfbCD3N9ZC4NhXUIEHr2Zsw0S/NKototxVwJtO2TTevqjpAR3PzQd5m3eEvKcPhknLikAszmM5GVRQc7vGSfTH7LhL4rt/Fsa5xncSDbOzu2CPutCVb8fJ7O/cvej/nHbBb55JK6LtwiaJECInOIHA84Xif3aCvKaAPiIo6BpFLPcii4UBtOwmizYjKMKbWaAl1V1QdxNJd+5JEu/3yvDdfiQ+xhHv+KIex0ouw+CUOBAvBreOWGRIIDgGWfpgDHFtfvb+AFUqbGbdB17ilwcFuXaL6Xy7sHb3V8mnMurXYXVZO/hOHIOk5RJYYVeTz4YympLJ7oiBldza4rBYszs2jkKToUR8sOCJ3VQdobWo+QMNDe+7+ehn7/mont/MccALhQKeFOqscYX2nRlf6Qlj0eiZEWkxYdqCQ5J6hmY4iUNU4z0b86Ivza954MfmTSIe4arynF0+fuEYWcwQhp2sJHBr5cJq27B0rNcQ/OgLRNgVsIOMdyU2D/QJwxkLijHJhiBPkjs7+9bg258gHmzc8+dUTKXNQZXa8csMLWjssS6wDUQhAWUPm/ZZlADFyLbhNzKKjfdDlWgHDintWuza0eG/jvvzzKAVCP6xe7tmUutCJpdc7qOBEOex61YsOq0qfn3wK+MnsNB9pMjwTPwgZy5zMqSH8+mKIeiUThBRV/6aJpLEReI4URW3Ta1Bd37Kn5FpH3qL5Dmho9UF7yylIS+iFYEiAL89mzMgsK9kPG1KCTGSCnKJY6A1aZmVgEvE7HaCprSXfYYrYfuxMPf5UXk2J0XUwIZuwsz+Kekfs05tOH9+N98ML8wOg1T23yk0+2TyjMVGg8bTUmCRcfXpn3qG0nKxowRbHaoBnvH9SwiM22cpLwDNVRpU5Ws0mi637G6XSKxjT3MgagkdlUzZBhBWM9kKaRNImdK6p8hCu/Hnr46lRCb/vTdoYq4ZUKPbdgPZQFbw2bwJEVKCozzODurmyFaSabjAtcIH6/PLwe5WMZuONrQaaBvxeXaJnT+1IT3TOLd792Po5RV2V9G3rYchdQBgtIm27bTU8Hi4y0FveCVh9V4XKGMSC+TzJzH0KsX1tsg+0HwSOIzMvVIYPnMxTO59OsMcVSdKIPZ5+BJegYNO4jQr90foPMLmjaeNmH8G6PhUuRXH4zVwMB50OAEZjbrnvREsrzM4YnvCtcOIQU1CC/pAJ+piTxlR+amcHl6wFJ1g5QlDH9rjTMRz4TXrQB5UcZc+x/8GZ/LAB/gG6OzKfPr/8DwAAAABJRU5ErkJggg%3D%3D",
	sound : "data:image/png;base64,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%3D",
	word : "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAEXUlEQVR42rWVfUzUdRzH33fHHTsejmcO4lDOIxCc0Rg9GEaKFKQBAbFcIJq5NpMiLBlRiSAmooCxH3+lDlFJXPm0Cag5XZqECmg1FA0xHjwQCwKOe+Ae+twPDu53sbnd5mf77PPw/f4+r9/3mYenLDybOIw0k9RvjrYniYl0gPQw6e25AFmSwLB9z76eKfTwC4CjUABHkWDKsj6ftXweDxqdgVQ/bWf94YF+3L9wZHKs784HVO+QNcDfyTeo692SWnH0YhlEjg7QGIxQG03QGI3km9hYQ7F62ldT3uyrzfnpviCIk3IE10o3qDVDfymortICWBudkVvzxZdb2CIq+kClN85ai086bp03cP0JUh6BfU/Voedk9Tqqe9AC2PRRUTkTm7aaBYT7uMDRQQCDyQQ9/ZjebClvsPhWebaPEehVaVHY3gMVTZfj5QZMHNyZTXWrZwAbt1cwLyelQyYRIzbIy64dk9/SjcpbfTD90oDJ2lIuYH3RHibqrXTIPZyQIPe2C1B8/QFKW3tgIIDu8C4uIKNwN/P8qneg8HBGSrCPXYCtNIIKM+BqIzRHbADpX5Uxi1amIdjTGRmhUuhpwRKrLrNzb5YzOTHIrmtF95AKbmIh3l8qx97zd9m2EKkLqjOjUHD1PqrbeqFvpjWoK+MCkgp2MaEJqQjxcsaGcH+YaPFO/6HE3kt/IvtVBTycRdjeNHV+zICqtAisJ6CHWITK1Ocgcxcj70oX9hFARwDV0d1cQHz+TkYRn4pQAnyyOIBNtvSOoPjiPeTHKHDoZj/6RzVTADonyxVeONkxiA9fCERymB+b3/zzPdS29UH3ayPG6m0Ay/K+Yea/kYJwbxfkRcjY5A3lKEqbuxEhdcWtwbGZuRYJ+Kx1ppNdHb9wJs65eBfft/dB29KI0WN7uIAln+9gnol7G5F0Bgoi57HJtkdjKKchW8Sd/nxEq5+J1yyUIsFqS+de6MRRGoHmWhNGfrABROaWML5xyXjF1xVfR81nk+2Px/Ht7w9Zn089s0J8UdP5aBomQNlL8pm/N8tnP91BfWsf1Neb8M+P5VzAopxixjM2GbF+Emx7MYhN3vxbhaoOJeu/RvmlUgl20EEyy3sLvBEX4M7ZplvO3cYxAkzcOIvHx20AwR8XMZLlSXjTzw0lS+RsUjmhQ/PQ1NzH+buxV8OlgVH2hlwV6AERn88B5NMuq2/thar1LIZOVHAB8zZtY8TLEpFChXZGK+w6aAUNHTSCXowTYPBUJRcg3biVEcYkItnHFcyKULsAm4//hjO0ZmNt56A8zQWslazJrRHErwaPdkme3AcyOli2wjPNXdhcRPmvBgdaHsBIV+tw8wkMnP9uHayua3++TNElLNwv1gkcwKfrQUB3s4As69N1bPGt22bbZ5Wn1aLrwKdqrc2Dw25rLAjfz1+ZJeR7Sqc/pu1pmi1siQVWscCq3TA8iKEr9ZPqh53/ezItYnn0/e1Ygic++k9F/gMKRzk3f4XU4AAAAABJRU5ErkJggg%3D%3D",
	pp : "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAADNElEQVR42rXVb0xNYRwH8G8mKVNGUy111RWaGDU3EYURY941SYQrWtpa2Rq98E4vvKg3bEiN6c/QZraE/tC/NdUYFZV0J6GirSSlbjq+x33OuveoW7ubZ/vt/J7z7/Oc5zx/7PCfi52q7s+IYbhPcW2mIjF6GLmMlqmAo8FLcCMtCPaapbzgxDNyOKqOcvnJGBZHkUs8dvYC6Y0w1vVDz7O3zQGPAGd01CfA0VHH2nzGBOP3NMdxkSuh1EeBkQ5Al4eR5iFoeaZbAWIzInAz+azqoXGzh2fKlTpLZgmQUotjTG8pwJnsJFw+sUfuSQdAmwY4eJl6VRKtlmC9PtgFPEsHfo0i5zWgL0Iiz16ZBJIJ7JQ76zAQmGvbkCni+KjNMwHFakD+gu3MvOIIXLcNeHwKqMhCTiOBR2ogkUC4AHQEel8BhpLZvdh3N+C2Hig2A56ogQQC25h5Ewgh0HgNGDAAa+Osv7ynATAOAutOs4sE0ESgRA3EE9gqgFACLQQMd9myTdaBoY+8J5RTlMADApUEmgmUqoE4ArwPGgJhBNoJ1MWbrjppOMa/cRgOm+rekRxlnI0LvIEPecCqBMCPwH0BvCFQpgb0BDYzW05gBwEDgZcE1lwEVp8DytjH35tMd7uGAfMWmZCv5QRS+R8IFBKoFkC5GjhOIEQAuwh0Emi7wLyb830O8CKKy0I7MMYvcfIF7AXQR0BLQEPgHoEqAm8JPFUDsQSC5RFBIIJAlwxwaod/AeY6T/Z5axK/pJ7nXPgVrkB/DeBznqOPwB0zoEINxBCQ16EVBPYS+EyghV20mLPP7xJbLHdHIfCerZ0wWv5o/6uAJ4ECAXAt1VeqgWgCGwWwn0APZ3NH6uzmgZYNcOcszhc/uZVAlRqIIhDEbCWBAzbO5FwBtBGoVgORBAKZ+RwCDubbBmRFA88LkPOOQI0lEJuxj8u1PKfG7IENKYDLMtMVaRYvlu/p/wSUZnBWG5HJYZrSYLlcewS4ccM5yQ2HI/Lv8jtdSNavj/D/6x5ywxmw3HDkciTYE9lpW7hlLuTQl6Z4oTTNkSEx7/whtsy+f7dMpSibvocNf2DGTf+/lD+SD2coyhHLGAAAAABJRU5ErkJggg%3D%3D",
	excel : "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAEnklEQVR42rWVe0zTVxTHv7/+fi205SUySgXE+cqMAza2mSVsuixbdCbqUMlQUEBI1EiyzCWKRpyD+OChKPzAsI0N5OGyF8KQbbrNZds/JGZuhOhwtBTWB1QelZYC7e/X7vJrKf05MzMSb3J67j0993x6b+85h8JjHtQD61VEMohEPeS7Rw03kUEijUTuPAywm16u/GjBNpU0TC1BEMMT4aAkEkTzUEo5wTazwcbRmCBi4xhBJnhGsI0ZXbB8OeTkeidyiFuDP0BNxSg0mxtU8swkE0IDnOBcFBEJODflnXvWTqI9Qua8x+702q0OBp26EPycq5906+3LSFzTLCDzuXfC6ypKxwRHBy/xiMurSaDZuVOwUx6bz2du7iYX9UONA5pqcxaJWz8LOPBujYTdlA3wvBxxdD5kiIWL/HoX2eACNTf3ap5ot1fPrMecJrSYqjHu4NHbZkNn4XAeiVvlAxz6gGJf20UjUpKORFndvF7MZWMeWgwd0LTa8NvJe2LAwRqafTWdxiI6F88GVs0L8JnpEJr7v0JfmxVdp81iwNsXGXZdOoNoAlijqCTHd+PIrUpoJ3oRQLsQowxHUfxxciU8TnQXwWC3YJqTYGXwUyhM3A+KotBkOIymvhb0t1txu3hQDDhQJWVf3skghgCSgysE491xLbb9mkE22yBnXMhemoX7zntoNbRjkgSXS1T4PLkR0Yoowb9u4AgadS3QXx1HT4lJDNjLytjkNClimVy8Enred+wOYxuKew4jkACCZJxgmyLBXS4FShIuIT400ef7oe4oGskJDB3j0JQZxYDcigD2xTQZFjM5eH1B+Vx6kqtitUfRabkMpYz3Plsae+PKsTZii+g/qNYeQ4O2BaZv7qP/nEEMyDofyK55KwBxBLAx4qxoY//kTVQNbBKe5Mybj5AtwbHlP4GmpCK/C38V4FLfFZi/tUBfrhcDMsrl7AupgYiT7sGWyDLfJhtvRq1xPUYdBiGreZJkNOXG2vB9WL/wfRGgrOc91GuvYPi7MQxe+FsM2HFWwSYRwBIC2B5VKhg59xSah96EfvqmkGBKySJYSELNAGgJhdTIeqxUvOEDnL5zAnWaVoxeG8Vw5YAYkFqqZJ/ZHognpTnYEV0s3H37yH7ctn8hZKySViEz6hp+sZzDH7Z68rJAXlEostQ3EMYsFoIUdhfiE3ICy/VRWNh+MSClJIhN2CrHUtke7I49AytnxJ/2Nt+viwt8CZGyp0mtmkLj0AYMObuFSqmWJWGn6mswVAAKuorwMTmB9fsRWKt1YsDmM8Hs6q0KLCOAnLhT/5mx45wBJsct3zo64HlS0qOQ//tJ1Pa2wv7jCOwXtWLAxlMh7KoUBSkVaTi4ohzzGfs6C/Cp7jombwzDUaMRATLX5YfUJWQEYWxKiXhFHsKl6rlc8H1QHuVv904Mk2ZU322GneMxfXUQriZdFvzKtXrhCkaT0qSS81LaW9u9NZ7z1HmnqP5LxD7+fWKKlPDjXZN4oOHMjF1PxMtqV2eHSWUqqV/H8utivGSum3k15+9jdoBv1Tuhsf2rZc6O2aavxv8fj2z6j2X8A6YNSjdJRYjRAAAAAElFTkSuQmCC",
	exe : "data:image/png;base64,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%3D%3D",
	video : "data:image/png;base64,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%3D",
	pdf : "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAEOUlEQVR42q2WXUxbdRTAz7n3tve2oxYQZkwMLpmBJXNjss1pMqfbg47ERDOT7UGjGCYhgxYYHxtQXVAGkVGgLZWPsY1pzDKNW8QsRF9cfJgf6DZUnE8YedAhOD6MbaG993gu0NlyLyQYT3LS//3f0/7O5/9fhGTZzHqKNZdVgLWJxjrMWs06Et/EBINMu90+0t/fn5mfn0+CKMbf0TK7+HPiPmmqioODg1BQUDARCoV0RyfigHtZn2V9srKy8tUTDW/C5NQ08dfXBABEzEhzQsOJN8jr9Zbznj8O2O4EGNKRLxw4ANt37oRoNGaaAzktDTYePLRijmyyDO+/dx5cLtdJfnz9LmATwLcn8/IoPTcXBYsF1FCIJIcDozMzINpsJMgyxmZnYfKnW/DYJ1fIZpONEfBatlrh/LmzZABsY0BbVhZtPH4cBUQgq5UkqxVRUUCLRCg2PY3jAwPw1++34aFPPyOnI8UUYJEkOHumzwjYxSnq5R9eX18PoiDA3zdvgiU7G5ScHNCmpmD+xg24c+kSYNaDsP7al7DObjNNkSSKcPp0rzFFeziCj9gLacMGVMfGAFNSyLpjB0aHhwGdToqNji56mp0D0tAQKbIV2WXCpQjia4Gd6+3pMUbgYEA2GxUUvoZP788HThOJooC6R6IoEn+ixOFbHOtA3LqV4Oo1xPRUEh/ZkpQiAQXo7u4yAGTWB1grOny+I8XFxUC0NCS4aIIYH5pFn6MffAyW/fsA7nEkpUivX1dXlyFFcXnL7/d7jpSUsD9kKODdIYhGac7bhXJZEaFNSbZhwDvBILnd7hUB9SU6YBXRfhuHSE8/2BuOmb4PBoOwGsCzBCAezMUCssTXusxdvExCRgZK+3YbbPTAg6tF4PP5PaWlJXryDR2ir9XRX2Hu3YskbduClufzDTY6oLOzk8rKyswBXGSPq7RUP1YWzhZYcm9hY2YWws0+PTUU6b+AStHLRhtNgwADylcCtHd01HMHLHRDkoQjEGr0gq2a4alOiHT2gVJSCLDMTmNOIBCAivJyc0Bbe7vHrQME4d/8hsMUamxH5ZVDgBnpwJ1D6i9jqP74M8kHn0uqgaqq4A8E6GhFhTmg1dvmcbtdIPFhHftqiIcpHeYvXyGl8EXk8wFoeoa76DZpE39i7JvrxKcbWp54HKTduwh5GKOxGPj9AaqqPGoOaDnVWl/GAPXcBZA2b4LY9e9BOfwSn8OKaUvOfzgAGtdGF+sze0G9/z7gRoGa6ipzQPPbLR4uEIhff0ege/do3mKHJLRpQksufMZ+uAXa+B/cWQ/jvMMB3ChUe6zGHNDY1OzhArHD8opzsByQuBfiZuBGIU9drSnAfbiouKOpqQky0lPhv8jknWmoq6uDvt7upCszLpmKYhtpbmnN3PPUXr70hTVe+hp+cfVzqK2pmohEwkmXfqL8739b/gEtcfcoOGay7QAAAABJRU5ErkJggg%3D%3D",
	web : "data:image/png;base64,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%3D%3D",
	
	login : "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAACq0lEQVR42n3Tb0gTYRgA8OfudrupO2/iuanU2nCTBRHrnxkhohsYBkKSMPpDMUrL2veKvoUV9iUcfWhEHwwxWiQUCVIaBm1jEGlKzPnBOf907sxO90/3t/ccgbPY8/F5n/f3Pu/z3mHwn+jr6ytubm62yeVyE4ZhEI/HR10ul91qtcZ212K7Ew6Hg2loaBiXSqUHBUGYEwGFQqFFS9+9Xm+jxWJZLwi43W47y7JdwWDwvMlkcoo5j8fToVarB0Kh0FOj0WgrCPh8Pg6dPFxfX2/dmec47jnKtxoMhsqCQCAQCKOT7tTV1dl35qenp20URd3X6/V0QQC1Hvb7/bfMZvOTnfmxsbEb6BoPdTpdYWBxYSHya8Vv930bfonhxHZJNpuCwyfaLbhMZXOPOOgL3b3Zf4BrbVrq9tWTE3GVxSCJzQAjiwBOEJCFDGQSMYiDEsJ4DZSuDQX6h74a7z6bWs8DLjaWKbpalb81x1uBrj4ARJEScJLersgmw5CK8RDhfsC89z0MfV7VPnrLB/KAS00VTHe7WthzqAnoKgNISipzAIqMCEQ5BPhhaeITOEeXNL1vlufzgMvmSsbWoRcqao1QVKYCqRx1IJXngEQEEpEV2BR44GcnwPkhoHnwKpgPWFuqmZtnawWmWg2kjASypBwIafH2WhrNIBFdhdRmCja4BXj9MaDpGZzbBZzay1w/oxPkrBKwdBjIomJ0BSoHJLcgGY8CEDREV3k0g0VNz8BsPnDl9D6ms00tyOQK2NrggaQowCVEDkil0elbQJWysBldh3dfQpp7/TP5QMuxcqans2aNpMrxDW4FJFISPSOem0EaAYkk0Col6kbIPnYGtS9GfuYDYhzZrzKYjlady4R5Vtws/oliZDPoa8hkgCxlhfFJftA1uTz1d88f3k8XIMuogrUAAAAASUVORK5CYII%3D",
	
	info : "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAACeklEQVR42mNkoBAwoguwWGSKSStrZVvoqXrLivGp8rIx/P/69duzB8/fXz9y5eHBN49uLfx1ctZHrAYI+HWEpwQ7zQh1NhRQEWJmEOQAqWBkePr8E4OkGA/Dn3//GKZvOvOqccHe8A9bag6gGADUHFuT5r/A2UCBSVeSHagPIvX//38Gw9S9DPOK9Rj0NcSAfAaGFfsuf05rW6z/80D3fbAqJrN0ubT4iOshDrpcdupCYHP/gzSDDWBgOH7+KYO5gRSQzwjm/wdC14JZU09Oz8gBGyAV2NZZlR5YFm6tzMDFzgrX+O/ff4am6acY7j/7wjC32Rks/u8/xODm2VvOTsr3NQEbYJ0760ROmJO5p6ky1AaIARMXnmE4fO0jg7GhLENJmBpcM4jumrfxyuSCAF2wATYZEy+UJgfoW2jJoRjw4dN3hinbnjIYKPEyuBmLwzWDcGJe9dojS9pCwAYouWeuaqstCbXSVUI4HxqAAYV7GOpTdBl0NIEG/GcEa37w7A2Dr6tt6L9XN9ZAgppPxj27be7W/EhXZlAA/oMa8v7jNwbroKUMnjZSDLlp1gwS4gIMv/78YUjPKzt0Yf1EN4b//37CopGZXUa/pbZnVom/kxkLzK+PnrxjCE5bz9BUYsvgbK/G8PnbD4ba5u6bx5a0+DD8/XUHPSGxMvBKpATnNDcmx4SJCgvygV3x5etPBk4udoadh079ntHdtOPVhe0FQJvv4UrKQD6jJqOwSrKijqmVnJqefEREuKSGigJDYVnV+vOru6MZ/v35jjcvIAFQQhZnkzEoT6nsSbx66/6hgxNT3QlmJiyAhYGFwxmIfzP8+LCPHAPwAgAmZwkgEIvHtwAAAABJRU5ErkJggg%3D%3D",
	move : "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAFK0lEQVR42pWVf2xTVRTHz3mv7eY2abvuR9u4jf3mN+3oBhOQaSIxiDBQxIgGo4gkLEGFEMAY/5AYQCcbv9QBG2KMqKNbHBFDAgxGAuhAI4zRsV8ddd3GWFlp13Z7713ve23HG1aHN2ly37vn3c8953zvtwjjjKqqI4r4BN0farV6CiEE6SsiWx59pmvuqKionfrk5L2ZGRkeecB/DpPJFFe2Z8/9Aks+CQQCSOhWDz5G6TkuNg7aO9qRYZg7vX19pakpKbvSUlPJowI0u8vLXaYZM2HQ7Y4Yk5yUCOcvXID5c+dBt7P73m3H7U0Z6RmVImR8wMzpmt179rnMJhPx3PcEDy0rES0NSUjQwfmGBny6qIjwHA9Op3OQgnbq9YZ94wLMpmmaz8v3u/LMZuL1eMf2AFEC6OK1cLq+Ho16PaGvQKVUoT/g76+trV02LiDPNFVTWn7AZcmbBV6vd+wi3UxsSUxsDND+AM9x0mulSgXOnh54ecWKpY8AmKQpLfvSlU+bPOTzRVQRIUQ2B1CpFNjd3U0oYPn4AHO2pnT3IVdBvoX4/H6k28tKBA+eZXOFUoEOh+NRAZk0gyrX7IJ88Pv9/3ILxs4pALq6usQSLcPqTxc9MyUp+qQyRq2kCYoSIWK0wAXcLQPezRvLL1d/dfj43cKCWWQ4MIIsw0olEQgvHjgiQsEqsNPeGcxgVtaEJSeOflCbmDkRyPD9UBChp43BY2XbG662uS6+tK1m8/zZ+eK+WFH/MbGkLwBTyjzg+GEMN0BeIlbBYkdHxyig+ETVFmt8sg+IrzeYARGATVpEpaYiDCvArx1JWDS/gOw48R5ONxByw9kLi/O2QKZuGnLCSPhyj2bAsiy2tbfJAJUba3RGGhe4OxrHUglW3PgNTvW0woupn8FA1BVI0Tmg954VjFoznLdxsOapo5CVOB04gRsjLmoZcKu1NdiDIOBdq+4JlpbIRdPE0CkUePD6JWLI0sJ1excatTQbvIn+Ebe0bpr4PHzxiwcPrzlDeJ4XAbRc4QwYtLW0yDI4XFKTkBZNAW4I2xMFQMWfF0GXQWCEt8Ew55F+4t0SQ1ocWnir8AzkJM+gDRekb8LtEDO4abOFM1BTwDprUvoEGOp3oiAIJBikwCO2RpI4dRAG/E3IiLnRRlKrIW32NHh7bi1MMppQdstCCpQcBJuam8MZUEDlWqvGEAOOxiZ8LHmyFMcySvyus4HoLc0wyP2FDAQBfQNAlP0bYfHUElr7gNQwMSsGqWSJQMIe1dvTM1iyfv0rIcCaGoYJ0FOraUlyQPA76FwFFb+fhbjcszCEPmBCx+RpNa5fM8BK/auQpdZQXzDA7SuN7tWfNGynEXyoz4QIgqvTbj+LFgqoq3zD6u3rgbQnn0XgBghwLnoiBdbfaSLHr10FboQJS1CyN1Ezm+Y8B7k6A3o9MeTUjz/Vvr7jUsnENMMQAhWLeCUE5Fa9tsqPlmx1sbVsuVWgTpg6dyEKPjvVAi/9WyG9taLGUaGUao0he5ZYojTZWLzT3s+XbP1mnTZ32tGKgyeHH7YaCtAUf/vRgpr49CxIyM2hShqQOdlDJiAzBlHNAjwOrZeudq98v3r1hq2rT7+5di/5JyBHs/TQBrPVOKcQoydEiSZEQlKN5DNj7HrYR/i6Y2d+2F93a1tjU589klmiMT46b0mh4R1atCgqgnHdVT54Ap7Ltnt1hQuyz1V8fcUXEbBy0eTY739u1tK58v9sHhojBSaj+4WF2Z4Pd50TIgX8DalGhvgn7SfLAAAAAElFTkSuQmCC",
	rename : "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAC8ElEQVR42u1UPUwUURB+8/bnDrh4p40kFiZYgBQmxBgbzJJQqRUhmBhbS01MlNqOgtLC2BgLIokFiQ25lljZGDkK4SCB+zExRvGA27vb25/3/GbZu5xKAg2VzO7szPvZ+ebnzSNxykRnAP8JQGl7+zxJKZglM1GsE6RMpNY61pkUdFJKaJ6PIhFhzOuK58DqUKehoaFfVK1UXsDAY9uyusahCCMx3GEpGQQeYTmKlEj39wvNaAAIsdCs10XE4BgzUCKXqVQqfUiZ5q3iXkOl0xbMxI/CJ9bxKoYU8bwQrTBUY5cG5ezsU2VZtmRvgazm5uZk03XZKJNkUIBUqFour7ihcgq1unZbvk6qwl/dk8aOLrwgpAdjwzp3LkurhYLe2NgQMzMzVKvVdOw5UgPWKgxlpFSJU7TiBpGT3/mmbINkYkeJ2Ot/dOEhgoc3rknLMtXa2prc2toSU1NTyvM8NtiNgGsRheEhwEEQOu83KzptyKO8/iOCJiJ4NH5d26ZJANAJALU8jyvbjSAEiOoA7PuB8259R6VN49gImn6gnjk3pf1XBACQoieCkPUOwB4A3hSKqs8yjwVo+L56Pjkubds6WYq+osg/2m3n5acvauAEAC4imL89IbOZjFoFQLFYFNPT06per8ukF3CwtAzabREXuQyAlGE68x8/659NT/OZj/OuO3nXf9QAAPT63l39dmGBdnd3NVhcHR2lyYkJXdvfZ6PUbDT08MiIDHy/TNVqddmU8s5AyhaGYcSMFhDSSJpMGjEadQ8YThKaqFQuH+7FniAIRDabFW14zQ3m+77I5XIiiKJ1yufzg6lU6gl2ZtEwGfzQB07DzgCkjkMgykDavXcMur7FfcfRIiWEK8PFRh+6C26AD5CtV7S4uHg5iqL7GFzB5otY6Ie8wDb4R0g2bvUcV0rqwhRy1hJHAvAe9AbkdziwCV448jZdWloyuGC2HTudop788Hzn8oNkoDanCGPNxT7yNj1NOgM4ln4DFrUPSUzP8kcAAAAASUVORK5CYII%3D",
	del : "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAFAUlEQVR42o2VW0wUVxjHz5mZvczCsnQxNG1YEV1XRZMKhpBY0ahgitbE1LctSRvSND747ENrozZprEnffDS9Epo+lcTY4GVJE3aVyoOFplW87KIiTdQAorvu7Mzumf6/szOULVg9yXDOzJ7z/33XA2eLxtlotLYuFjuqh0Ja/sGDL95OJh+zVxi/bd0a8EYix0zD0B/fvHlq/8TEtPsbdxeD0eiK6nXrJv8eH6+2slm2ur3dCnk8azaeOzf1f+K3urtD85qWSV+5Ehaaxhrb2uyn6XSs+8aNOwuAwVisXo9Gb2dSqRoNmzRFEbZpKo2treaKmpro+rNnl4Xc2bcvNFsqSXHu8bCSbTPDNFm0o6OUv3dvwzvXr9+WgMT27aOZ8fE2D4Q1VWUq5xyPLQxDaWxpMetCoei6/0DSjngG4orPZ5cYswlQEoIDwptaWqa7kskGCfi+vv6JaZohKY5NEBd4FFVRmCgUWGTz5goIWT4H8cmRkbDq95PlAo/iAESRMUVFJD589IhLwHfh8CnLto+QoATAEwLAEabRYYQrAk90XV/DLWve1LS7d2G5qutMuKIA2NgvYQBwIX7snZt7fyHJX4fD/dgcp/gDwgGxQWAK1grWRYQr0to6rwUCubup1JsawiIQRogzCHMAIC2YA0p8NDvbVVFFNL4hCOdxjTHBsREw7OAEVDiFqwjnyUMkFGKiBC/pHAwTRSGkB8AM9c7Odi4pU3d8W1fXj8NxWE3WM8wSRgBns8Cj2KQLURjEBA3A8G2od2amc7HeEoCEwBPEMk65kD7YMk7MJmW8g2DjIMpMUJgoB5SvRK8TlpcCaJypre2Dyz0yXHDD3UiWY60I8oA8gZew/9LHT57sWU7nhYDB9nb90fR0zkBXU0/wyjOQlZ5QPvgbTU2RvVevPnhlwOXOzlCW88wEStHn8UgPnPDIHAgqQycHRSR+FfokFAxGOwYHp14KGN69O5RlLPNXKhX2o85hqwQo5fBIAC+zyiECroBmjG7ZYtYCsuP8+akXAn7dtUuK/+mI04/cASzaKKtIOCC73Ae4VQxlLe6u13B37bpwYWoJILFzpxQfTybDAb+fqsZ2AJzWTogUF2K71eQ0Gt1FZj6vxOAJQbouXpxaAFzYsaM2pyjp34eHwzruFkbdTHGGLKc++Ff4FyweQqyXu7nAXzRcuTewziNcGwCpCwbX7kkk7kvAT21tyT+uXdvmg7gruhhAscb70JFnz2QTfRUM9gMSt2XGhex2Nx8EMwsFJbZp0+QHY2OrJeBkVdW8IUSNk0DyQDaXSolF/DEnPslmK5roy+pqFyL72KZyQNPBI+kOrhn+WS7HXcBpAA6Xa6V8ggqHLMLL0PFCoaL93fG519svVDUuE+8UgnsWRg0czeXeW0jyMXxgXu8BBRcZbWKlEu4G+9IJy1q2Q91x3O/vwyXYo/p88r1kWQxX+uUTtr1tSZmeXL9+IDsxcYDW1c3NI68fOrTf39Dg14TwwbcqeaB8Y1Ll5CzOC9bMzPOHZ8788HR09F3KSWDlytSn9+93VJTpzwMD0ZxhHMY/kFX5W7feYsWirjc3F0U+H4B1lBEvtnmd8FE0aDYxW3gpcV3PFdJp1S4UivrGjWOYJ/2advrgwYN3Fjzo6+vzwTqdqWoAFnqQrCDlGEJ+CFW5jbwIQP2Xx6fncu3xoI2YKSzrOb4bPT09Bu3/B31GvzdI0IHtAAAAAElFTkSuQmCC",
	
	upload : "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAADLElEQVR42q1Ve0hTURg/59y7h+LmVs7Xwh5/GBKZKKVBIT2IMKtRkUkiSQYVxYr+CIr+KHqABI6CiCJ76fBB5B9CURgFEmZSWZlRYS9w6qbuoW7Oe8/pu2vTa0G6rnc7fL+d+33nd37fd/YdjKJ47rjycmiQpO1JfX5/pjE4GoI6V5kXInRI020uinvWM6sE9QMl9/oHW7YJogelzN36sch0K2PWCGo9m3aMev0N3uG3FCEOazUp2KBPP7srof6UYoK60VV60bfI0+tqQhwXI01RkQaIyZCP+Fjf0mLD4/eKCGqdZa0O58Nchgh4h9xBBSNU9CNzkqWv2FSV/N8Edtf2Yx5Pz8WR0W+UYF4ewxiimOf1OMGYXbU7qWZv1AR33esW0hFTt8P1hBFOK/cDBZIcRCmc2QR9DlLH4vWliU3NURHU9JZ//e5oWsBhdWTBvwgkK7AxlJZc4GfaH7pSY7M4I4LbjhKbc/CDNRBwUUK4UEr+iGETqWIUEcITc2L+g9LUqoJpCa70Z2aRkfmvnUMdCBMVYn/taaqC0AwRkFG3GJEY986D5raGaRVYG+clY0QY4XFQn6KqoCIrDy84RQGGryhQk69X4MAdx8ZrBi6s+TI+oxpEnnOdGZV+t3BEFkGRdGbBwgbImRWf/rnGtASnO5bY/F5qlSUIkg4fsESNyPmVXcoITrZn2sZ8olU2NVEDwiNSsbpTGcHxF1mgQLBGagABGArAJAsHDFeufaeM4GhLji0oU0ClnYMCyfJQ2ssb3igjOPR0uS0gr8GUFGFyraBdGcGB5jwgmEwRkh1TIMA3Nr9USpBrCw5PKoAGBP9uQiTLqzhyvbBNGcG+R9mXBD87HHGlUmvA5LeFBnuzMIoaNDY2Sj1ZGlrYIMOUU38eb934U/3qBEF8UJ4iBneCiml7lgW3FOu08SImocsiAEOwWCzCBIHdbk+HxfbDyADlSTAXB1gHmAMcg0RYGquD4ZOJpOaGQQFYhgEIJMhTuH3AX+paPogdBtwHuAvGVVxdXS0tZIQXBnihB6wDrAljKefQLcmcsLLIb8kKYAdlc16AY4B9YewGPPQLl25lI/VRgFUAAAAASUVORK5CYII%3D",
	newfile : "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAFa0lEQVR42qVWW2xUVRTde+6dufPqzDCddoZCkw40TYyooS0iIRr90S+jhBgTP0w0UT4kEhP84EeNQSWQqMRgjQICXyiPBEMMhYC2GCWIAlEendJ3O6923u87d+52n9NKCMES4k1O5t5zz93r7LXW3mcQ5q9NPDbzCMCDX2Ue3/H4gId+90sUwdevX9+3f/9+WN7ejgvztPDu7vs7n+VvLpuFbdu20cGDB/fy85v3AvhraGhoVZPPT9Wa/sAAqqpAwOel9vblRiqV8vNc8W6AWLlcDmYKJajrOuRyOVAUZT4SERiGAVarFUzTBFVV5bOYF8Pn84HVZoNmnxfWPr4Grl69GubPJu4FECpVa1TIFzAWi4HD6SBd15Ev0Gs1crnd2ODAFosC9bpODCIz6OgIo2bXwOt205o1vcgAHf8JoBsNisfjGI/HQFVUqnMQn9cL5UqFODMZSCznnRPTguVyhVpbW7GtrQ1cDgf19vYsChA0mdHJqUlIJpNgNkxwuVxQ02vAmYCnyQONRkPSZNI8VQ67Azo7O2UQu2aDnp6exSni/Ck6E2WKooJzKhaL2MSB+R15PE1Yr9ehVCqD3a5RrVbDJ9atI1wQX6zv6e5enCJFVWl2dhbHxsY4iF0GWRFeAc2B5kVdJCZURaHu+wAEVXYK2wymp6c5GQvYFpzDQOD3N0MqnZK0lYolsCgWUFjwYDAILa0t0nXdq1cvTpHNZqNUOo1pBnE6nZyBjjprYNM0IpMkRUwPu6hOPIfsJPZ+O1pZD+HaBRd1cbzhe2agaRqkMxlIJBKiAKT3DRbW5XSysCRFtvOafKEgRa5Vq1B2A1xM3oRsOQ9ffbwbooPX8qCbfRzzQx6V2wClUikkeE+LDNJpEUx6XdSBgy3IVGE+nwe32y0FjSTH8dvxH6l9WTuuCoTBqWoUK6Xw/Mhl6O87QsWzE5c57rM8UhKAHRNyMC3RmRlkoaXXRaGJrKpcaCwiiqrljXC6s7Rn+nt8amU3WS2IKz1h3iVSRs/gXDUL0VKaDu05gLOHI2c49nMSoFAohFhAinGh3Roelrw7HQ4UNcCCE2eEVaZEiLx7ah8tWeJCu4KkWgjfX/sRqKjSQPQcnpk4DXUTaTyfwJNbj1PlRv5FCZDL54NNnH40GpUuEjsXnLPYsvC6urogFArB0GwEtl54B5Y6vWChOqgWEz5/+jD/WqF/4ij8MHIY9IYFUNFgsP8SRHYNHZMAmWw25PV4iAXGSCQCwlFCH+YfWBNieqQePydOw6+Nk/TCio24sXOTXKMpmqyNBjXQMA1omAZtGdyAY7EUnHv18qgEmEulQ/4lPspkMrLZCSGZGmFFWXSCIsH/T8kT8Kd5jJ4Pv8IA75JEne+6t+8Ns05vD6zFRE6H4xuuxSRAPDkXbA34IcuHhyi2SqUCHo8HBO8iCzaBtO3ArRNwxf4NPBJ4FB5reRKsFoLVLW+xyAokyr/DVPEC1BoEJ0b3wPiwAUdfH/tDAkxH46G2UKtsFSIYA8hNiYoW4vPuUQAkUzE4bW4mtz2LXptBblsDX+6cAAtqdCPzGV5K7oS8rlK2pmL/3gL88nX6EwkwOjkd6ljeRiwojo6OcmvwE/uem12TsKakKxAIgJfb96nruyjjPoABO5BXM/CZZRFABpgsfIHX059CpqbQyKSBO15KlCo58yF5ZJ4dOP/wOj6RRNu93yWa4ZXUdvCFfoMWRxO4rQ3uSwQ1wwJMO/w9FoP3XkvC1E3jDV6+Tx763T29fdt37ISlbW33P5O570SjM5jInqKOzosYXjYHLs2k6yMVPHJsFg59WS4VsrRFBIc7Pvw/f1vMhTgzPET1il409e/LfwBGMiRx8YbaeAAAAABJRU5ErkJggg%3D%3D",
	newlink : "data:image/png;base64,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",
	newdir : "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAENklEQVR42p1WW0xcVRRde96FGWSmt4BAoDBDsUKgjZBQP3zQWFMTmwZf1ZjY+mha0qSYtPGjjUn7Y/GVNCH60Q8TabS1WipqUzSNSv2or0Ipj2aEFpH3MDMwwDAzzNztOcOMUnUYxpO7kn3O3XevvfY5+95LSH3oBNKT+IQF5qVBKQZ/yWhEY04O1gqblz1/h+33Q3W58JWwX4gvagQ2CqStEDynqBAt7S1aTW52NFhCApVBR08wGpvUU3IxnwgXqytRftcK4UPC85kHjHh5u3FVUsc9jIKds05JcLbpQPrT9XsOMhkUiqXDFMsoarO45joRHmmFGjTGs02oQNpTs4z8/a5+ueicaN1Zsq764J1B/2UzBj97FgPXApyToSGznniNDqQVxSUihlbUQScmWmKxSJ4g4Hh9LErw2+Tn2+xK2VPJdU93YazvI/i90xDBYdASNJJdRpFMciIQiRA6e9Zg29uegSWClhqH4tiaRMFf+kW9IpTIJ2pHAnT7+lkU744rOF/mUAqrVk8g5gkJ1DDTwgi5hruQ9ZwnTrDeruSWptgSywZHxDFzA8FxIDAhFlRM+YCs5xEr0fksh5KVl6ICZoRnRTW8CC9Ms1arkk635CLvuyTBLsQUtGTYFcW6mlRFcuJ4RPzw++bxq8eE7/wK5mCG3u/DFnUKm7MDyM3DkoIn4wpaTQ7FlpZAgWyCMEFdFJOgbFMe8mrp3cAWNhQ8QZvWlcCo1bM/HKRfxnvh7/iEdwc7aEMpI7suruCS1qFk6lkct6WgLAlUkrWMbtqyEk16iQ/NPUyO9Y+LPtDSxsxSZOjN7A56qd93C7OhIPf9fI5edP+Ex47FCS6TQ7GKBokmLgn4P9WEQsDJ3/PZqeygTKOR9RqmveUHUGgp5hvuDvrY2YxFcYInF+bIdLEFp05OxQiuwL6aLfBMAvs81VhrLYFisInGZewpb0CBxY4e9zV86vwAYVWDieAkhrp78HVDd2wPfoRQgKSnyDmgweHF+7hC2UDHa5pFSelvxcvs+m8foUGXB5d2dQzHCAyCQLPiy0saPX3AGwY732MroqPVrQkJXr1SRaPuBZyr64sSdPVfRXlxYfISDQqC98LFSLcY4bA6kKaL4MG8RtiMZRidb8fViTfhX9TipqcXw70hfPjKH92S4LVHa3HireNgi3llBTOiWduGrEwVZrIaI5xhCNPW/AuwmWp4dP4C/TC2D76Qjj0BHZ055kXnF7NH5IPya9YgIF+npmQq9CayH25WLFWbLWwzqVSSuRfpuiKeCV2ngZkz8ASJv7/so6b97n5WUZHqN1mOh8yZ9OWhd2xpdTsKRPZW0WiEQFiFa8GN06eH0HRk2h0KoFb43vg/BHJUCrx/b6Wmpnarhu7O0vDQSIS+aVP5lpPbxL16gdtA6n8V/xybBO4XkF3kEmgXuLnc4U+9lwUYV6CYGgAAAABJRU5ErkJggg%3D%3D",
	
	edit : "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAFfUlEQVR42qWWC1CUVRTH/xfYXZb3a0F8gY8StZGsdErFEsuR0VJmqplSc0JFSFhQ1AS2REUZ0BB2WbMSYcYcK+3lNI0NWb4ChjEUiUBUJEveIAa77C7sns73lTNI4OR0Z87Md+937/l993/OPfcTuL9NZ9vLFs7mhIdrDrZqNhPbRbZsaUwMmqBxc3OrKS4u1kRFRZGTs/O9d8Q2eN69/uBx6rfZxLFPj+PO3V469e1Jce7Mj9skyOCF2pSUlPztO3ai40438fL/DLBYLNT4e7NwdVVjbLCGioqKRNau7Yf41brBC3cZDIb0FatWo89qRVdnJ4QQsNvtUCgUsPKYUqmEw+GQTalSwcZjPT09uNXcBT8/X4wZpcHVG43I37sH58+cfoN9fjwUoFv9ZgysNhtdq68XKnZiMpmJpRMmUy+8vL3Rb+snh8MuFAoltba2iKq6RkybNpXGjQ4STS1tqKu7Slu0sbd40hT2af0XIGbNWtj6+6m8vFy4qdVwkIPYmfDx8UFzcxNcXBTk7e0tGm/epF8bmsXkRyYjdNxoYpnEH83tMOZmUdmFs1KAUzFEW1mideti0T8wgLLyMigVSlmWAe7b7QNwd3dnIKG5qQkVV+oxcfIUjAkOhMbXG9du3sLPFy/i62NFuN5wYzf7e2c4gC52/XrYHQ4q/alUeHp6SDEgk8kkPDw8OSYcWQ5//uHPxIwZ4TQrfKoYpfFDZXUdtXV0irPfHEdoaAgVGAtGBsTFxcuylJeVyzFwdnYmpUopwsLCJP2RYyymzK9qRUBIGGW+9oQQva3oMVuo4tz3YtGzc2DqM1NiYuKIgPT4eAY4CJerLsPZyZkzyEWKCdzUbjh56jQyjl+BxZfj5xMClcOEmOk2WFvq4aeyY2dmJgoLCzEiQK/X697asIFlIKqrreMdKKFydSUzS1R5+QqSD3yHdvUkgt8kIU1C1w2hbq9C9FQFZe7OFONDQnDAaCStVjsiIH0DA6RWW1srnwPJz+3bTUjMOYp6x3gg4FHAxRXo/g1OrdWIDLoLbVwMlixdIq8zGo14EED3D4BqamoE5z9aWlro3dzD4od2fy4mYYDal/Bnk0BbDT3j1SpWLovE8uhoCg4OFtLHGB+0g/x8vS4h4W9ARUWF8PD0hOH9Q1R4aUDYA6YBnqMAcyehrVZMEQ204oWZInLhQoSHhxOnsAwoKCigpKSk4QF5+fm6xIQESRoqLSsTNdXVSD9aQR2ejwn4sDw2LpQd9TTWUifWLJpOixcvFgNcStg5PT5zpuDTCwMDkkcC7M/LS+cMgBNrX1JSgsyDJ3DeNBHwYyOuxp3XEWiqxbaXn8LKVSsREBBwf73mHXAmYmNy8vCA3P37dVoJ4ORE23fuFtkXrLD5hhFcVALdjXBrq8SauUH0fORzInTCBDKbzSIoKIgPVyjxroVUGPUGA23auHF4wL73cnVabSJMvb0U8UqC+MUrkoPqIwfVvb0S6+cFYtnSKNIEBgquqLJTKdMkgFqtFlKJ0esNtDll0/CAnL370pOStPjy8y9wd0CBkiYPnChtgHfnJWgXjkH0Sy9yRfWCzWaTF7i4uEDKtMDAQPlZAnCiYOuWzcMDsrJzdBwgZO3JoldfXyW6e3qRka2n5QueFBHz5sLLy0uuTdJns0P5wpEA/v7+8pjFagMnCqW+vXV4QOaeLF18XBwOfvARqd09RWdbM+bNeZr8AwK42HmANZdrE6elfKPJ+kgP3KRnc58FnCikS0sdFqBdGxuXl7EjA58cOYKI+RGYNXs2HqZ1dHUjLS0Nhz48mMxd/VCAhu/UmqycfZr5zy3gS9/poS59h90hXfRI3bq53WLpk/5O2ocCpPZ/f1uq2Law1dwb/As9ta83TnMQNwAAAABJRU5ErkJggg%3D%3D"

};



