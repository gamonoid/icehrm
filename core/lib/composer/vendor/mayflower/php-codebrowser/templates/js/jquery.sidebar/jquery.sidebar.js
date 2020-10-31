/*!
 * Side Bar v1.0.1
 * http://sideroad.secret.jp/
 *
 * Copyright (c) 2009 sideroad
 *
 * Dual licensed under the MIT licenses.
 * Date: 2009-09-01
 */
//Side Bar Plugin
(function($) {

	$.fn.sidebar = function(options){
		return this.each(function(){
			options = options || {};

			//default setting
			options = $.extend({
				position: "left",
				width: 100,
				height: 200,
				injectWidth: 50,
				liMouseOver: {
					marginLeft: "5px"
				},
				liMouseOut: {
					marginLeft: "0px"
				},
				open : "mouseenter",
				close : "mouseleave"
			}, options);
			var m;
			var icss;
            var ccss = {
                height: options.height,
                width: options.width
            };
			if(options.position == "left" || options.position == "right") {
                m = options.width - options.injectWidth;
				icss = {
					height: options.height,
					width: options.injectWidth
				};
				ccss.top = ($(window).height()/2) - (options.height/2) + "px";

			} else {
				m = options.height - options.injectWidth;
                icss = {
                    height: options.injectWidth,
                    width: options.width
                };
                ccss.left = ($(window).width()/2) - (options.width/2) + "px";
			}
			var bcss = {
				height: options.height,
				width: options.width
			};
			var e = {};
			var l = {};

			ccss[options.position] = "-" + m + "px";
			icss[options.position] = m + "px";
			e[options.position] = 0;
			l[options.position] = "-" + m;

			//container
			var c = $("<div><div/>").attr("id", "jquerySideBar" + new Date().getTime()).addClass("sidebar-container-" + options.position).css(ccss);

			//inject
			var i = $("<div><div/>").addClass("sidebar-inject-" + options.position).css(icss);

			//body
			var b = $("<div><div/>").addClass("sidebar-body").css(bcss).hide();

			//menu events
            var isEnter;
			$(this).addClass("sidebar-menu").find("li,li *").mouseenter(function(){
                if (!isEnter) return;
				$(this).animate(options.liMouseOver, 250);
			}).mouseleave(function(){
				$(this).animate(options.liMouseOut, 250);
			});

			//container events
			var isProcessing;
			c.bind(options.open,function(){
				if (isEnter) return;
				if (isProcessing) return;
				isEnter = true;
                isProcessing = true;
				c.animate(e, {
					duration: 200,
					complete: function(){
						i.fadeOut(200, function(){
							b.show("clip", 200,function(){
                                isProcessing = false;
							});
						});
					}
				});
			}).bind(options.close,function(){
				if(!isEnter) return;
				if(isProcessing) return;
				isProcessing = true;
				c.animate(l, {
					duration: 200,
					complete: function(){
						b.hide("clip", 200, function(){
							i.fadeIn(200, function(){
                                isEnter = false;
								isProcessing = false;
							});
						});
					}
				});
			});

			//append to body
			b.append(this);
			c.append(b);
			c.append(i);
			$(document.body).append(c);
			$(window).resize(function(){
	            if(options.position == "left" || options.position == "right") {
					c.css({top:($(this).height()/2) - (options.height/2) + "px"});
	            } else {
	                c.css({left:($(this).width()/2) - (options.width/2) + "px"});
	            }
			});
		});
	}
})(jQuery);