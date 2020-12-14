(function($) {
	"use strict";

	// highlight current nav item
	var url = window.location.pathname, items = $(".main-menu").find("li");
	items.removeClass("current");
	items.each(function(){
		var a = $(this).find("a");
		if (a.attr("href") == url) {
			$(this).addClass("current");
		}
	});

	// effects
	$(".txt").html(function(i, html) {
		var chars = $.trim(html).split("");
		return "<span>" + chars.join("</span><span>") + "</span>";
	});

	// full screen css hack
	function fullScreen() {
		$(".full-screen").css("height", $(window).height());
	}

	function toggleTop() {
		if ($(window).scrollTop() > 0) {
			$("#scroll-top").show();
		}
		else {
			$("#scroll-top").hide();
		}
	}

	function getPlayers() {
		$.get("https://api.mcsrvstat.us/2/81.134.82.56", function(data) {
			var list = data.players.list || [];
			$.each(list, function(index, item) {
				$("li:contains(" + item + ")").find(".avatar").css("border-color", "lime");
			});
		});
	}

	fullScreen();
	toggleTop();
	getPlayers();

	// scroll to a specific div
	if ($(".scroll-to-target").length){
		$(".scroll-to-target").on("click", function() {
			var target = $(this).attr("data-target");
		    $("html, body").animate({
			   scrollTop: $(target).offset().top
			}, 1000);
	
		});
	}

	// when document is resized, do
	$(window).on("resize", function() {
		fullScreen();		
	});


	// when document is scrolling, do
	$(window).on("scroll", function() {
		fullScreen();
		toggleTop();
	});

	// cookie stuff
	if (Cookies.get("cookieaccept") === undefined) {
		$("#cookie-box").show();
	}
	else {
		$("#cookie-box").hide();
	}

	window.set_cookie = function() {
		Cookies.set("cookieaccept", "1");
		$("#cookie-box").hide();
	};

	// fullscreen modal
	$(function() {
		function cssn($e, props) {
			var sum = 0;
			props.forEach(function (p) {
				sum += parseInt($e.css(p).match(/\d+/)[0]);
			});
			return sum;
		}
		function g($e) {
			return {
				width: cssn($e, ['margin-left', 'margin-right', 'padding-left', 'padding-right', 'border-left-width', 'border-right-width']),
				height: cssn($e, ['margin-top', 'margin-bottom', 'padding-top', 'padding-bottom', 'border-top-width', 'border-bottom-width'])
			};
		}
		function calc($e) {
			var wh = $(window).height();
			var ww = $(window).width();
			var $d = $e.find('.modal-dialog');
			if ($d.length) {
				$d.css('width', 'initial');
				$d.css('height', 'initial');
				$d.css('max-width', 'initial');
				$d.css('margin', '5px');
				var d = g($d);
				var $h = $e.find('.modal-header');
				var $f = $e.find('.modal-footer');
				var $b = $e.find('.modal-body');
				$b.css('overflow-y', 'scroll');
				var bh = wh - $h.outerHeight() - $f.outerHeight() - ($b.outerHeight() - $b.height()) - d.height;
				$b.height(bh);
			}
		}
		$('.modal-fullscreen').on('show.bs.modal', function(e) {
			var $e = $(e.currentTarget).css('visibility', 'hidden');
		});
		$('.modal-fullscreen').on('shown.bs.modal', function(e) {
			calc($(e.currentTarget));
			var $e = $(e.currentTarget).css('visibility', 'visible');
		});
		$(window).resize(function() {
			calc($('.modal.modal-fullscreen.in'));
		});
	});
	
})(window.jQuery);