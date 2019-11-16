class HTMLFactory {
	constructor() {}
	static listItem(target, index, text) {
		let item = "<li class=\"list-group-item list-group-flush list-group-item-action\" value=\"" +index+ "\">" +text+ "</li>";
		target.append(item);
	}
}

class EventFactory {
	constructor() {}
	static hoverOutput(btn) {
		btn.hover( function() { $(this).css("background","#dcdcdc").find("img").toggleClass("d-none"); },
			function() { $(this).css("background","#17202a").find("img").toggleClass("d-none"); })
			.mousedown( function() { $(this).css("background","#bd2130").find("img").toggleClass("d-none"); })
			.mouseup( function() { $(this).css("background","#dcdcdc").find("img").toggleClass("d-none"); });
	}
	static hoverControl(btn) {
		btn.hover( function() { $(this).addClass("btn-outline-secondary font-weight-bold").removeClass("btn-secondary"); },
			function() { $(this).addClass("btn-secondary").removeClass("btn-outline-secondary font-weight-bold"); });
	}
	static hoverRed(btn) {
		btn.hover( function() { $(this).addClass("btn-danger").removeClass("btn-outline-danger"); },
			function() { $(this).addClass("btn-outline-danger").removeClass("btn-danger"); });
	}
	static clickControl(btn, collapse) {
		btn.click( function() {
			if(!collapse.hasClass("show")) {
				$(this).addClass("btn-danger").removeClass("btn-secondary btn-outline-secondary font-weight-bold").off("mouseenter mouseleave");
			} else {
				$(this).removeClass("btn-danger").addClass("btn-outline-secondary font-weight-bold");
				EventFactory.hoverControl(btn);
			}
		});
	}
	static clickRed(btn) {
		btn.click( function() {
			let button = $(this);
			button.addClass("btn-success").removeClass("btn-danger btn-outline-danger").off("mouseenter mouseleave").text("Success!").prop('disabled', true);
			setTimeout(function(){
				button.removeClass("btn-success").addClass("btn-outline-danger").text("Reset").prop('disabled', false);
				EventFactory.hoverRed(button);
			}, 2000);
		});
	}
}