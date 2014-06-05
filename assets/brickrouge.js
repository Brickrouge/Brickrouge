!function(){function c(b){var c=b.get(a);if(!c)throw new Error("The "+a+" attribute is not defined.");var d=Brickrouge.Widget[c];if(!d)throw new Error("Undefined constructor: "+c);b.store("widget",!0);var e=new d(b,b.get("dataset"));b.store("widget",e);try{window.fireEvent("brickrouge.widget",[e,this])}catch(f){console.error(f)}return e}function d(a){a=a||document.body;var d=a.getElements(b);a.match(b)&&d.unshift(a),d.reverse().each(function(a){a.retrieve("widget")||c(a)})}var a="brickrouge-is",b="["+a+"]";Element.Properties.widget={get:function(){var a=this.retrieve("widget");return a||(a=c(this)),a}};var e=function(){var a=null,b=null;return function(c,d){var g,e=new Array,f=new Array;return null===a&&(a=[],"undefined"!=typeof brickrouge_cached_css_assets&&(a=brickrouge_cached_css_assets),document.id(document.head).getElements('link[type="text/css"]').each(function(b){a.push(b.get("href"))})),null===b&&(b=[],"undefined"!=typeof brickrouge_cached_js_assets&&(b=brickrouge_cached_js_assets),document.id(document.html).getElements("script").each(function(a){var c=a.get("src");c&&b.push(c)})),c.css.each(function(b){-1==a.indexOf(b)&&e.push(b)}),e.each(function(b){new Asset.css(b),a.push(b)}),c.js.each(function(a){-1==b.indexOf(a)&&f.push(a)}),(g=f.length)?(f.each(function(a){new Asset.javascript(a,{onload:function(){b.push(a),--g||d()}})}),void 0):(d(),void 0)}}();this.Brickrouge={IS_ATTRIBUTE:a,WIDGET_SELECTOR:b,Utils:{Busy:new Class({startBusy:function(){1!=++this.busyNest&&this.element.addClass("busy")},finishBusy:function(){--this.busyNest||this.element.removeClass("busy")}})},Widget:{},updateDocument:function(a){a=a||document.body,window.fireEvent("brickrouge.update",{target:a}),d(a)},updateAssets:e}}(),Request.API&&(Request.Element=new Class({Extends:Request.API,onSuccess:function(a,b){var c=Elements.from(a.rc).shift();return a.assets?(Brickrouge.updateAssets(a.assets,function(){this.fireEvent("complete",[a,b]).fireEvent("success",[c,a,b]).callChain()}.bind(this)),void 0):(this.parent(c,a,b),void 0)}}),Request.Widget=new Class({Extends:Request.Element,initialize:function(a,b,c){void 0==c&&(c={}),c.url="widgets/"+a,c.onSuccess=b,this.parent(c)}})),Element.Properties.dataset={get:function(){for(var e,a={},b=this.attributes,c=0,d=b.length;d>c;c++)e=b[c],e.name.match(/^data-/)&&(a[e.name.substring(5).camelCase()]=e.value);return a}},document.id(document.body),Browser.ie&&document.body.addEvent("click",function(a){window.fireEvent("click",a)}),window.addEvent("domready",function(){Brickrouge.updateDocument(document.body)}),Brickrouge.Form=new Class({Implements:[Options,Events],options:{url:null,useXHR:!1,replaceOnSuccess:!1},initialize:function(a,b){this.element=document.id(a),this.setOptions(b),this.options.replaceOnSuccess&&(this.options.useXHR=!0),(this.options.useXHR||b&&(b.onRequest||b.onComplete||b.onFailure||b.onSuccess))&&this.element.addEvent("submit",function(a){a.stop(),this.submit()}.bind(this))},alert:function(a,b){var c=a,d=this.element.getElement("div.alert-"+b)||new Element("div.alert.alert-"+b,{html:'<button class="close" data-dismiss="alert">×</button>'});"string"==typeOf(a)?a=[a]:"object"==typeOf(a)&&(a=[],Object.each(c,function(b,c){if("string"==typeOf(c)&&"_base"!=c){var d,g,e=null,f=document.id(this.element.elements[c]);if("collection"==typeOf(f))if(d=document.id(f[0]).getParent("div.radio-group"),e=d.getParent(".control-group"),d)d.addClass("error");else for(g=0,j=f.length;j>g;g++)document.id(f[g]).addClass("error");else f&&(f.addClass("error"),e=f.getParent(".control-group"));e&&e.addClass("error")}b&&b!==!0&&a.push(b)},this)),a.length&&(a.each(function(a){d.adopt(new Element("p",{html:a}))}),this.insertAlert(d))},insertAlert:function(a){a.hasClass("alert-success")&&this.options.replaceOnSuccess?(a.getElement('[data-dismiss="alert"]').dispose(),a.addClass("undissmisable"),a.inject(this.element,"before"),this.element.addClass("hidden")):a.getParent()||a.inject(this.element,"top")},clearAlert:function(){var a=this.element.getElements("div.alert:not(.undissmisable)");a&&a.destroy(),this.element.getElements(".error").removeClass("error")},submit:function(){this.fireEvent("submit",{}),this.getOperation().send(this.element)},getOperation:function(){return this.operation?this.operation:this.operation=new Request.JSON({url:this.options.url||this.element.action,method:this.element.get("method")||"GET",onRequest:this.request.bind(this),onComplete:this.complete.bind(this),onSuccess:this.success.bind(this),onFailure:this.failure.bind(this)})},request:function(){this.clearAlert(),this.fireEvent("request",arguments)},complete:function(){this.fireEvent("complete",arguments)},success:function(a){a.message&&this.alert(a.message,"success"),this.onSuccess(a)},onSuccess:function(){this.fireEvent("success",arguments)},failure:function(a){var b={};try{b=JSON.decode(a.responseText),b.errors&&this.alert(b.errors,"error"),b.exception&&alert(b.exception)}catch(c){console&&console.log(c),alert(a.statusText)}this.fireEvent("failure",[a,b])}}),Brickrouge.Form.STORED_KEY_NAME="_brickrouge_form_key",document.body.addEvent("click:relay(.alert a.close)",function(a,b){var c=b.getParent("form");a.stop(),c&&c.getElements(".error").removeClass("error"),b.getParent(".alert").destroy()}),document.body.addEvent('click:relay([data-dismiss="alert"])',function(a,b){var c=b.getParent(".alert"),d=c.getParent("form");a.stop(),d&&d.getElements(".error").removeClass("error"),c.destroy()}),!function(){function c(){$$(a).getParent().removeClass("open")}function d(){var d,a=this.get("data-target")||this.get("href"),b=document.id(a)||this.getParent();return d=b.hasClass("open"),c(),!d&&b.toggleClass("open"),!1}var a='[data-toggle="dropdown"]',b=!1;window.addEvent("click:relay("+a+")",function(a,c){a.rightClick||(a.stop(),b=!0,d.apply(c))}),window.addEvent("click",function(){return b?(b=!1,void 0):(c(),void 0)})}(),document.body.addEvent("click:relay(.tabbable .nav-tabs a)",function(a,b){var d,e,c=b.get("href");if("#"==c){var f=b.getParent(".nav-tabs").getElements("a").indexOf(b);d=b.getParent(".tabbable").getElement(".tab-content").getChildren()[f]}else d=document.id(c.substring(1));if(a.preventDefault(),!d)throw new Error("Invalid pane id: "+c);e=b.getParent(".nav-tabs").getFirst(".active"),e&&e.removeClass("active"),b.getParent("li").addClass("active"),e=d.getParent(".tab-content").getFirst(".active"),e&&e.removeClass("active"),d.addClass("active")}),window.addEvent("brickrouge.update",function(){document.body.getElements(".nav-tabs-fill").each(function(a){var b=a.getElements("a"),c=b[b.length-1],d=c.getCoordinates(a),e=a.getSize(),f=e.x-(d.left+d.width),g=0|f/b.length/2,h=f-2*g*b.length;c.setStyle("padding-right",c.getStyle("padding-right").toInt()+h),b.each(function(a){a.setStyle("padding-left",a.getStyle("padding-left").toInt()+g),a.setStyle("padding-right",a.getStyle("padding-right").toInt()+g)})})}),Brickrouge.Popover=new Class({Implements:[Events,Options],options:{anchor:null,animate:!1,popoverClass:null,placement:null,visible:!1,fitContent:!1,loveContent:!1,iframe:null},initialize:function(a,b){this.element=document.id(a),this.setOptions(b),this.arrow=this.element.getElement(".arrow"),this.actions=this.element.getElement(".popover-actions"),this.repositionCallback=this.reposition.bind(this,!1),this.quickRepositionCallback=this.reposition.bind(this,!0),a=this.element,b=this.options,this.iframe=b.iframe,b.anchor&&this.attachAnchor(b.anchor),this.tween=null,b.animate&&(this.tween=new Fx.Tween(a,{property:"opacity",link:"cancel",duration:"short"})),(b.fitContent||b.loveContent)&&a.addClass("fit-content"),b.loveContent&&a.addClass("love-content"),b.popoverClass&&a.addClass(b.popoverClass),a.addEvent("click:relay(.popover-actions [data-action])",function(a,b){this.fireAction({action:b.get("data-action"),popover:this,event:a})}.bind(this)),b.visible&&this.show()},fireAction:function(){this.fireEvent("action",arguments)},attachAnchor:function(a){this.anchor=document.id(a),this.anchor||(this.anchor=document.body.getElement(a)),this.reposition(!0)},changePlacement:function(a){this.element.removeClass("before").removeClass("after").removeClass("above").removeClass("below").addClass(a)},show:function(){this.element.setStyles({display:"block",visibility:"hidden"}),window.addEvents({load:this.quickRepositionCallback,resize:this.quickRepositionCallback,scroll:this.repositionCallback}),this.iframe&&document.id(this.iframe.contentWindow).addEvents({load:this.quickRepositionCallback,resize:this.quickRepositionCallback,scroll:this.repositionCallback}),document.body.appendChild(this.element),Brickrouge.updateDocument(this.element),this.reposition(!0),this.options.animate?(this.tween.set(0),this.element.setStyle("visibility","visible"),this.tween.start(1)):this.element.setStyle("visibility","visible")},hide:function(){var a=function(){this.element.setStyle("display",""),this.element.dispose()}.bind(this);if(window.removeEvent("load",this.quickRepositionCallback),window.removeEvent("resize",this.quickRepositionCallback),window.removeEvent("scroll",this.repositionCallback),this.iframe){var b=document.id(this.iframe.contentWindow);b.removeEvent("load",this.quickRepositionCallback),b.removeEvent("resize",this.quickRepositionCallback),b.removeEvent("scroll",this.repositionCallback)}this.options.animate?this.tween.start(0).chain(a):a()},isVisible:function(){return"visible"==this.element.getStyle("visibility")&&"none"!=this.element.getStyle("display")},computeAnchorBox:function(){var b,d,e,f,g,h,i,a=this.anchor,c=this.iframe;return c?(d=c.getCoordinates(),e=c.contentDocument.documentElement,aX=a.offsetLeft,aY=a.offsetTop,aW=a.offsetWidth,aH=a.offsetHeight,f=e.clientHeight,h=e.scrollTop,aY-=h,0>aY&&(aH+=aY),aY=Math.max(aY,0),aH=Math.min(aH,f),g=e.clientWidth,i=e.scrollLeft,aX-=i,0>aX&&(aW+=aX),aX=Math.max(aX,0),aW=Math.min(aW,g),aX+=d.left,aY+=d.top):(b=a.getCoordinates(),aX=b.left,aY=b.top,aH=b.height,aW=b.width),{x:aX,y:aY,w:aW,h:aH}},computeBestPlacement:function(a,b,c){function k(){return f+1>b+2*j}function l(){return e-(f+1+h)>b+2*j}function m(){return g+1>c+2*j}var i,d=document.body.parentNode,e=d.scrollWidth,f=a.x,g=a.y,h=a.w,j=20;switch(i=this.options.placement){case"after":if(l())return i;break;case"before":if(k())return i;break;case"above":if(m())return i;break;case"below":return i}return l()?"after":k()?"before":m()?"above":"below"},reposition:function(a){if(this.anchor){void 0===a&&(a="visible"!=this.element.getStyle("visibility"));var d,e,f,g,h,i,j,n,o,p,y,z,b=20,c=this.actions,k=this.element.getSize(),l=k.x,m=k.y,q=document.id(document.body),r=q.getSize(),s=q.getScroll(),t=s.x,u=s.y,v=r.x,w=r.y,x={top:null,left:null};d=this.computeAnchorBox(),e=d.x,f=d.y,g=d.w,h=d.h,i=e+g/2-1,j=f+h/2-1,p=this.computeBestPlacement(d,l,m),this.changePlacement(p),"before"==p||"after"==p?(o=Math.round(f+(h-m)/2-1),n="before"==p?e-l+1:e+g-1,n=n.limit(t+b-1,t+v-(l+b)-1),o=o.limit(u+b-1,u+w-(m+b)-1)):(n=Math.round(e+(g-l)/2-1),o="above"==p?f-m+1:f+h-1,n=n.limit(t+b-1,t+v-(l+b)-1)),m>2*b&&("before"==p||"after"==p?(z=f+h/2-1-o,z=Math.min(m-(c?c.getSize().y:b)-10,z),z=Math.max(b,z),z+o-1!=j&&(o-=o+z-j),x.top=z):(y=(e+g/2-1-n).limit(b,l-b),y+l-1!=i&&(n-=n+y-i),x.left=y)),a?(this.element.setStyles({left:n,top:o}),this.arrow.setStyles(x)):(this.element.morph({left:n,top:o}),this.arrow.morph(x))}}}),Brickrouge.Popover.from=function(a){var b,c=a.title,d=a.content,e=a.actions,f=new Element("div.popover-inner");return c&&f.adopt(new Element("h3.popover-title",{html:c})),"string"==typeOf(d)?f.adopt(new Element("div.popover-content",{html:d})):(f.adopt(new Element("div.popover-content").adopt(d)),void 0===a.fitContent&&(a.fitContent=!0)),"boolean"==e&&(e=[new Element('button.btn.btn-cancel[data-action="cancel"]',{html:Locale.get("Popover.cancel")||"Cancel"}),new Element('button.btn.btn-primary[data-action="ok"]',{html:Locale.get("Popover.ok")||"Ok"})]),e&&f.adopt(new Element("div.popover-actions").adopt(e)),b=new Element("div.popover").adopt([new Element("div.arrow"),f]),new Brickrouge.Popover(b,a)},Brickrouge.Widget.Popover=Brickrouge.Popover,document.body.addEvents({'mouseenter:relay([rel="popover"])':function(a,b){var d,c=b.retrieve("popover");c||(d=b.get("dataset"),d.anchor=b,c=Brickrouge.Popover.from(d),b.store("popover",c)),c.show()},'mouseleave:relay([rel="popover"])':function(a,b){var c=b.retrieve("popover");c&&c.hide()}}),Brickrouge.Modal=new Class({Implements:[Options,Events],options:{},initialize:function(a,b){this.element=a=document.id(a),this.backdrop=new Element("div.modal-backdrop"),this.backdrop.addEvent("click",this.hide.bind(this)),this.setOptions(b),a.addEvent("click:relay([data-action])",function(a,b){this.action(b.get("data-action"))}.bind(this))},show:function(){var a=this.element;a.addClass("in"),a.removeClass("out"),a.removeClass("hide"),this.backdrop.inject(a,"before"),window.fireEvent("brickrouge.modal.show",this)},hide:function(){var a=this.element;window.fireEvent("brickrouge.modal.hide",this),a.removeClass("in"),a.addClass("out"),a.addClass("hide"),this.backdrop.dispose()},isHidden:function(){return this.element.hasClass("hide")},toggle:function(){this.isHidden()?this.show():this.hide()},action:function(a){this.fireEvent("action",a)}}),window.addEvent('click:relay([data-toggle="modal"])',function(a,b){if(!a.rightClick){var e,c=b.get("href").substring(1),d=document.id(c);d&&(a.stop(),e=d.retrieve("modal"),e||(e=new Brickrouge.Modal(d),d.store("modal",e)),e.toggle())}}),window.addEvent('click:relay([data-dismiss="modal"])',function(a,b){if(!a.rightClick){var d,c=b.getParent(".modal");c&&(a.stop(),d=c.retrieve("modal"),d?d.hide():c.addClass("hide"))}}),!function(){var a=[];Brickrouge.Tooltip=new Class({Implements:[Options],options:{animation:!0,placement:"top",selector:!1,template:'<div class="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>',trigger:"hover",title:"",delay:0,html:!0},initialize:function(a,b){this.setOptions(b),this.anchor=document.id(a),this.element=Elements.from(this.options.template).shift(),this.setContent(a.get("data-tooltip-content"))},setContent:function(a){this.element.getElement(".tooltip-inner").set(this.options.html?"html":"text",a),["fade","in","top","bottom","left","right"].each(this.element.removeClass,this.element)},getPosition:function(a){var b=this.anchor,c=0,d=0,e=b.offsetWidth,f=b.offsetHeight;if(!a){var g=b.getPosition();c=g.y,d=g.x}if("AREA"==b.tagName){var h=null,i=null,j=null,k=null,l=b.getParent(),m=l.id||l.name,n=document.body.getElement('[usemap="#'+m+'"]');g=n.getPosition(),c=g.y,d=g.x,b.coords.match(/\d+\s*,\s*\d+/g).each(function(a){var b=a.match(/(\d+)\s*,\s*(\d+)/),c=b[1],d=b[2];h=null===h?c:Math.min(h,c),i=null===i?c:Math.max(i,c),j=null===j?d:Math.min(j,d),k=null===k?d:Math.max(k,d)}),c+=j,d+=h,e=i-h+1,f=k-j+1}return Object.append({},{y:c,x:d},{width:e,height:f})},show:function(){var e,f,g,h,b=this.element,c=this.options,d=c.placement,i={};switch(c.animation&&b.addClass("fade"),"function"==typeOf(d)&&(d=d.call(this,b,anchor)),e=/in/.test(d),b.dispose().setStyles({top:0,left:0,display:"block"}).inject(e?this.anchor:document.body),g=b.offsetWidth,h=b.offsetHeight,f=this.getPosition(e),e?d.split(" ")[1]:d){case"bottom":i={top:f.y+f.height,left:f.x+f.width/2-g/2};break;case"top":i={top:f.y-h,left:f.x+f.width/2-g/2};break;case"left":i={top:f.y+f.height/2-h/2,left:f.x-g};break;case"right":i={top:f.y+f.height/2-h/2,left:f.x+f.width}}a.unshift(this),b.setStyles(i).addClass(d).addClass("in")},hide:function(){var b=this.element;a.erase(this),b.removeClass("in"),b.dispose()}}),Brickrouge.Tooltip.hideAll=function(){Array.slice(a).each(function(a){a.hide()})}}(),document.body.addEvent("mouseenter:relay([data-tooltip-content])",function(a,b){var c=b.retrieve("tooltip");c||(c=new Brickrouge.Tooltip(b,Brickrouge.extractDataset(b,"tooltip")),b.store("tooltip",c)),c.show()}),document.body.addEvent("mouseleave:relay([data-tooltip-content])",function(a,b){try{b.retrieve("tooltip").hide()}catch(c){}}),Brickrouge.Widget.Searchbox=new Class({Implements:Brickrouge.Utils.Busy,initialize:function(a){this.element=document.id(a)}}),Brickrouge.Carousel=new Class({Implements:[Options,Events],options:{autodots:!1,autoplay:!1,delay:6e3,method:"fade"},initialize:function(a,b){this.element=a=document.id(a),this.setOptions(b),this.inner=a.getElement(".carousel-inner"),this.slides=this.inner.getChildren(),this.limit=this.slides.length,this.position=0,this.timer=null,this.options.method&&(this.setMethod(this.options.method),this.method.initialize&&this.method.initialize.apply(this)),this.options.autodots&&this.setDots(this.slides.length),this.dots=a.getElements(".carousel-dots .dot"),this.dots.length||(this.dots=null),this.dots&&this.dots[0].addClass("active"),a.addEvents({'click:relay([data-slide="prev"])':function(a){a.stop(),this.prev()}.bind(this),'click:relay([data-slide="next"])':function(a){a.stop(),this.next()}.bind(this),"click:relay([data-position])":function(a,b){a.stop(),this.setPosition(b.get("data-position"))}.bind(this),"click:relay([data-link])":function(a,b){var c=b.get("data-link");c&&(document.location=c)},mouseenter:this.pause.bind(this),mouseleave:this.resume.bind(this)}),this.resume()},setDots:function(a){for(var b=new Element("div.carousel-dots"),c=this.element.getElement(".carousel-dots"),d=0;a>d;d++)b.adopt(new Element("div.dot",{html:"&bull;","data-position":d}));c?b.replaces(c):this.element.adopt(b)},setMethod:function(a){if("string"==typeOf(a)){var b=Brickrouge.Carousel.Methods[a];if(void 0===b)throw new Error("Carousel method is not defined: "+a);a=b}this.method=a,a.next&&(this.next=a.next),a.prev&&(this.prev=a.prev)},play:function(){this.timer||(this.timer=function(){this.setPosition(this.position+1)}.periodical(this.options.delay,this),this.fireEvent("play",{position:this.position,slide:this.slides[this.position]}))},pause:function(){this.timer&&(clearInterval(this.timer),this.timer=null,this.fireEvent("pause",{position:this.position,slide:this.slides[this.position]}))},resume:function(){this.options.autoplay&&this.play()},setPosition:function(a,b){a%=this.limit,a!=this.position&&(this.method.go.apply(this,[a,b]),this.dots&&(this.dots.removeClass("active"),this.dots[a].addClass("active")),this.fireEvent("position",{position:this.position,slide:this.slides[this.position]}))},prev:function(){this.setPosition(this.position?this.position-1:this.limit-1,-1)},next:function(){this.setPosition(this.position==this.limit?0:this.position+1,1)}}),Brickrouge.Carousel.Methods={fade:{initialize:function(){this.slides.each(function(a,b){a.setStyles({left:0,top:0,position:"absolute",opacity:b?0:1,visibility:b?"hidden":"visible"})})},go:function(a){var b=this.slides[this.position],c=this.slides[a];c.setStyles({opacity:0,visibility:"visible"}).inject(b,"after").fade("in"),this.position=a}},slide:{initialize:function(){var a=this.inner.getSize(),b=a.x,c=a.y,d=new Element("div",{styles:{position:"absolute",left:0,top:0,width:2*b,height:c}});this.w=b,this.h=c,this.view=d,d.adopt(this.slides),d.set("tween",{property:"left",onComplete:Brickrouge.Carousel.Methods.slide.onComplete.bind(this)}),this.slides.each(function(a,c){a.setStyles({position:"absolute",left:b*c,top:0}),c&&a.setStyle("display","none")}),this.inner.adopt(d)},go:function(a,b){var c=this.slides[a],d=this.slides[this.position];b||(b=a-this.position),this.view.setStyle("left",0),d.setStyle("left",0),c.setStyles({display:"",left:b>0?this.w:-this.w}),this.view.tween(b>0?-this.w:this.w),this.position=a},onComplete:function(){var b=this.slides[this.position];this.slides.each(function(a){a!=b&&a.setStyle("display","none")})}},columns:{initialize:function(){this.working=!1,this.fitting=0,this.childWidth=0;var a=0,b=0,c=0,d=this.element.getSize().x;this.view=new Element("div",{styles:{position:"absolute",top:0,left:0,height:this.element.getStyle("height")}}),this.view.adopt(this.slides),this.view.inject(this.inner),this.view.set("tween",{property:"left"}),this.slides.each(function(d){d.get("data-url")&&d.setStyle("cursor","pointer");var e=d.getSize().x+d.getStyle("margin-left").toInt()+d.getStyle("margin-right").toInt();d.setStyles({position:"absolute",top:0,left:a}),a+=e,b+=e,c=Math.max(c,e)},this),this.childWidth=c,this.fitting=(d/c).floor(),this.view.setStyle("width",b)},go:function(a){var b=this.limit,c=this.position-a,d=null,e=0;this.working||(this.working=!0,d=0>c?this.position+this.fitting:this.position-c,0>d?d=b+d:d>b-1&&(d-=b),0>a?a=b-c:a%=b,this.position=a,e=0>c?this.childWidth*this.fitting:-this.childWidth,this.slides[d].setStyle("left",e),this.view.get("tween").start(this.childWidth*c).chain(function(){for(var c=a,d=0,e=this.childWidth;b>c;c++,d+=e)this.slides[c].setStyle("left",d);for(c=0;a>c;c++,d+=e)this.slides[c].setStyle("left",d);this.view.setStyle("left",0),this.working=!1}.bind(this)))},next:function(){this.setPosition(this.position+1)},prev:function(){this.setPosition(this.position-1)}}},Brickrouge.Widget.Carousel=new Class({Extends:Brickrouge.Carousel});