jQuery(document).ready(function(a) {
	function b(b, f) {
		p.array = "";
		switch (p.type) {
		case "album":
			if ((f = f.match(/album\/(\d+)/gi)) && f.length > 0) {
				var g = [];
				a.each(f,
				function(b, c) { - 1 === a.inArray(c, g) && g.push(c)
				}),
				p.array = g.join(",").replace(/album\//g, "")
			}
			break;
		default :
			 if ((f = f.match(/subject\/(\d+)/gi)) && f.length > 0) {
				var g = [];
				a.each(f,
				function(b, c) { - 1 === a.inArray(c, g) && g.push(c)
				}),
				p.array = g.join(",").replace(/subject\//g, "")
			}
		}
		p.array ? d() : c(),
		e(b, p)
	}
	function c() {
		a("#wpd-shell-insert").attr("disabled", "disabled")
	}
	function d() {
		a("#wpd-shell-insert").removeAttr("disabled")
	}
	function e(b, c) {
		h = '[douban id="' + c.array + '" type="' + c.type + '"][/douban]',
		a("#wpd-preview").text(h).addClass("texted")
	}
	function f() {
		g = "xiami",
		p = {
			type: "",
			array: "",
			auto: 0,
			loop: 0,
			unexpand: 0
		},
		h = "",
		k.append(m())
	}
	var g,
	h,
	i,
	j = a("#gowpd"),
	k = a("body"),
	l = a("#wpd-template").html(),
	m = Handlebars.compile(l),
	n = a("#wpd-remote-template").html(),
	o = Handlebars.compile(n),
	p = {
		type: "",
		array: "",
		auto: 0,
		loop: 0,
		unexpand: 0
	},
	q = 1;
	j.click(function() {
		f()
	}),
	k.on("click", "#wpd-shell-close",
	function() {
		a("#wpd-shell").remove()
	}),
	k.on("click", "#wpd-shell-insert",
	function() {
		var b = a(this);
		"disabled" != b.attr("disabled") && (send_to_editor(h), a("#wpd-shell").remove())
	}),
	k.on("click", "#wpd-remote-content ul li",
	function() {
		var b = a(this);
		b.hasClass("selected") ? b.removeClass("selected") : b.addClass("selected")
	}),
	k.on("click", ".media-router a",
	function() {
		var b = a(this),
		c = a(".media-router a").index(b);
		b.hasClass("active") || (a(".media-router a.active,.wpd-li.active").removeClass("active"), b.addClass("active"), a(".wpd-li").eq(c).addClass("active"), g = a(".wpd-li").eq(c).data('type'),
		p.type = a(".wpd-li.active").data('type'),
		b(g, a(".wpd-li.active .wpd-textarea").val()))
	}),
	k.on("click", "#wpd-remote-sure",
	function() {
		var c = [];
		a("#wpd-remote-content ul li.selected").each(function() {
			c.push(a(this).attr("data-id"))
		}),
		c = c.join(","),
		console.log(g),
		b(g, c)
	}),
	k.on("change", ".wpd-li.active",
	function() {
		var c = a(".wpd-li.active .wpd-textarea").val();
		p.type = a(".wpd-li.active").data('type'),
		b(g, c)
	}),
	k.on("focus keyup input paste", ".wpd-textarea",
	function() {
		var c = a(this),
		d = c.val();
		p.type = a(".wpd-li.active").data('type'),
		b(g, d)
	})
});