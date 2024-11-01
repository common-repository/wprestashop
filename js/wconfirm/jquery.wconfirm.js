(function($){
	$.fn.wconfirm = function(options){
        var defaults = {
            showclose:      true,
            width:          "500px",
            showheader:     true,
            showok:         true,
            showcancel:     true,
            message:        'Message Confirm',
            showload:       false,
            ok:             "OK",
            cancel:         "CANCEL",
            title:          "TITLE",
			callbackFinish: function(){},
            callbackClose: function(){},
            callbackok: function(){}
        }
		
        var o = $.extend(defaults,options);
        var msg = o.message;

		
        $(".box-message").remove();
		
		var boxWapper = $("<div/>",{
		  "class":  "box-message"
		}).appendTo("body").css({"width":o.width});
        
        var showheader = $("<div/>",{
            "class": "box-header"
        }).appendTo(boxWapper);

        var box_header_left = $("<span/>",{
            "class":  "box-header-left-top-left"
		}).appendTo(showheader);
        var box_header_right = $("<span/>",{
            "class":  "box-header-left-top-right"
		}).appendTo(showheader);
        
        var box_header_center = $("<div/>",{
            "class": "box-header-left-top-center",
            text: o.title
        }).appendTo(showheader);

        if(o.showclose){
			var button_close = $("<span/>",{
                "class":  "closeme",
                click: function(){
                    closeBoxMsgFade();
                    o.callbackClose.call();
                },
                mouseout: function(){
                    $(this).removeClass("hover-close");
                },
                mouseover: function(){
                    $(this).addClass("hover-close");
                }
			}).appendTo(showheader);        
		}
        
		
		if(!o.showheader){
            showheader.addClass("no-header");
            box_header_left.addClass("no-header-left");
            box_header_right.addClass("no-header-right");
            box_header_center.addClass("no-header-center").empty();
		}
        
        

        var showcenter = $("<div/>",{
            "class": "box-center"
        }).appendTo(boxWapper);
        
		
        var showcenter_left = $("<span/>",{
            "class":  "box-header-left-center-left"
		}).appendTo(showcenter);

        var showcenter_right = $("<span/>",{
            "class":  "box-header-left-center-right"
		}).appendTo(showcenter);


        var box_center_center = $("<div/>",{
            "class": "box-center-center",
            html: msg 
        }).appendTo(showcenter);


        var showfooter = $("<div/>",{
            "class": "box-footer"
        }).appendTo(boxWapper);
        
		
        $("<span/>",{
            "class":  "box-header-left-footer-left"
		}).appendTo(showfooter);

        $("<span/>",{
            "class":  "box-header-left-footer-right"
		}).appendTo(showfooter);


        var box_footer_show = $("<div/>",{
            "class": "box-header-left-footer-center"
        }).appendTo(showfooter);

        if(o.showcancel){
			var button_cancel = $("<span/>",{
                "class":  "edialog-cancel",
                html:   o.cancel,
                click:  function(){
                    closeBoxMsgFade();
                    o.callbackClose.call();
                }
			});
            button_cancel.appendTo(box_footer_show);
		}
        if(o.showok){
            var button_ok = $("<span/>",{
                "class":  "edialog-ok",
                html:   o.ok,
                click:  function(){
                    closeBoxMsgFade();
                    o.callbackok.call();
                }
			});
            button_ok.appendTo(box_footer_show);
        }

        boxWapper.css({"left":getWidth(boxWapper),"top":getHeight(boxWapper)});
        showcenter_left.css({"height":box_center_center.outerHeight()});
		showcenter_right.css({"height":box_center_center.outerHeight()});

		if(o.showload){
			$("#cbox").remove();			
			var cbox = $("<div/>",{
				id:	        "cbox"
			}).appendTo("body").show();
		}
		
		o.callbackFinish.call();
		
        function closeBoxMsgFade(){
			boxWapper.hide();
			if(o.showload){$("#cbox").hide();}
        }
        function getWidth(string){
            return Math.round(($(window).width()- string.outerWidth())/2);
        }
        function getHeight(string){
            return Math.round(($(window).height() - string.outerHeight())/2);
        }
	}
})(jQuery);