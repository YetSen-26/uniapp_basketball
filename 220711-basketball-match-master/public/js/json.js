$(function() {
	$.ajax({
		type: "GET",
		url: "js/news.json",
		dataType: "json",
		success: function(data) {
			$.each(data, function(k, v) {
				$("ul").append("<li><div class='img'><img src='" + v.images +
					"'></div></li>")
				$("li:eq(" + k + ")").append(("<div class='box'></div>"));
				$("li:eq(" + k + ") .box").append(("<h1>" + v.title + "</h1>"));
				$("li:eq(" + k + ") .box").append(("<p class='time'>" + v.time + "</p>"));
				$("li:eq(" + k + ") .box").append(("<p class='text'>" + v.paragraph +
					"</p>"));
			})

		},
	})
})
